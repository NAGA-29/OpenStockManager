<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\RentalHist;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
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
                $rental->remaining_days = $todayStart->diffInDays($scheduleDate, false);

                return $rental;
            });

        // 延滞（期限超過）
        $overdue = RentalHist::where('all_returned', false)
            ->where('schedule_return_at', '<', $today)
            ->with(['clients', 'devices', 'user'])
            ->orderBy('schedule_return_at', 'asc')
            ->get()
            ->map(function ($rental) use ($todayStart) {
                $scheduleDate = $rental->schedule_return_at->copy()->startOfDay();
                $rental->overdue_days = $scheduleDate->diffInDays($todayStart);

                return $rental;
            });

        return view('dashboard.index', compact('lendingCount', 'nearDeadline', 'overdue'));
    }
}
