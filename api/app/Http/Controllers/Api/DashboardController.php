<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Device;
use App\Models\RentalHist;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    /**
     * ダッシュボードの集計データを JSON で返す。
     */
    public function index(): JsonResponse
    {
        $today = Carbon::today();
        $todayStart = $today->copy()->startOfDay();
        $threeDaysLater = $today->copy()->addDays(3);

        // 貸出中台数
        $lendingCount = Device::whereNotNull('lending_now')->count();

        // 期限間近（今日〜3日後）
        $nearDeadline = RentalHist::where('all_returned', false)
            ->whereBetween('schedule_return_at', [$today, $threeDaysLater])
            ->with(['clients', 'devices', 'user'])
            ->orderBy('schedule_return_at', 'asc')
            ->get()
            ->map(function ($rental) use ($todayStart) {
                $scheduleDate = $rental->schedule_return_at->copy()->startOfDay();

                return $this->rentalResource($rental, [
                    'remaining_days' => (int) $todayStart->diffInDays($scheduleDate, false),
                ]);
            });

        // 延滞（期限超過）
        $overdue = RentalHist::where('all_returned', false)
            ->where('schedule_return_at', '<', $today)
            ->with(['clients', 'devices', 'user'])
            ->orderBy('schedule_return_at', 'asc')
            ->get()
            ->map(function ($rental) use ($todayStart) {
                $scheduleDate = $rental->schedule_return_at->copy()->startOfDay();

                return $this->rentalResource($rental, [
                    'overdue_days' => (int) $scheduleDate->diffInDays($todayStart),
                ]);
            });

        return response()->json([
            'lending_count' => $lendingCount,
            'near_deadline' => $nearDeadline,
            'overdue'       => $overdue,
        ]);
    }

    /**
     * @param  array<string, mixed>  $extra
     * @return array<string, mixed>
     */
    private function rentalResource(RentalHist $rental, array $extra = []): array
    {
        return array_merge([
            'lend_id'            => $rental->lend_id,
            'company'            => $rental->clients->company ?? null,
            'staff'             => $rental->user->name ?? null,
            'schedule_return_at' => optional($rental->schedule_return_at)->toDateString(),
            'device_count'       => $rental->devices->count(),
        ], $extra);
    }
}
