<?php

use App\Http\Controllers\ClientsController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DeviceCategoryController;
use App\Http\Controllers\DevicesController;
use App\Http\Controllers\DeviceTypeFieldController;
use App\Http\Controllers\InventoryStockController;
use App\Http\Controllers\InventoryUnitController;
use App\Http\Controllers\MailingController;
use App\Http\Controllers\ContactsController;
use App\Http\Controllers\RentalHistsController;
use App\Http\Controllers\SalesHistsController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');

Auth::routes(['register' => false]);

Route::group(['middleware' => 'auth'], function () {
    // ダッシュボード
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // マイページ
    Route::get('/profile', [UserController::class, 'index'])->name('profile');
    Route::post('/profile/email/change', [UserController::class, 'changeEmail'])->name('profile.email.change');
    Route::get('/profile/email/verify', [UserController::class, 'verifyNewEmail'])->name('profile.email.verify');

    // ユーザー管理 (管理者のみ)
    Route::group(['middleware' => 'admin'], function () {
        Route::get('/users', [UserController::class, 'userList'])->name('user.list');
        Route::get('/users/register', [UserController::class, 'register'])->name('user.register');
        Route::post('/users/register', [UserController::class, 'store'])->name('user.store');
        Route::post('/users/update', [UserController::class, 'update'])->name('user.update');
    });

    // 在庫一覧 - 個別管理
    // Route::get('/inventory/units', [InventoryUnitController::class, 'index'])->name('inventory.units.index');

    // 在庫一覧 - 数量管理
    Route::get('/inventory/stocks', [InventoryStockController::class, 'index'])->name('inventory.stocks.index');

    // 在庫一覧（個別管理・動的カテゴリ）
    Route::get('/inventory/units/{code}', [DevicesController::class, 'deviceListByCategory'])->name('inventory.units.category');
    // Route::get('/device/list', [DevicesController::class, 'deviceListDefault'])->name('device.list');
    Route::get('/devices/{device_id}', [DevicesController::class, 'deviceIndividual'])->name('device.individual');
    Route::post('/devices', [DevicesController::class, 'updateDevice'])->name('device.update');
    Route::get('/devices/{device_id}/barcode', [DevicesController::class, 'barcodePrint'])->name('device.barcode');
    Route::get('/devices/search/', [DevicesController::class, 'searchDevice'])->name('device.search.IDorSerial');

    // 機材カテゴリ管理 (管理者のみ)
    Route::group(['middleware' => 'admin', 'prefix' => 'device/categories'], function () {
        Route::get('/', [DeviceCategoryController::class, 'index'])->name('device_categories.index');
        Route::post('/', [DeviceCategoryController::class, 'store'])->name('device_categories.store');
        Route::put('/{id}', [DeviceCategoryController::class, 'update'])->name('device_categories.update');
        Route::delete('/{id}', [DeviceCategoryController::class, 'destroy'])->name('device_categories.destroy');
        Route::post('/reorder', [DeviceCategoryController::class, 'reorder'])->name('device_categories.reorder');
    });

    // カスタムフィールド管理 (管理者のみ)
    Route::group(['middleware' => 'admin', 'prefix' => 'device/fields'], function () {
        Route::get('/', [DeviceTypeFieldController::class, 'index'])->name('device_fields.index');
        Route::post('/', [DeviceTypeFieldController::class, 'store'])->name('device_fields.store');
        Route::put('/{id}', [DeviceTypeFieldController::class, 'update'])->name('device_fields.update');
        Route::delete('/{id}', [DeviceTypeFieldController::class, 'destroy'])->name('device_fields.destroy');
        Route::post('/reorder', [DeviceTypeFieldController::class, 'reorder'])->name('device_fields.reorder');
    });

    // カスタムフィールド取得 AJAX (認証済みユーザー)
    Route::get('/device/fields/{code}', [DeviceTypeFieldController::class, 'getByCategory'])->name('device_fields.by_category');

    // 販売履歴
    Route::get('/device/sale/history/', [SalesHistsController::class, 'getAllHistory'])->name('sales.history');
    Route::get('/device/sale/history/{id}', [SalesHistsController::class, 'getDetail'])->name('sales.sales_detail');
    Route::post('/device/sale/history/edit/', [SalesHistsController::class, 'editSaleHistory'])->name('sales.history.edit');

    // レンタル履歴
    Route::get('/device/rental/history/', [RentalHistsController::class, 'getAllHistory'])->name('rental.history');
    Route::get('/device/rental/history/{id}', [RentalHistsController::class, 'getDetail'])->name('rental.rental_detail');
    Route::post('/device/rental/history/edit/', [RentalHistsController::class, 'editRentalHistory'])->name('rental.edit');

    // デバイス登録 単数
    Route::get('/device/register', [DevicesController::class, 'registerDevice'])->name('device.register');
    Route::post('/device/register', [DevicesController::class, 'storeDevice'])->name('device.save');

    // デバイス登録 複数
    Route::get('/device/register/multi', [DevicesController::class, 'registerDeviceMulti'])->name('device.register_multi');
    Route::post('/device/register/multi', [DevicesController::class, 'confirmMulti'])->name('device.confirm_multi');
    Route::post('/device/register/multi/store', [DevicesController::class, 'storeDeviceMulti'])->name('device.store_multi');
    Route::get('/device/register/multi/download', [DevicesController::class, 'download'])->name('device.file.register.download');

    // デバイス関連ファイル
    Route::get('/device/file/spec', [DevicesController::class, 'getSpecFile'])->name('device.file.spec');
    Route::post('/device/file/spec', [DevicesController::class, 'specUpload'])->name('device.file.spec.upload');
    Route::get('/device/file/spec/download', [DevicesController::class, 'download'])->name('device.file.spec.download');
    Route::get('/device/file/benchmark', [DevicesController::class, 'getBenchMarkFile'])->name('device.file.benchmark');
    Route::post('/device/file/benchmark', [DevicesController::class, 'benchmarkUpload'])->name('device.file.benchmark.upload');
    Route::get('/device/file/benchmark/download', [DevicesController::class, 'download'])->name('device.file.benchmark.download');

    // 企業登録
    Route::get('/client/list', [ClientsController::class, 'getAllClient'])->name('client.list');
    Route::get('/client/register', [ClientsController::class, 'form'])->name('client.register_form');
    Route::post('/client/register', [ClientsController::class, 'register'])->name('client.register');
    Route::post('/client/edit', [ClientsController::class, 'edit'])->name('client.edit');
    Route::get('/client/id/{client_id}', [ClientsController::class, 'clientDetails'])->name('client.details');
    Route::post('/client/search', [ClientsController::class, 'searchClient'])->name('client.search');

    // 担当者登録
    Route::get('/personnel/list', [ContactsController::class, 'getAllContacts'])->name('personnel.list');
    Route::get('/personnel/register', [ContactsController::class, 'form'])->name('personnel.register_form');
    Route::post('/personnel/client/search', [ContactsController::class, 'searchClient'])->name('personnel.search.client');
    Route::post('/personnel/register', [ContactsController::class, 'register'])->name('personnel.register');
    Route::get('/personnel/detail/{contact_id}', [ContactsController::class, 'contactDetail'])->name('personnel.detail');

    // レンタル File形式(複数)
    Route::post('/device/rental/multi/upload', [RentalHistsController::class, 'upload'])->name('device.multi_csv_upload');
    Route::post('/device/rental/multi/store', [RentalHistsController::class, 'storeWithFile'])->name('device.multi_csv_store');
    Route::get('/device/rental/multi/download', [RentalHistsController::class, 'download'])->name('device.multi_csv_download');
    Route::get('/device/rental/multi/return_confirm/{lend_id}', [RentalHistsController::class, 'confirmReturnDeviceMulti'])->name('device.multi_return_device_confirm');
    Route::post('/search/personnel', [RentalHistsController::class, 'getPersonnel'])->name('search.personnel');
    // 一括返却機能
    Route::post('/device/rental/multi/return_complete/{lend_id}', [RentalHistsController::class, 'storeReturnDeviceMulti'])->name('device.multi_return_device_complete');

    // レンタル カート形式(単数)
    Route::get('/device/rental', [RentalHistsController::class, 'rental'])->name('device.rental');
    Route::post('/device/rental/store', [RentalHistsController::class, 'storeWithCart'])->name('device.rental.store');
    Route::post('/device/rental/return', [RentalHistsController::class, 'return'])->name('device.return');
    // Route::post('/device/rental/client/search', [RentalHistsController::class, 'searchClient'])->name('device.search.client');
    // Route::post('/device/rental/client/search/personnel', [RentalHistsController::class, 'getPersonnel'])->name('device.search.client.personnel');
    Route::get('/device/rental/{device_id}', [RentalHistsController::class, 'checkOutWrite'])->name('device.checkout_write');

    // 販売 複数
    Route::get('/device/sale', [SalesHistsController::class, 'multiIndex'])->name('device.sale');
    Route::post('/device/sales/multi/upload', [SalesHistsController::class, 'upload'])->name('device.multi_sales_csv_upload');
    Route::post('/device/sales/multi/store', [SalesHistsController::class, 'store'])->name('device.multi_sales_csv_store');

    // 販売 単体
    // Route::post('/device/sales', [SalesHistsController::class, 'sell'])->name('device.sell'); // CHANGE: Delete 2024-09-25
    Route::post('/device/sales/store', [SalesHistsController::class, 'storeWithCart'])->name('device.sale.store');
    // Route::post('/device/sales/client/search', [SalesHistsController::class, 'searchClient'])->name('sales.search.client');
    // Route::post('/device/sales/client/search/personnel', [SalesHistsController::class, 'getPersonnel'])->name('sales.search.client.personnel');
    Route::get('/device/sales/{device_id}', [SalesHistsController::class, 'saleWrite'])->name('sales.sales_write');

    // メール送信・CRM同期 (管理者のみ)
    Route::group(['middleware' => 'admin'], function () {
        Route::get('/mail', [MailingController::class, 'index']);
        Route::post('/sendmail', [MailingController::class, 'sendMail']);
        Route::get('/sync/crm', [ClientsController::class, 'syncFromCRM'])->name('synchronize.clients');
    });
    // Route::get('/sync/personnel', [PersonnelsController::class, 'synchronizePersonnel'])->name('synchronize.personnel');
});
