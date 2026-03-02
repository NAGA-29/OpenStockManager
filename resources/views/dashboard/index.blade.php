@extends('layouts.app')

@section('content')
@endsection

@section('main_contents')
    <div class="col-md-12 shadow-sm pt-0 pr-0 pb-0 pl-0 mt-2 mb-2">
        <div class="device-bar rounded bg-dark text-white p-3">
            <div class="device-name h3 m-0">
                <i class="fas fa-home"></i> {{ __('ダッシュボード') }}
            </div>
        </div>
    </div>

    {{-- 貸出中台数カード --}}
    <div class="summary-cards mb-3 mt-3">
        <div class="summary-card-group">
            <div class="summary-card summary-card--primary">
                <span class="summary-card__label">貸出中台数</span>
                <span class="summary-card__value">{{ $lendingCount }}</span>
            </div>
            <div class="summary-card summary-card--danger">
                <span class="summary-card__label">延滞中</span>
                <span class="summary-card__value">{{ $overdue->count() }}</span>
            </div>
            <div class="summary-card summary-card--success">
                <span class="summary-card__label">期限間近（3日以内）</span>
                <span class="summary-card__value">{{ $nearDeadline->count() }}</span>
            </div>
        </div>
    </div>

    <div class="row gx-3">
        {{-- 延滞一覧 --}}
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-danger-soft text-dark fw-bold">
                    <i class="fas fa-exclamation-triangle"></i> {{ __('返却期限超過（延滞）') }}
                </div>
                <div class="card-body p-0">
                    @if ($overdue->isEmpty())
                        <p class="text-muted p-3 mb-0">延滞中のレンタルはありません。</p>
                    @else
                        <div class="table-responsive text-nowrap">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>レンタルID</th>
                                        <th>クライアント</th>
                                        <th>デバイス</th>
                                        <th>返却予定日</th>
                                        <th>超過日数</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($overdue as $rental)
                                        <tr>
                                            <td>
                                                <a href="{{ route('rental.rental_detail', ['id' => $rental->lend_id]) }}">
                                                    {{ $rental->lend_id }}
                                                </a>
                                            </td>
                                            <td>{{ $rental->clients->company ?? '-' }}</td>
                                            <td>
                                                @foreach ($rental->devices as $device)
                                                    <span class="badge bg-secondary">{{ $device->device_id }}</span>
                                                @endforeach
                                            </td>
                                            <td>{{ $rental->schedule_return_at->format('Y-m-d') }}</td>
                                            <td class="text-danger fw-bold">
                                                {{ $rental->overdue_days }}日超過
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- 期限間近一覧 --}}
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-warning-soft text-dark fw-bold">
                    <i class="fas fa-clock"></i> {{ __('貸出し期限間近（3日以内）') }}
                </div>
                <div class="card-body p-0">
                    @if ($nearDeadline->isEmpty())
                        <p class="text-muted p-3 mb-0">期限間近のレンタルはありません。</p>
                    @else
                        <div class="table-responsive text-nowrap">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>レンタルID</th>
                                        <th>クライアント</th>
                                        <th>デバイス</th>
                                        <th>返却予定日</th>
                                        <th>残日数</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($nearDeadline as $rental)
                                        <tr>
                                            <td>
                                                <a href="{{ route('rental.rental_detail', ['id' => $rental->lend_id]) }}">
                                                    {{ $rental->lend_id }}
                                                </a>
                                            </td>
                                            <td>{{ $rental->clients->company ?? '-' }}</td>
                                            <td>
                                                @foreach ($rental->devices as $device)
                                                    <span class="badge bg-secondary">{{ $device->device_id }}</span>
                                                @endforeach
                                            </td>
                                            <td>{{ $rental->schedule_return_at->format('Y-m-d') }}</td>
                                            <td>
                                                @if ($rental->remaining_days == 0)
                                                    <span class="text-danger fw-bold">本日</span>
                                                @else
                                                    <span class="text-warning fw-bold">あと{{ $rental->remaining_days }}日</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
