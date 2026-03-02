<?php

namespace App\Http\Controllers;

// Model
use App\Http\Requests\StoreRentalCartRequest;
use App\Http\Requests\StoreRentalFileRequest;
use App\Http\Requests\UpdateRentalHistoryRequest;
use App\Models\Client;
// Request
use App\Models\Device;
use App\Models\Personnel;
use App\Models\RentalHist;
use App\Traits\SearchesClients;
// Facades
use App\Utils\GeneralUtil;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
// Exception
use Illuminate\Support\Facades\Auth;
// Utils
use Illuminate\Support\Facades\DB;
// Traits
use Illuminate\Support\Facades\Log;
// Enum
// Library
use Illuminate\Support\Facades\Storage;
use League\Csv\Reader;
use League\Csv\Statement;

class RentalHistsController extends Controller
{
    use SearchesClients;

    protected $dates = [
        'checkout_at',
    ];

    /**
     * 機材貸出情報の登録ページ
     * @access public
     * @param string $device_id : デバイスID
     * @return \Illuminate\View\View
     */
    public function checkOutWrite(string $device_id)
    {
        $date_list = [];
        $device_info_collection = Device::getIndividualDeviceInfo($device_id);
        if (empty($device_info_collection) || !isset($device_info_collection[0])) {
            abort(404, __('messages.device_not_found'));
        }
        // 時間のフォーマットを変更
        $first_work_date_at = new Carbon($device_info_collection[0]->first_work_date_at);
        $date_list['first_work_date_at'] = $first_work_date_at->format('Y-m-d');

        $purchase_date_at = new Carbon($device_info_collection[0]->purchase_date_at);
        $date_list['purchase_date_at'] = $purchase_date_at->format('Y-m-d');
        $clients = Client::all(); // Clientテーブル全データ取得
        return view('rental.rental', compact('device_info_collection', 'date_list', 'clients'));
    }

    /**
     * 貸出機材ファイルのアップロードページ表示
     * @access public
     * @return \Illuminate\View\View
     */
    public function rental()
    {
        $clients = Client::get();
        return view('rental.index', compact('clients'));
    }

    /**
     * カート内の機材レンタル処理
     * @access public
     * @param \App\Http\Requests\StoreRentalCartRequest $request
     * @return \Illuminate\View\View
     */
    public function storeWithCart(StoreRentalCartRequest $request)
    {
        try {
            DB::beginTransaction();
            $safe = $request->safe()->all();
            $result = RentalHist::create([
                'lend_id'               => $safe['lend_id'],
                'client'                => $safe['client_id'],
                'personnel'             => $safe['personnel'],
                'staff'                 => Auth::id(),
                'all_returned'          => 0,
                'checkout_at'           => $safe['checkout_at'],
                'schedule_return_at'    => $safe['schedule_return_at'],
                'note'                  => $safe['note'],
            ]);

            if (!$result) {
                throw new Exception(__('messages.rental_registration_failed'));
            }

            // 端末をレンタル中に変更
            foreach ($safe['deviceIds'] as $device_id) {
                $device_update = [
                    'lending_now'       => $safe['lend_id'],
                ];
                $_ = Device::where('device_id', $device_id)->update($device_update);
                if (!$_) {
                    throw new Exception(__('messages.rental_registration_failed'));
                }
                $result->devices()->attach($device_id, ['checkout_at' => $safe['checkout_at']]);
            }
            DB::commit();

            return redirect()
                ->route('rental.history')
                ->with('success_message', __('messages.registration_completed'))
                ->with('device_cart', 'RESET'); // NOTE: カートのリセット
        } catch (Exception $err) {
            DB::rollBack();
            Log::channel('error')->error(
                __('messages.rental_process_failed'),
                [
                    'error_message' => $err->getMessage(),
                ]
            );
            return redirect()
                ->back()
                ->with('error_message', __('messages.registration_failed'));
        }
    }

    /**
     * 貸出ファイルのアップロードと解析
     * @access public
     * @param StoreRentalFileRequest $request
     * @return \Illuminate\View\View
     */
    public function upload(StoreRentalFileRequest $request)
    {
        try {
            $safe = $request->safe()->all();
            // csvアップロード処理
            $file_path = $request->file('csv_file')->getPathname();
            $csv = Reader::createFromPath($file_path, 'r')->setHeaderOffset(0);
            $stmt = Statement::create();
            $records = $stmt->process($csv);

            $lists = [];
            foreach ($records as $record) {
                $record = GeneralUtil::sanitizeCsvRecord($record);
                $device_info = Device::where('device_id', $record['device_id'])->first();
                if ($device_info === null) {
                    throw new Exception(__('messages.device_not_exists', ['device_id' => $record['device_id']]));
                }
                if (!empty($device_info->sale_id)) {
                    throw new Exception(__('messages.device_already_sold', ['device_id' => $record['device_id']]));
                }
                if (!empty($device_info->lending_now)) {
                    throw new Exception(__('messages.device_currently_rented', ['device_id' => $record['device_id']]));
                }
                if ($device_info->defective != 0) {
                    throw new Exception(__('messages.device_defective', ['device_id' => $record['device_id']]));
                }
                array_push($lists, [$device_info, $record]);
            }

            array_push(
                $safe,
                Personnel::where('personnel_id', $request['personnel'])->first()
            );

            $request_info = [
                'lend_id'               => $safe['lend_id'],
                'client_id'             => $safe['client_id'],
                'personnel_id'          => $safe['personnel'],
                'checkout_at'           => $safe['checkout_at'],
                'schedule_return_at'    => $safe['schedule_return_at'],
                'note'                  => $safe['note'],
            ];
            $request->session()->put('request_data', $request_info);
            $request->session()->put('lists', $lists);
            return view('rental.rental_with_file_confirm', compact('lists', 'safe'));
        } catch (Exception $err) {
            Log::channel('error')->error(
                __('messages.csv_parse_failed'),
                [
                    'error_message' => $err->getMessage(),
                ]
            );
            return redirect()
                ->back()
                ->with('error_message', $err->getMessage())
                ->withInput();
        }
    }

