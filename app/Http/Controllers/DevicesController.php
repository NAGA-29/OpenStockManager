<?php

namespace App\Http\Controllers;

// Facade
use App\Enums\ContentEnum;
use App\Http\Requests\ConfirmMultiDeviceRequest;
use App\Http\Requests\SearchDeviceRequest;
// FormRequests
use App\Http\Requests\StoreDeviceRequest;
use App\Http\Requests\UpdateDeviceRequest;
use App\Http\Requests\UploadBenchmarkFileRequest;
use App\Http\Requests\UploadSpecFileRequest;
// Model
use App\Models\Content;
use App\Models\Device;
use App\Models\DeviceCategory;
use App\Models\DeviceTypeField;
// Request
use App\Services\Image\ImageProcessor;
use App\Traits\Keyword;
use App\Utils\GeneralUtil;
// Exception
use Carbon\Carbon;
// Facades
use Exception;
// Utils
use Illuminate\Http\Request;
// Traits
use Illuminate\Support\Facades\DB;
// Enum
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
// Library
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use League\Csv\Reader;
use League\Csv\Statement;
// Service
use Ramsey\Uuid\Uuid;

class DevicesController extends Controller
{
    use Keyword;

    /**
     * Create a new controller instance.
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * 機材一覧デフォルトページ（最初のカテゴリへリダイレクト）
     *
     * @access public
     * @return \Illuminate\Http\RedirectResponse
     */
    public function deviceListDefault()
    {
        $first = DeviceCategory::active()->ordered()->first();

        if (!$first) {
            abort(404, __('messages.device_not_found'));
        }

        return redirect()->route('device.category', ['code' => $first->code]);
    }

    /**
     * 動的デバイスカテゴリ一覧ページ表示
     * device_categoriesテーブルから動的にタブを生成し、指定カテゴリのデバイス一覧を表示する
     *
     * @access public
     * @param string $code デバイスカテゴリコード
     * @return \Illuminate\View\View
     */
    public function deviceListByCategory(string $code)
    {
        $categories = DeviceCategory::active()->ordered()->get();
        $currentCategory = $categories->firstWhere('code', $code);

        if (!$currentCategory) {
            abort(404, __('messages.device_not_found'));
        }

        $devices = Device::with('contents')
            ->where('device_type', $currentCategory->code)
            ->orderBy('device_id', 'desc')
            ->paginate(10);

        $count = [];
        $count['all_count'] = Device::where('device_type', $currentCategory->code)->count();
        $count['lending_count'] = Device::where('device_type', $currentCategory->code)
            ->where('lending_now', '<>', '')
            ->whereNotNull('lending_now')
            ->count();
        $count['defective_count'] = Device::where('device_type', $currentCategory->code)
            ->where('defective', 1)
            ->count();

        return view('devices.device_list', compact('categories', 'currentCategory', 'devices', 'count'));
    }

    /**
     * 機材登録ページ表示
     * @access public
     * @return \Illuminate\View\View
     */
    public function registerDevice()
    {
        $deviceCategories = DeviceCategory::active()->ordered()->get();
        return view('register_device.register_device', compact('deviceCategories'));
    }

