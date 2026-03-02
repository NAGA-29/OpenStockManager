<?php

use Monolog\Formatter\JsonFormatter;
use Monolog\Handler\NullHandler;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\SyslogUdpHandler;
use Monolog\Processor\PsrLogMessageProcessor;

return [

    /*
    |--------------------------------------------------------------------------
    | Default Log Channel
    |--------------------------------------------------------------------------
    |
    | This option defines the default log channel that gets used when writing
    | messages to the logs. The name specified in this option should match
    | one of the channels defined in the "channels" configuration array.
    |
    */

    'default' => env('LOG_CHANNEL', 'stack'),

    /*
    |--------------------------------------------------------------------------
    | Log Channels
    |--------------------------------------------------------------------------
    |
    | Here you may configure the log channels for your application. Out of
    | the box, Laravel uses the Monolog PHP logging library. This gives
    | you a variety of powerful log handlers / formatters to utilize.
    |
    | Available Drivers: "single", "daily", "slack", "syslog",
    |                    "errorlog", "monolog",
    |                    "custom", "stack"
    |
    */

    'channels' => [
        'stack' => [
            'driver' => 'stack',
            'channels' => ['daily'],
            'ignore_exceptions' => false,
        ],

        // Sentry エラー監視
        'sentry' => [
            'driver' => 'sentry',
            'level' => env('SENTRY_LOG_LEVEL', 'error'),
            'bubble' => true,
        ],

        'single' => [
            'driver' => 'single',
            'path' => storage_path('logs/laravel.log'),
            'level' => env('LOG_LEVEL', 'debug'),
        ],

        'daily' => [
            'driver' => 'daily',
            'tap' => [App\Logging\JsonFormatter::class],
            'path' => storage_path('logs/daily.log'),
            'level' => env('LOG_LEVEL', 'info'),
            'days' => 30,
        ],

        'daily_json' => [
            'driver' => 'stack',
            'channels' => ['daily_json_monolog', 'sentry'],
            'ignore_exceptions' => false,
        ],

        'daily_json_monolog' => [
            'driver' => 'monolog',
            'level' => env('LOG_LEVEL', 'debug'),
            'handler' => RotatingFileHandler::class,
            'handler_with' => [
                'filename' => storage_path('logs/laravel.log'),
                'maxFiles' => (int) env('LOG_DAILY_DAYS', 30),
            ],
            'formatter' => JsonFormatter::class,
            'processors' => [PsrLogMessageProcessor::class],
        ],

        // ログインユーザー記録
        'login' => [
            'driver' => 'daily',
            'tap' => [App\Logging\JsonFormatter::class],
            'path' => storage_path('logs/login/user/user.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 90,
        ],

        // 操作ログ
        'operation' => [
            'driver' => 'daily',
            'tap' => [App\Logging\AddUserContext::class, App\Logging\JsonFormatter::class],
            'path' => storage_path('logs/operation/user/operation.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 90,
        ],

        // エラーログ
        'error' => [
            'driver' => 'daily',
            'tap' => [App\Logging\AddUserContext::class, App\Logging\JsonFormatter::class],
            'path' => storage_path('logs/error/user/error.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 90,
            'include_source' => true, // 重要
        ],

        'slack' => [
            'driver' => 'slack',
            'url' => env('LOG_SLACK_WEBHOOK_URL'),
            'username' => 'Laravel Log',
            'emoji' => ':boom:',
            'level' => env('LOG_LEVEL', 'critical'),
        ],

        'papertrail' => [
            'driver' => 'monolog',
            'level' => env('LOG_LEVEL', 'debug'),
            'handler' => SyslogUdpHandler::class,
            'handler_with' => [
                'host' => env('PAPERTRAIL_URL'),
                'port' => env('PAPERTRAIL_PORT'),
            ],
        ],

        'stderr' => [
            'driver' => 'monolog',
            'handler' => StreamHandler::class,
            'formatter' => env('LOG_STDERR_FORMATTER'),
            'with' => [
                'stream' => 'php://stderr',
            ],
        ],

        'syslog' => [
            'driver' => 'syslog',
            'level' => env('LOG_LEVEL', 'debug'),
        ],

        'errorlog' => [
            'driver' => 'errorlog',
            'level' => env('LOG_LEVEL', 'debug'),
        ],

        'null' => [
            'driver' => 'monolog',
            'handler' => NullHandler::class,
        ],

        'emergency' => [
            'path' => storage_path('logs/laravel.log'),
        ],
    ],

];
