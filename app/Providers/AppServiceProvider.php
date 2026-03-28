<?php

namespace App\Providers;

use App\Models\DeviceCategory;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
// Eloquent Model
use Illuminate\Database\Eloquent\Model;
// Library for Sentry integration
use Sentry\Laravel\Integration;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Paginator::useBootstrap();

        // 編集モーダルに動的デバイスカテゴリを共有
        View::composer('component.modal.edit_device_info', function ($view) {
            $view->with('deviceCategories', DeviceCategory::active()->ordered()->get());
        });

        // N+1問題を防止するための設定
        // 本番環境以外ではlazy loadingを禁止する
        Model::preventLazyLoading(! app()->isProduction());

        // lazy loadingが発生した場合のハンドリング
        if (app()->isProduction()) {
            Model::handleLazyLoadingViolationUsing(
                Integration::lazyLoadingViolationReporter()
            );
        }
    }
}
