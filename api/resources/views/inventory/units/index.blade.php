@extends('layouts.app')

@section('content')
@endsection

@section('main_contents')
    <div class="container-fluid py-4">
        <div class="d-flex align-items-center mb-4">
            <i class="fas fa-barcode fa-lg me-2 text-primary"></i>
            <h4 class="mb-0">個別管理</h4>
        </div>

        <div class="alert alert-info d-flex align-items-start" role="alert">
            <i class="fas fa-info-circle me-2 mt-1"></i>
            <div>
                <strong>個別管理</strong>とは、シリアル番号・個体 ID で各アイテムを 1 件ずつ追跡する管理方式です。<br>
                貸出・返却・状態履歴をアイテム単位で記録できます。
            </div>
        </div>

        <div class="bg-white rounded shadow p-5 text-center text-muted">
            <i class="fas fa-tools fa-3x mb-3"></i>
            <p class="mb-0">この機能は現在開発中です。</p>
        </div>
    </div>
@endsection
