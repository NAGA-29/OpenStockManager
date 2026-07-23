<?php

namespace App\Providers;

use App\Services\Messaging\Chatwork;
use App\Services\Messaging\Slack;
use Illuminate\Support\ServiceProvider;

class NotificationServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        app()->bind('chatwork', function ($app, $arg) {
            return new Chatwork($arg[0]);
        });

        app()->bind('slack', function ($app, $arg) {
            return new Slack($arg['token']);
        });
    }
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
