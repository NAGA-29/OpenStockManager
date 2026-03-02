<?php

namespace App\Http\Controllers;

// Request
use App\Http\Requests\StoreSaleCartRequest;
use App\Http\Requests\UpdateSaleHistoryRequest;
use App\Http\Requests\UploadSaleFileRequest;
use App\Models\Client;
// Model
use App\Models\Device;
use App\Models\Personnel;
use App\Models\SaleHist;
use App\Traits\SearchesClients;
// Facades
use App\Utils\GeneralUtil;
use Carbon\Carbon;
use Exception;
// Exception
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
// Utils
use Illuminate\Support\Facades\DB;
// Traits
use Illuminate\Support\Facades\Log;
// Enum
// Library
use Illuminate\Support\Str;
use League\Csv\Reader;
use League\Csv\Statement;

class SalesHistsController extends Controller
{
    use SearchesClients;

    /**
     * 機材販売情報の登録ページ表示
     *
     * @access public
     * @param $device_id : デバイスID
     * @return \Illuminate\View\View
     */
    public function saleWrite($device_id)
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
        // Clientテーブル全データ取得
        $clients = Client::all();
        return view('sales.sales', compact('device_info_collection', 'date_list', 'clients'));
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
     * 全機材販売情報の表示
     *
     * @access public
     * @return \Illuminate\View\View | \Illuminate\Http\RedirectResponse
     */
    public function getAllHistory(Request $request)
    {
        try {
            $query = SaleHist::with(['clients'])->orderBy('sale_date_at', 'desc');

            if ($request->filled('word')) {
                $keyword = '%' . addcslashes($request->word, '%_\\') . '%';
                $query->where(function ($q) use ($keyword) {
                    $q->where('note', 'like', $keyword)
                        ->orWhereHas('clients', fn ($q) => $q->where('company', 'like', $keyword));
                });
            }

            $histories = $query->paginate(10)->withQueryString();
            return view('history.all_sales_historys', compact('histories'));
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
     * 販売情報詳細
     * @access public
     * @param string $id : 販売ID
     * @return \Illuminate\View\View | \Illuminate\Http\RedirectResponse
     */
    public function getDetail(string $id)
    {
        try {
            $sales_details = SaleHist::find($id);
            if (!$sales_details) {
                abort(404, __('messages.sales_history_not_found'));
            }
            $collection = $sales_details->devices->toArray();
            return view('sales.sales_detail', compact('sales_details', 'collection'));
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
     * 複数端末販売用ファイルのアップロードページ
     * @access public
     * @return \Illuminate\View\View
     */
    public function multiIndex()
    {
        return view('sales.index');
    }

    /**
     * 販売ファイルのアップロードと解析
     * @access public
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\View\View | \Illuminate\Http\RedirectResponse
     */
    public function upload(UploadSaleFileRequest $request)
    {
        try {
            // csvアップロード処理
            $file_path = $request->file('csv_file')->getPathname();
            $csv = Reader::createFromPath($file_path, 'r')->setHeaderOffset(0);
            $stmt = Statement::create();
            $records = $stmt->process($csv);

            $device_ids = [];
            $lists = [];
            foreach ($records as $record) {
                $record = GeneralUtil::sanitizeCsvRecord($record);
                $device_info = Device::where('device_id', $record['device_id'])->first();
                if (!$device_info) {
                    throw new Exception(__('messages.device_not_exists', ['device_id' => $record['device_id']]));
                }
                if (!empty($device_info['sale_id'])) {
                    throw new Exception(__('messages.device_already_sold', ['device_id' => $device_info['device_id']]));
                }
                if (!empty($device_info['lending_now'])) {
                    throw new Exception(__('messages.device_currently_rented', ['device_id' => $device_info['device_id']]));
                }
                if ($device_info['defective'] != 0) {
                    throw new Exception(__('messages.device_defective', ['device_id' => $device_info['device_id']]));
                }
                if (in_array($device_info->device_id, $device_ids)) {
                    throw new Exception(__('messages.device_duplicate', ['device_id' => $device_info->device_id]));
                } else {
                    array_push($device_ids, $device_info->device_id);
                    array_push($lists, [$device_info, $record]);
                }
            }
        } catch (Exception $err) {
            Log::channel('error')->error(__('messages.sales_csv_failed'), [
                'error_message' => $err->getMessage(),
            ]);
            return redirect()->back()->with('error_message', $err->getMessage());
        }

        $request_data = $request->all();
        array_push($request_data, Personnel::where('personnel_id', $request['personnel'])->first());

        $request_info = [
            'client_id'             => $request_data['client_id'],
            'personnel_id'          => $request_data['personnel'],
            'sale_date_at'          => $request_data['sale_date_at'],
            'note'                  => $request_data['note'],
        ];
        $request->session()->put('request_data', $request_info);
        $request->session()->put('lists', $lists);
        return view('sales.multi_sale_confirm', compact('lists', 'request_data'));
    }

    /**
     * カート内の機材販売処理
     * @access public
     * @param \App\Http\Requests\StoreSaleCartRequest $request
     * @return \Illuminate\View\View
     */
    public function storeWithCart(StoreSaleCartRequest $request)
    {
        try {
            DB::beginTransaction();
            $safe = $request->safe()->all();
            $result = SaleHist::create([
                'sale_id'               => $safe['sale_id'],
                'client'                => $safe['client_id'],
                'personnel'             => $safe['personnel'],
                'staff'                 => Auth::id(),
                'sale_date_at'          => $safe['sale_date_at'],
                'note'                  => $safe['note'],
            ]);

            if (!$result) {
                throw new Exception(__('messages.cart_device_registration_failed'));
            }

            // 端末を販売済みに変更
            foreach ($safe['deviceIds'] as $device_id) {
                $device_update = [
                    'sale_id' => $result->sale_id,
                ];
                $_ = Device::where('device_id', $device_id)->update($device_update);
                if (!$_) {
                    throw new Exception(__('messages.rental_registration_failed'));
                }
                $result->devices()->attach($device_id, ['sale_date_at' => $safe['sale_date_at']]);
            }
            DB::commit();

            return redirect()
            ->route('sales.history')
            ->with('success_message', __('messages.registration_completed'))
            ->with('device_cart', 'RESET'); // NOTE: カートのリセット
        } catch (Exception $err) {
            DB::rollBack();
            Log::channel('error')->error(
                __('messages.sales_process_failed'),
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
     * 販売(複数)データのDB保存
     * @access public
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        // キャンセルボタンの場合
        if ($request->back === 'back') {
            // FIXME:セッションの削除が必要
            $request->session()->forget(['request_data', 'lists']);
            return redirect()->back();
        }
        // セッションがない場合はリダイレクト ※念の為
        if (!$request->session()->exists('request_data') || !$request->session()->exists('lists')) {
            return redirect()
            ->route('device.sale')
            ->with('error_message', __('messages.session_expired'));
        }

        try {
            DB::beginTransaction();
            $request_info = $request->session()->pull('request_data');
            $lists = $request->session()->pull('lists');
            $dt = Carbon::now();
            $sale_id = ($dt->timestamp) . '_' . (Str::random(5)); // create lend_id
            SaleHist::create([
                'sale_id'               => $sale_id,
                'client'                => $request_info['client_id'],
                'personnel'             => $request_info['personnel_id'],
                'staff'                 => Auth::id(),
                'sale_date_at'          => $request_info['sale_date_at'],
                'note'                  => $request_info['note'],
            ]);
            foreach ($lists as $list) {
                $device_update = [
                    'sale_id'           => $sale_id,
                    'using_user_id'     => $list[1]['user_id'],
                    'note'              => $list[0]->note, //CHANGE: noteがまっさらになってしまう不具合修正 2022/09/01
                ];

                Device::where('device_id', $list[0]['device_id'])->update($device_update);
                SaleHist::find($sale_id)->devices()->attach($list[0]['device_id'], ['sale_date_at' => $request_info['sale_date_at']]);
            }
            DB::commit();
            return redirect()
                ->route('device.sale')
                ->with('success_message', __('messages.registration_completed'));
        } catch (Exception $err) {
            DB::rollBack();
            Log::channel('error')->error(
                __('messages.registration_failed'),
                [
                    'error_message' => $err->getMessage(),
                ]
            );
            return redirect()
                ->route('device.sale')
                ->with('error_message', $err->getMessage());
        }
    }

    /** 販売履歴の修正
     * @access public
     * @param Request $request
     * @return UpdateSaleHistoryRequest $request
     */
    public function editSaleHistory(UpdateSaleHistoryRequest $request)
    {
        try {
            $safe = $request->safe()->all();
            $sale_history = SaleHist::where('sale_id', $safe['sale_id'])->first();
            $sale_history->sale_date_at = $safe['sale_date_at'];
            $sale_history->note = $safe['note'];
            $sale_history->save();

            return redirect()
                ->route('sales.history')
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
                ->with('error_message', __('messages.registration_error'))
                ->withInput();
        }
    }
}