    /**
     * 機材登録処理
     * @access public
     * @param StoreDeviceRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storeDevice(StoreDeviceRequest $request)
    {
        try {
            $safe = $request->safe()->toArray();
            DB::beginTransaction();

            $deviceId = Device::generateDeviceId($safe['device_type'], $safe['device_name']);

            Device::create([
                'device_id'             => $deviceId,
                'device_type'           => $safe['device_type'],
                'device_name'           => $safe['device_name'],
                'device_serial'         => $safe['device_serial'],
                'custom_fields'         => $safe['custom_fields'] ?? null,
                'first_work_date_at'    => $safe['first_work_date_at'],
                'purchase_date_at'      => $safe['purchase_date_at'],
                'client'                => $safe['client'],
                'condition_id'          => $safe['condition'],
                'defective'             => array_key_exists('defective', $safe) ? true : false,
                'not_for_sale'          => array_key_exists('not_for_sale', $safe) ? true : false,
                'note'                  => $safe['note'],
            ]);

            if (array_key_exists('device_image', $safe)) {
                // @TODO: ファイルの詳細分析ロジック処理
                $imgPro = new ImageProcessor();
                $img_info = $imgPro->process($request->file('device_image'));
                if (!$img_info) {
                    throw new Exception(__('messages.image_analysis_failed'));
                }
                $result = Content::create([
                    'id'            => (string) Uuid::uuid7(),
                    'filename'      => $img_info['original_name'],
                    'extension'     => $img_info['extension'],
                    'hash'          => $img_info['hash'],
                    'path'          => ContentEnum::IMAGE_DIR . $img_info['original_name'],
                    'height'        => $img_info['dimensions']['height'],
                    'width'         => $img_info['dimensions']['width'],
                    'size'          => $img_info['size'],
                    'device_id'     => $deviceId,
                ]);

                if ($result) {
                    Storage::disk('public')->put("device_img/{$img_info['original_name']}", file_get_contents($img_info['temp_path']));
                } else {
                    throw new Exception(__('messages.image_save_failed'));
                }
            }

            DB::commit();
            return redirect()
                ->back()
                ->with('success_message', __('messages.registration_completed'))
                ->with('registered_device_id', $deviceId);
        } catch (\Exception $err) {
            DB::rollBack();
            Log::channel('error')->error('device.store.failed', [
                    'action' => 'device_registration',
                    'device_id' => $deviceId ?? null,
                    'device_type' => $safe['device_type'] ?? null,
                    'error_message' => $err->getMessage(),
                    'error_class' => get_class($err),
                ]);
            return redirect()
                ->back()
                ->with('error_message', __('messages.registration_failed'));
        }
    }

    /**
     * 複数端末の登録処理(CSV)
     * @access public
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storeDeviceMulti(Request $request)
    {
        // キャンセルボタンの場合
        if ($request->back === 'back') {
            $request->session()->forget('register_device_multi');
            return redirect()->route('device.register_multi');
        }
        // セッションがない場合はリダイレクト ※念の為
        if (!($request->session()->exists('register_device_multi'))) {
            return redirect()->route('device.register_multi');
        }
        try {
            DB::beginTransaction();
            foreach ($request->session()->pull('register_device_multi') as $device_data) {
                Device::create([
                    'device_id'             => $device_data['device_id'],
                    'device_type'           => $device_data['device_type'],
                    'device_name'           => $device_data['device_name'],
                    'device_serial'         => $device_data['device_serial'],
                    'first_work_date_at'    => $device_data['first_work_date_at'] ?: null,
                    'purchase_date_at'      => $device_data['purchase_date_at'] ?: null,
                    'option'                => $device_data['option'],
                    'condition_id'          => $device_data['condition'],
                    'defective'             => $device_data['defective'] == true ? true : false,
                    'not_for_sale'          => $device_data['not_for_sale'] == true ? true : false,
                    'note'                  => $device_data['note'],
                ]);
            }
            DB::commit();
            return redirect(route('device.register_multi'))->with('success_message', __('messages.save_completed'));
        } catch (Exception $err) {
            DB::rollBack();
            Log::channel('error')->error('device.store_multi.failed', [
                'action' => 'device_multi_registration',
                'error_message' => $err->getMessage(),
                'error_class' => get_class($err),
            ]);
            return redirect()
                ->route('device.register_multi')
                ->withInput()
                ->with('error_message', __('messages.registration_error'));
        }
    }

    /**
     * 複数台機材の登録ページを表示
     * devicesテーブルへ新規登録を行う
     * @access public
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function registerDeviceMulti(Request $request)
    {
        return view('register_device.register_device_multi');
    }

    /**
     * 複数台機材の確認ページを表示
     * @access public
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function confirmMulti(ConfirmMultiDeviceRequest $request)
    {
        // csvアップロード処理
        $file_path = $request->file('device_register_file')->getPathname();
        $csv = Reader::createFromPath($file_path, 'r')->setHeaderOffset(0);
        $stmt = Statement::create();
        $records = $stmt->process($csv);
        $devices = [];
        try {
            foreach ($records as $record) {
                $record = GeneralUtil::sanitizeCsvRecord($record);
                Validator::make($record, [
                    'device_type'           => ['required', 'string', 'max:16', Rule::in(DeviceCategory::activeCodes())],
                    'device_name'           => ['required', 'string', 'max:255',],
                    'device_serial'         => ['required', 'string', 'max:32',],
                    'first_work_date_at'    => ['nullable', 'date',],
                    'purchase_date_at'      => ['nullable', 'date',],
                    'option'                => ['nullable', 'string',],
                    'defective'             => ['nullable', 'integer',],
                    'not_for_sale'          => ['nullable', 'integer',],
                    'note'                  => ['nullable', 'string'],
                ])->validate();

                // device_idを自動生成（既に追加済みの端末も考慮してカウント）
                $samePrefix = collect($devices)->filter(function ($d) use ($record) {
                    return str_starts_with($d['device_id'], "{$record['device_type']}_{$record['device_name']}_");
                })->count();
                $baseId = Device::generateDeviceId($record['device_type'], $record['device_name']);
                if ($samePrefix > 0) {
                    // 同バッチ内で同じprefix があれば番号を加算
                    $prefix = "{$record['device_type']}_{$record['device_name']}_";
                    $baseNum = (int) substr($baseId, strlen($prefix));
                    $record['device_id'] = $prefix . str_pad($baseNum + $samePrefix, 6, '0', STR_PAD_LEFT);
                } else {
                    $record['device_id'] = $baseId;
                }

                array_push($devices, $record);
                $request->session()->put('register_device_multi', $devices);
            }
            return view('register_device.register_device_confirm_multi', compact('devices'));
        } catch (Exception $err) {
            Log::channel('error')->error('device.confirm_multi.failed', [
                'action' => 'device_csv_validation',
                'error_message' => $err->getMessage(),
                'error_class' => get_class($err),
            ]);
            return redirect()
                ->back()
                ->with('error_message', __('messages.registration_error'));
        }
    }

    /**
     * 機材それぞれ個別ページを表示
     * @access public
     * @param string $device_id デバイス個別id
     * @return \Illuminate\View\View
     */
    public function deviceIndividual(string $device_id)
    {
        $date_list = [];
        $device_info_collection = Device::with([
            'contents',
            'condition',
            'rental_hists.clients',
            'sale_hists.clients',
        ])->find($device_id);

        if (!$device_info_collection) {
            abort(404, __('messages.device_not_found'));
        }

        // 時間のフォーマットを変更
        $first_work_date_at = new Carbon($device_info_collection->first_work_date_at);
        $date_list['first_work_date_at'] = $first_work_date_at->toDateString();

        $purchase_date_at = new Carbon($device_info_collection->purchase_date_at);
        $date_list['purchase_date_at'] = $purchase_date_at->toDateString();

        $sale_date_at = new Carbon($device_info_collection->sale_date_at);
        $date_list['sale_date_at'] = $sale_date_at->toDateString();
        // コレクションをソート(desc)
        $device_info_collection->rental_hists = $device_info_collection->rental_hists->sortByDesc('checkout_at');

        $deviceCategories = DeviceCategory::active()->ordered()->get();
        $customFieldDefs  = DeviceTypeField::where('device_category_code', $device_info_collection->device_type)
                                           ->orderBy('sort_order')->orderBy('id')->get();

        return view('devices.show', compact('device_info_collection', 'date_list', 'deviceCategories', 'customFieldDefs'));
    }