    /**
     * 貸出(複数)データのDB保存
     * @access public
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storeWithFile(Request $request)
    {
        // キャンセルボタンの場合
        if ($request->back === 'back') {
            $request->session()->forget(['request_data', 'lists']);
            return redirect()
                ->route('device.rental');
        }

        // セッションがない場合はリダイレクト ※念の為
        if (!$request->session()->exists('request_data') || !$request->session()->exists('lists')) {
            return redirect()
                ->route('device.rental');
        }
        try {
            DB::beginTransaction();
            $request_info = $request->session()->pull('request_data');
            $lists = $request->session()->pull('lists');
            RentalHist::create([
                'lend_id'               => $request_info['lend_id'],
                'client'                => $request_info['client_id'],
                'personnel'             => $request_info['personnel_id'],
                'staff'                 => Auth::id(),
                'all_returned'          => 0,
                'checkout_at'           => $request_info['checkout_at'],
                'schedule_return_at'    => $request_info['schedule_return_at'],
                'note'                  => $request_info['note'],
            ]);

            foreach ($lists as $list) {
                $device_update = [
                    'lending_now'       => $request_info['lend_id'],
                    'using_user_id'     => $list[1]['user_id'],
                    'note'              => $list[1]['note'],
                ];

                Device::where('device_id', $list[0]['device_id'])->update($device_update);
                $rental_hists = RentalHist::find($request_info['lend_id']);
                $rental_hists->devices()->attach($list[0]['device_id'], ['checkout_at' => $request_info['checkout_at']]);
            }

            DB::commit();
            session()->flash('checkout_message', __('messages.registration_completed'));
            return redirect()
                ->route('rental.history')
                ->with('success_message', __('messages.registration_completed'));
        } catch (Exception $err) {
            session()->flash('checkout_message', __('messages.registration_failed'));
            DB::rollBack();
            Log::channel('error')->error(
                __('messages.registration_failed'),
                [
                    'error_message' => $err->getMessage(),
                ]
            );
            return redirect()
                ->route('device.rental')
                ->with('error_message', __('messages.registration_failed'));
        }
    }

    /**
     * personnelsテーブルから担当者情報を取得
     * client_idからその企業に所属している人物のデータを全て取得する
     * @access public
     * @param Request $request: client_id クライアントID
     * @return string|false
     */
    public function getPersonnel(Request $request)
    {
        $data = [];
        $data_list = [];
        $personnel_id = $request->personnel_id;
        $personnels = Personnel::where('client_id', $personnel_id)->get();

        // 結果判定
        if (count($personnels) == 0) {
            $data_list['success'] = 0;
        } else {
            $data_list['success'] = 1;
            foreach ($personnels as $personnel) {
                array_push($data, $personnel);
            }
            $data_list['data'] = $data;
        }
        return response()->json($data_list);
    }


