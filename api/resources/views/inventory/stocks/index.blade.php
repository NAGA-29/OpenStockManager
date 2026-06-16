@extends('layouts.app')

@section('content')
@endsection

@section('main_contents')
    <div class="container-fluid py-4">
        <div class="d-flex align-items-center mb-4">
            <i class="fas fa-boxes fa-lg me-2 text-success"></i>
            <h4 class="mb-0">数量管理</h4>
        </div>

        <div class="alert alert-info d-flex align-items-start" role="alert">
            <i class="fas fa-info-circle me-2 mt-1"></i>
            <div>
                <strong>数量管理</strong>とは、ロケーション × 品目ごとに在庫数をまとめて管理する方式です。<br>
                個体を特定せず、入庫・出庫・調整によって数量を増減させます。
            </div>
        </div>

        <div class="bg-white rounded shadow p-5 text-center text-muted">
            <i class="fas fa-tools fa-3x mb-3"></i>
            <p class="mb-0">この機能は現在開発中です。</p>
        </div>
    </div>
@endsection