    /**
     * 機材情報更新
     * @access public
     * @param UpdateDeviceRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateDevice(UpdateDeviceRequest $request)
    {
        $safe = $request->safe()->toArray();
        try {
            DB::beginTransaction();
            $device = Device::where('device_id', $safe['device_id'])->first();
            $device->device_type        = $safe['device_type'];
            $device->device_name        = $safe['device_name'];
            $device->device_serial      = $safe['device_serial'];
            $device->custom_fields      = $safe['custom_fields'] ?? null;
            $device->first_work_date_at = $safe['first_work_date_at'];
            $device->purchase_date_at   = $safe['purchase_date_at'];
            $device->option             = $safe['option'];
            $device->condition_id       = $safe['condition'];
            $device->defective          = $safe['defective'] ?? false;
            $device->not_for_sale       = $safe['not_for_sale'] ?? false;
            $device->note               = $safe['note'];
            $device->save();


            $imageList = $device->contents;
            // $imageListから消えた画像IDを取得し、DBから削除
            $deleteImageIdList = array_diff(array_column($imageList->toArray(), 'id'), $safe['imageList'] ?? []);
            foreach ($deleteImageIdList as $deleteImageId) {
                $deleteImage = Content::find($deleteImageId);
                $result = Storage::disk('public')->delete($deleteImage->path);
                if (!$result) {
                    throw new Exception(__('messages.image_delete_failed'));
                }
                $deleteImage->delete();
            }

            if (array_key_exists('device_image', $safe)) {
                // @TODO: ファイルの詳細分析ロジック処理
                $imgPro = new ImageProcessor();
                $img_info = $imgPro->process($request->file('device_image'));
                if (!$img_info) {
                    throw new Exception(__('messages.image_analysis_failed'));
                }
                $result = Content::create([
                    'id'            => (string) Uuid::uuid7(),
                    'filename'      => $img_info['original_name'],
                    'extension'     => $img_info['extension'],
                    'hash'          => $img_info['hash'],
                    'path'          => ContentEnum::IMAGE_DIR . $img_info['original_name'],
                    'height'        => $img_info['dimensions']['height'],
                    'width'         => $img_info['dimensions']['width'],
                    'size'          => $img_info['size'],
                    'device_id'     => $safe['device_id'],
                ]);

                if ($result) {
                    Storage::disk('public')->put("device_img/{$img_info['original_name']}", file_get_contents($img_info['temp_path']));
                } else {
                    throw new Exception(__('messages.image_save_failed'));
                }
            }

            DB::commit();
            return redirect()
                ->back()
                ->with('success_message', __('messages.save_completed'))
                ->withInput();
        } catch (\Exception $err) {
            DB::rollBack();
            Log::channel('error')->error('device.update.failed', [
                'action' => 'device_update',
                'device_id' => $safe['device_id'] ?? null,
                'error_message' => $err->getMessage(),
                'error_class' => get_class($err),
            ]);
            return redirect()
                ->back()
                ->withInput()
                ->with('error_message', __('messages.edit_failed'));
        }
    }

    /**
     * デバイス検索
     * @access public
     * @param Request $request
     * @return \Illuminate\View\View | \Illuminate\Http\RedirectResponse
     */
    public function searchDevice(SearchDeviceRequest $request)
    {
        if ($request->isMethod('post')) {
            return redirect()->back();
        }

        try {
            // NOTE:全角を半角変換->スペース排除してkeywordを複数作成
            $keywords = $this->extractKeywords(mb_convert_kana($request->word, 'r'));

            $device_info_collection = collect();
            $query = Device::query();
            if ($request->hiddenType) {
                // hiddenTypeがある場合はdevice_typeを絶対条件にする
                $query->where('device_type', $request->hiddenType);
            }

            foreach ($keywords as $key) {
                $escapedKey = addcslashes($key, '%_\\');
                $query->where(function ($subQuery) use ($escapedKey) {
                    $subQuery->orWhere('device_id', 'like', '%' . $escapedKey . '%')
                        ->orWhere('device_serial', 'like', '%' . $escapedKey . '%')
                        ->orWhere('note', 'like', '%' . $escapedKey . '%');
                });
            }
            // ページネーションを適用
            $device_info_collection = $query->orderBy('device_id', 'desc')->paginate(10);
            $search_keywords = $request->hiddenType . ' '. $request->word;

            return view('devices.search_results', compact('device_info_collection', 'search_keywords'));
        } catch (Exception $err) {
            Log::channel('error')->error('device.search.failed', [
                'action' => 'device_search',
                'search_word' => $request->word ?? null,
                'device_type' => $request->hiddenType ?? null,
                'error_message' => $err->getMessage(),
                'error_class' => get_class($err),
            ]);
            return redirect()
                ->back()
                ->with('error_message', __('messages.search_failed'));
        }
    }

