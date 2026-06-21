<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| 旧 Blade UI は React SPA（`frontend/`）へ全面移行済みのため、Laravel 側は
| JSON API（`routes/api.php`）専用とする。Web（セッション）UI ルートは持たない。
| SPA は別配信され、認証は Sanctum トークン方式（`auth:sanctum`）を用いる。
|
*/

// 旧 Blade UI ルートは撤去済み（Phase 4-1/4-2）。
