<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRentalApiRequest;
use App\Http\Requests\UploadRentalMultiApiRequest;
use App\Http\Requests\StoreRentalMultiApiRequest;
use App\Models\RentalHist;
use App\Models\Device;
use App\Models\Client;
use App\Traits\Keyword;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use League\Csv\Reader;
use League\Csv\Statement;
use Ramsey\Uuid\Uuid;

class RentalController extends Controller
{
    use Keyword;

    /**
     * レンタル一覧（貸出中・返却予定）の取得。
     */
    public function index(Request $request): JsonResponse
    {
        $query = RentalHist::with(['clients', 'contacts', 'user', 'devices'])
            ->orderBy('checkout_at', 'desc');

        if ($request->filled('word')) {
            $keyword = '%' . addcslashes($request->word, '%_\\') . '%';
            $query->where(function ($q) use ($keyword) {
                $q->where('note', 'like', $keyword)
                    ->orWhereHas('clients', fn ($q) => $q->where('company', 'like', $keyword));
            });
        }

        $rentals = $query->paginate(10);

        return response()->json([
            'data' => $rentals->items(),
            'meta' => [
                'current_page' => $rentals->currentPage(),
                'last_page' => $rentals->lastPage(),
                'per_page' => $rentals->perPage(),
                'total' => $rentals->total(),
            ],
        ]);
    }

    /**
     * レンタル登録。
     */
    public function store(StoreRentalApiRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $safe = $request->safe()->all();
            $lendId = 'RENT-' . Uuid::uuid4()->toString();

            // RentalHist レコード作成
            $rental = RentalHist::create([
                'lend_id' => $lendId,
                'client' => $safe['client_id'],
                'contact' => $safe['contact_id'],
                'staff' => Auth::id(),
                'all_returned' => 0,
                'checkout_at' => $safe['checkout_at'],
                'schedule_return_at' => $safe['schedule_return_at'],
                'note' => $safe['note'] ?? null,
            ]);

            // 各端末の貸出状態を更新し、pivot に checkout_at を記録
            foreach ($safe['device_ids'] as $deviceId) {
                Device::where('device_id', $deviceId)->update([
                    'lending_now' => $lendId,
                ]);
                $rental->devices()->attach($deviceId, ['checkout_at' => $safe['checkout_at']]);
            }

            DB::commit();

            return response()->json([
                'data' => $rental->load(['clients', 'contacts', 'devices']),
                'message' => 'レンタル登録が完了しました。',
            ], 201);
        } catch (\Exception $err) {
            DB::rollBack();
            Log::channel('error')->error('Rental registration failed', [
                'error_message' => $err->getMessage(),
            ]);

            return response()->json([
                'message' => 'レンタル登録に失敗しました。',
                'error' => $err->getMessage(),
            ], 500);
        }
    }

    /**
     * CSV ファイルからレンタル端末をパース・プレビュー。
     */
    public function uploadMulti(UploadRentalMultiApiRequest $request): JsonResponse
    {
        try {
            $filePath = $request->file('rental_file')->getPathname();
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
            Log::channel('error')->error('Rental CSV parse failed', [
                'error_message' => $err->getMessage(),
            ]);

            return response()->json([
                'message' => 'CSVファイルの解析に失敗しました。',
                'error' => $err->getMessage(),
            ], 422);
        }
    }

    /**
     * 一括レンタル保存。
     */
    public function storeMulti(StoreRentalMultiApiRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $safe = $request->safe()->all();
            $lendId = 'RENT-' . Uuid::uuid4()->toString();

            // RentalHist レコード作成
            $rental = RentalHist::create([
                'lend_id' => $lendId,
                'client' => $safe['client_id'],
                'contact' => $safe['contact_id'],
                'staff' => Auth::id(),
                'all_returned' => 0,
                'checkout_at' => $safe['checkout_at'],
                'schedule_return_at' => $safe['schedule_return_at'],
                'note' => $safe['note'] ?? null,
            ]);

            // 各端末を登録
            foreach ($safe['rentals'] as $rentalItem) {
                $deviceId = $rentalItem['device_id'];
                Device::where('device_id', $deviceId)->update([
                    'lending_now' => $lendId,
                ]);
                $rental->devices()->attach($deviceId, ['checkout_at' => $safe['checkout_at']]);
            }

            DB::commit();

            return response()->json([
                'data' => $rental->load(['clients', 'contacts', 'devices']),
                'count' => count($safe['rentals']),
                'message' => count($safe['rentals']) . '件のレンタル登録が完了しました。',
            ], 201);
        } catch (\Exception $err) {
            DB::rollBack();
            Log::channel('error')->error('Bulk rental registration failed', [
                'error_message' => $err->getMessage(),
            ]);

            return response()->json([
                'message' => '一括レンタル登録に失敗しました。',
                'error' => $err->getMessage(),
            ], 500);
        }
    }

    /**
     * レンタル履歴一覧。
     */
    public function history(Request $request): JsonResponse
    {
        $query = RentalHist::with(['clients', 'contacts', 'user', 'devices'])
            ->orderBy('checkout_at', 'desc');

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
     * レンタル履歴詳細。
     */
    public function historyDetail(string $lendId): JsonResponse
    {
        $rental = RentalHist::with(['clients', 'contacts', 'user', 'devices'])->find($lendId);

        if (!$rental) {
            return response()->json([
                'message' => 'レンタル履歴が見つかりません。',
            ], 404);
        }

        return response()->json([
            'data' => $rental,
        ]);
    }

    /**
     * 端末の返却処理。
     */
    public function returnDevice(Request $request, string $lendId): JsonResponse
    {
        try {
            DB::beginTransaction();

            $rental = RentalHist::with('devices')->find($lendId);
            if (!$rental) {
                DB::rollBack();
                return response()->json([
                    'message' => 'レンタル履歴が見つかりません。',
                ], 404);
            }

            $deviceId = $request->device_id;
            $returnAt = $request->return_at ?? Carbon::now()->format('Y-m-d');

            // pivot テーブルを更新
            $rental->devices()->updateExistingPivot($deviceId, ['return_at' => $returnAt]);

            // 全端末が返却されたか確認
            $rental->refresh();
            $unreturned = $rental->devices()
                ->wherePivotNull('return_at')
                ->count();

            if ($unreturned === 0) {
                $rental->update([
                    'return_at' => $returnAt,
                    'all_returned' => 1,
                ]);
            }

            // 端末の貸出状態をクリア
            Device::where('device_id', $deviceId)->update([
                'lending_now' => '',
            ]);

            DB::commit();

            return response()->json([
                'data' => $rental->load(['clients', 'contacts', 'devices']),
                'message' => '返却処理が完了しました。',
            ]);
        } catch (\Exception $err) {
            DB::rollBack();
            Log::channel('error')->error('Rental return failed', [
                'error_message' => $err->getMessage(),
                'trace' => $err->getTraceAsString(),
            ]);

            return response()->json([
                'message' => '返却処理に失敗しました。',
                'error' => $err->getMessage(),
            ], 500);
        }
    }
}