    /**
     * スペックExcelファイルアップロードページ
     * @access public
     * @return \Illuminate\View\View
     */
    public function getSpecFile()
    {
        return view('devices.device_spec_file');
    }

    /**
     * ベンチマークExcelファイルアップロードページ
     * @access public
     * @return \Illuminate\View\View
     */
    public function getBenchMarkFile()
    {
        return view('devices.device_benchmark_file');
    }

    /**
     * スペックExcelファイルアップロード
     * @access public
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function specUpload(UploadSpecFileRequest $request)
    {
        $upload_image = $request->file('spec_file');
        $file_name = basename($request->file('spec_file')->getClientOriginalName());

        if ((Storage::disk('public')->missing('spec'))) {
            // ディレクトリがない場合作成
            Storage::disk('public')->makeDirectory('spec');
        }
        $files = Storage::disk('public')->files('spec');
        Storage::disk('public')->delete($files);

        $upload_image->storeAs('public/spec', $file_name);
        return redirect()
            ->back()
            ->with('success_message', __('messages.upload_completed'));
    }

    /**
     * ベンチExcelファイルアップロード
     * @access public
     * @param Request $request
     */
    public function benchmarkUpload(UploadBenchmarkFileRequest $request)
    {
        $upload_image = $request->file('benchmark_file');
        $file_name = basename($request->file('benchmark_file')->getClientOriginalName());

        if (!(Storage::disk('public')->exists('benchmark'))) {
            // ディレクトリがない場合作成
            Storage::disk('public')->makeDirectory('benchmark');
        }

        $files = Storage::disk('public')->files('benchmark');
        Storage::disk('public')->delete($files);

        $upload_image->storeAs('public/benchmark', $file_name);
        return redirect()
            ->back()
            ->with('success_message', __('messages.upload_completed'));
    }

