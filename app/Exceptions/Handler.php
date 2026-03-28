<?php

namespace App\Exceptions;

use Throwable;
use App\Exceptions\Domain\DeviceException;
use App\Exceptions\Infrastructure\CsvImportException;
use App\Exceptions\Infrastructure\ImageProcessingException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Session\TokenMismatchException;
// Facade
use Illuminate\Support\Facades\Log;
// Sentry
use Sentry\Laravel\Integration;

class Handler extends ExceptionHandler
{
    /**
     * 報告されない例外タイプのリスト
     *
     * @var array
     */
    protected $dontReport = [
        DeviceException::class,
        CsvImportException::class,
    ];

    /**
     * バリデーション例外でフラッシュされない入力のリスト
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * アプリケーションの例外ハンドリングコールバックを登録する
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            Integration::captureUnhandledException($e); // Sentryに例外を送信
        });
    }

    /**
     * 例外をHTTPレスポンスに変換する
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $exception
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Throwable
     */
    public function render($request, Throwable $exception)
    {
        if ($exception instanceof TokenMismatchException) {
            // return redirect('user/login');       // 追加-> このURLは存在しなかったので修正
            return redirect('login');
        }

        if ($exception instanceof DeviceException) {
            Log::channel('error')->error('device.exception.unhandled', [
                'error_message' => $exception->getMessage(),
                'error_class' => get_class($exception),
                'context' => $exception->getContext(),
            ]);
            return redirect()->back()->with('error_message', $exception->getMessage());
        }

        if ($exception instanceof ImageProcessingException) {
            Log::channel('error')->error('image.processing.exception.unhandled', [
                'error_message' => $exception->getMessage(),
                'error_class' => get_class($exception),
                'context' => $exception->getContext(),
            ]);
            return redirect()->back()->with('error_message', $exception->getMessage());
        }

        return parent::render($request, $exception);
    }
}
