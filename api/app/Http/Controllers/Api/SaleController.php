<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSaleApiRequest;
use App\Http\Requests\UploadSaleMultiApiRequest;
use App\Http\Requests\StoreSaleMultiApiRequest;
use App\Models\SaleHist;
use App\Models\Device;
use App\Models\Client;
use App\Traits\Keyword;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use League\Csv\Reader;
use League\Csv\Statement;
use Ramsey\Uuid\Uuid;

class SaleController extends Controller
{
    use Keyword;

    /**
     * 販売一覧の取得。
     */
    public function index(Request $request): JsonResponse
    {
        $query = SaleHist::with(['clients', 'contacts', 'user', 'devices'])
            ->orderBy('sale_date_at', 'desc');

        if ($request->filled('word')) {
            $keyword = '%' . addcslashes($request->word, '%_\\') . '%';
            $query->where(function ($q) use ($keyword) {
                $q->where('note', 'like', $keyword)
                    ->orWhereHas('clients', fn ($q) => $q->where('company', 'like', $keyword));
            });
        }

        $sales = $query->paginate(10);

        return response()->json([
            'data' => $sales->items(),
            'meta' => [
                'current_page' => $sales->currentPage(),
                'last_page' => $sales->lastPage(),
                'per_page' => $sales->perPage(),
                'total' => $sales->total(),
            ],
        ]);
    }

    /**
     * 販売登録。
     */
    public function store(StoreSaleApiRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $safe = $request->safe()->all();
            $saleId = 'SALE-' . Uuid::uuid4()->toString();

            // SaleHist レコード作成
            $sale = SaleHist::create([
                'sale_id' => $saleId,
                'client' => $safe['client_id'],
                'contact' => $safe['contact_id'],
                'staff' => Auth::id(),
                'sale_date_at' => $safe['sale_date_at'],
                'note' => $safe['note'] ?? null,
            ]);

            // 各端末の販売状態を更新し、pivot に sale_date_at を記録
            foreach ($safe['device_ids'] as $deviceId) {
                Device::where('device_id', $deviceId)->update([
                    'sale_id' => $saleId,
                ]);
                $sale->devices()->attach($deviceId, ['sale_date_at' => $safe['sale_date_at']]);
            }

            DB::commit();

            return response()->json([
                'data' => $sale->load(['clients', 'contacts', 'devices']),
                'message' => '販売登録が完了しました。',
            ], 201);
        } catch (\Exception $err) {
            DB::rollBack();
            Log::channel('error')->error('Sale registration failed', [
                'error_message' => $err->getMessage(),
            ]);

            return response()->json([
                'message' => '販売登録に失敗しました。',
                'error' => $err->getMessage(),
            ], 500);
        }
    }

    /**
     * CSV ファイルから販売端末をパース・プレビュー。
     */
    public function uploadMulti(UploadSaleMultiApiRequest $request): JsonResponse
    {
        try {
            $filePath = $request->file('sale_file')->getPathname();
            $csv = Reader::createFromPath($filePath, 'r')->setHeaderOffset(0);
            $stmt = Statement::create();
            $records = $stmt->process($csv);

            $previews = [];
            foreach ($records as $record) {
                $record = array_map('trim', $record);
                $deviceId = $record['device_id'] ?? null;

                if (!$deviceId) {
                    continue;
                }

                $device = Device::where('device_id', $deviceId)->first();
                if (!$device) {
                    continue;
                }

                $previews[] = [
                    'device_id' => $device->device_id,
                    'device_type' => $device->device_type,
                    'device_name' => $device->device_name,
                    'device_serial' => $device->device_serial,
                    'condition' => $device->condition,
                ];
            }

            return response()->json([
                'data' => $previews,
                'count' => count($previews),
            ]);
        } catch (\Exception $err) {
            Log::channel('error')->error('Sale CSV parse failed', [
                'error_message' => $err->getMessage(),
            ]);

            return response()->json([
                'message' => 'CSVファイルの解析に失敗しました。',
                'error' => $err->getMessage(),
            ], 422);
        }
    }

    /**
     * 一括販売保存。
     */
    public function storeMulti(StoreSaleMultiApiRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $safe = $request->safe()->all();
            $saleId = 'SALE-' . Uuid::uuid4()->toString();

            // SaleHist レコード作成
            $sale = SaleHist::create([
                'sale_id' => $saleId,
                'client' => $safe['client_id'],
                'contact' => $safe['contact_id'],
                'staff' => Auth::id(),
                'sale_date_at' => $safe['sale_date_at'],
                'note' => $safe['note'] ?? null,
            ]);

            // 各端末を登録
            foreach ($safe['sales'] as $saleItem) {
                $deviceId = $saleItem['device_id'];
                Device::where('device_id', $deviceId)->update([
                    'sale_id' => $saleId,
                ]);
                $sale->devices()->attach($deviceId, ['sale_date_at' => $safe['sale_date_at']]);
            }

            DB::commit();

            return response()->json([
                'data' => $sale->load(['clients', 'contacts', 'devices']),
                'count' => count($safe['sales']),
                'message' => count($safe['sales']) . '件の販売登録が完了しました。',
            ], 201);
        } catch (\Exception $err) {
            DB::rollBack();
            Log::channel('error')->error('Bulk sale registration failed', [
                'error_message' => $err->getMessage(),
            ]);

            return response()->json([
                'message' => '一括販売登録に失敗しました。',
                'error' => $err->getMessage(),
            ], 500);
        }
    }

    /**
     * 販売履歴一覧。
     */
    public function history(Request $request): JsonResponse
    {
        $query = SaleHist::with(['clients', 'contacts', 'user', 'devices'])
            ->orderBy('sale_date_at', 'desc');

        if ($request->filled('word')) {
            $keywords = $this->extractKeywords($request->word);
            foreach ($keywords as $keyword) {
                $keyword = '%' . addcslashes($keyword, '%_\\') . '%';
                $query->where(function ($q) use ($keyword) {
                    $q->where('note', 'like', $keyword)
                        ->orWhereHas('clients', fn ($q) => $q->where('company', 'like', $keyword));
                });
            }
        }

        $histories = $query->paginate(10);

        return response()->json([
            'data' => $histories->items(),
            'meta' => [
                'current_page' => $histories->currentPage(),
                'last_page' => $histories->lastPage(),
                'per_page' => $histories->perPage(),
                'total' => $histories->total(),
            ],
        ]);
    }

    /**
     * 販売履歴詳細。
     */
    public function historyDetail(string $saleId): JsonResponse
    {
        $sale = SaleHist::with(['clients', 'contacts', 'user', 'devices'])->find($saleId);

        if (!$sale) {
            return response()->json([
                'message' => '販売履歴が見つかりません。',
            ], 404);
        }

        return response()->json([
            'data' => $sale,
        ]);
    }
}