    /**
     * バーコード印刷ページ表示
     * @access public
     * @param string $device_id デバイスID
     * @return \Illuminate\View\View
     */
    public function barcodePrint(string $device_id)
    {
        $device = Device::find($device_id);

        if (!$device) {
            abort(404, __('messages.device_not_found'));
        }

        return view('devices.barcode_print', compact('device'));
    }

    /**
     * ベンチExcelファイルダウンロード
     * @access public
     * @param Request $request
     */
    public function download(Request $request)
    {
        if ($request->is('device/file/spec/download')) {
            $filePath = Storage::disk('public')->files('spec/');
            if (empty($filePath)) {
                abort(404, __('messages.file_not_found'));
            }
            return Storage::disk('public')->download($filePath[0]);
        }
        if ($request->is('device/file/benchmark/download')) {
            $filePath = Storage::disk('public')->files('benchmark/');
            if (empty($filePath)) {
                abort(404, __('messages.file_not_found'));
            }
            return Storage::disk('public')->download($filePath[0]);
        }
        if ($request->is('device/register/multi/download')) {
            $filePath = Storage::disk('public')->files('device/');
            if (empty($filePath)) {
                abort(404, __('messages.file_not_found'));
            }
            return Storage::disk('public')->download($filePath[0]);
        }
    }
}
