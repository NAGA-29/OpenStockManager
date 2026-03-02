<?php

namespace App\Providers;

use App\Models\DeviceCategory;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

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
    }
}
