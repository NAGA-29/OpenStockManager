<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ClientController;
use App\Http\Controllers\Api\ContactController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\DeviceController;
use App\Http\Controllers\Api\InventoryStockController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| SPA(React)向けの JSON API ルート。認証は Laravel Sanctum の
| Personal Access Token 方式（`auth:sanctum`）。フロントは
| `Authorization: Bearer <token>` を付与する。
|
*/

// 認証不要
Route::post('/auth/login', [AuthController::class, 'login'])->name('api.auth.login');

// 認証必須（Sanctum トークン）
Route::middleware('auth:sanctum')->group(function () {
    // 認証
    Route::get('/auth/me', [AuthController::class, 'me'])->name('api.auth.me');
    Route::post('/auth/logout', [AuthController::class, 'logout'])->name('api.auth.logout');

    // ダッシュボード
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('api.dashboard');

    // 在庫 - 数量管理
    Route::get('/inventory/stocks', [InventoryStockController::class, 'index'])->name('api.inventory.stocks');

    // 在庫 - 個別管理（カテゴリ別一覧／端末詳細）
    Route::get('/devices/category/{code}', [DeviceController::class, 'byCategory'])->name('api.devices.category');
    // 端末登録（フォーム選択肢／単体登録）。`/devices/{deviceId}` より前に定義する。
    Route::get('/devices/form-options', [DeviceController::class, 'formOptions'])->name('api.devices.form_options');
    Route::post('/devices', [DeviceController::class, 'store'])->name('api.devices.store');
    Route::get('/devices/{deviceId}', [DeviceController::class, 'show'])->name('api.devices.show');

    // データ - クライアント
    Route::get('/clients', [ClientController::class, 'index'])->name('api.clients.index');
    Route::post('/clients', [ClientController::class, 'store'])->name('api.clients.store');
    Route::get('/clients/{clientId}', [ClientController::class, 'show'])->name('api.clients.show');

    // データ - 担当者
    Route::get('/contacts', [ContactController::class, 'index'])->name('api.contacts.index');
    Route::post('/contacts', [ContactController::class, 'store'])->name('api.contacts.store');
    Route::get('/contacts/{contactId}', [ContactController::class, 'show'])->name('api.contacts.show');
});