    /**
     * レンタル端末の返却処理
     * @access public
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function return(Request $request)
    {
        try {
            DB::beginTransaction();
            // device_rentalテーブル
            $device_rental_update = [
                'return_at' => $request->return_at,
            ];
            RentalHist::find($request->lend_id)->devices()->updateExistingPivot($request->device_id, $device_rental_update);
            $result = RentalHist::with(['devices'])->where('lend_id', $request->lend_id)->get();
            $judge = true;
            foreach ($result as $i) {
                foreach ($i->devices as $n) {
                    $judge = (is_null($n->pivot->return_at)) ? false : true;
                    if (!$judge) {
                        break;
                    }
                }
            }
            // rental_histsテーブル
            if ($judge) {
                $history_update = [
                    'return_at' => $request->return_at,
                    'all_returned' => true,
                ];
                RentalHist::where('lend_id', $request->lend_id)->update($history_update);
            }

            // device テーブル
            $device_update = [
                'lending_now' => '',
                'defective' => $request->defective == null ? 0 : $request->defective,
                'not_for_sale' => $request->not_for_sale == null ? 0 : $request->not_for_sale,
                'note' => $request->note,
            ];
            Device::where('device_id', $request->device_id)->update($device_update);
            DB::commit();
            return redirect()
                ->back()
                ->with('success_message', __('messages.registration_completed'));
        } catch (\Exception $err) {
            DB::rollBack();
            Log::channel('error')->error(
                __('messages.registration_failed'),
                [
                    'error_message' => $err->getMessage(),
                ]
            );
            return redirect()
                ->back()
                ->with('error_message', __('messages.registration_failed'));
        }
    }

    /**
     * 全機材貸出履歴の表示
     * @access public
     * @param Request $request
     * @return \Illuminate\View\View | \Illuminate\Http\RedirectResponse
     */
    public function getAllHistory(Request $request)
    {
        try {
            $query = RentalHist::with(['clients', 'personnels', 'user'])
                ->orderBy('checkout_at', 'desc');

            if ($request->filled('word')) {
                $keyword = '%' . addcslashes($request->word, '%_\\') . '%';
                $query->where(function ($q) use ($keyword) {
                    $q->where('note', 'like', $keyword)
                        ->orWhereHas('clients', fn ($q) => $q->where('company', 'like', $keyword));
                });
            }

            $histories = $query->paginate(10)->withQueryString();
            return view('history.all_rental_historys', compact('histories'));
        } catch (\Exception $err) {
            Log::channel('error')->error(
                __('messages.data_fetch_failed'),
                [
                    'error_message' => $err->getMessage(),
                ]
            );
            return redirect()
                ->back()
                ->with('error_message', __('messages.data_fetch_failed'));
        }
    }

    /**
     * レンタル情報詳細
     * @access public
     * @param string $id
     * @return \Illuminate\View\View | \Illuminate\Http\RedirectResponse
     */
    public function getDetail($id)
    {
        try {
            $rental_details = RentalHist::find($id);
            if (!$rental_details) {
                abort(404, __('messages.rental_history_not_found'));
            }
            return view('rental.rental_detail', compact('rental_details'));
        } catch (\Exception $err) {
            Log::channel('error')->error(
                __('messages.data_fetch_failed'),
                [
                    'error_message' => $err->getMessage(),
                ]
            );
            return redirect()
                ->back()
                ->with('error_message', __('messages.data_fetch_failed'));
        }
    }

    /**
     * 登録用CSVファイルダウンロード
     * @access public
     * @param Request $request
     */
    public function download(Request $request)
    {
        if ($request->is('device/rental/multi/download')) {
            $filePath = Storage::disk('public')->files('rental/');
            if (empty($filePath)) {
                abort(404, __('messages.file_not_found'));
            }
            return Storage::disk('public')->download($filePath[0]);
        }
    }

    /**
     * 端末一斉返却ページ表示
     * @access public
     * @param Request $request
     * @return \Illuminate\View\View | \Illuminate\Http\RedirectResponse
     */
    public function confirmReturnDeviceMulti(Request $request)
    {
        try {
            $lend_id = $request->lend_id;
            $request_data = RentalHist::find($lend_id);
            $collection = $request_data->devices->toArray();
            return view('rental.multi_return_device_confirm', compact('request_data', 'collection'));
        } catch (Exception $err) {
            Log::channel('error')->error(
                __('messages.data_fetch_failed'),
                [
                    'error_message' => $err->getMessage(),
                ]
            );
            return redirect()
                ->back()
                ->with('error_message', __('messages.data_fetch_failed'));
        }
    }

    /**
     * 端末一斉返却処理
     *
     * @access public
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storeReturnDeviceMulti(Request $request)
    {
        try {
            $now = Carbon::now();
            $request_data = RentalHist::with(['devices'])->find($request->lend_id);
            DB::beginTransaction();
            foreach ($request_data->devices as $i) {
                $i->lending_now = '';
                $i->save();
            }
            RentalHist::find($request->lend_id)->devices()->newPivotStatement()->orWhereNull('return_at')->update(['return_at' => $now]);

            $request_data->all_returned = true;
            $request_data->return_at = $now;
            $request_data->save();
            DB::commit();
            return redirect()
                ->route('rental.history')
                ->with('success_message', __('messages.registration_completed'));
        } catch (Exception $err) {
            DB::rollBack();
            Log::channel('error')->error(
                __('messages.data_fetch_failed'),
                [
                    'error_message' => $err->getMessage(),
                ]
            );
            return redirect()
                ->back()
                ->with('error_message', __('messages.registration_error'));
        }
    }

    /**
     * @access public
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function editRentalHistory(UpdateRentalHistoryRequest $request)
    {
        try {
            $validated = $request->validated();

            $rental_history = RentalHist::find($validated['lend_id']);
            $rental_history->checkout_at = $validated['checkout_at'];
            $rental_history->schedule_return_at = $validated['schedule_return_at'];
            $rental_history->note = $validated['note'];
            $rental_history->save();
            return redirect()
                ->route('rental.history')
                ->with('success_message', __('messages.registration_completed'));
        } catch (Exception $err) {
            Log::channel('error')->error(
                __('messages.registration_error'),
                [
                    'error_message' => $err->getMessage(),
                ]
            );
            return redirect()
                ->back()
                ->with('error_message', __('messages.registration_error'));
        }
    }
}
