<?php

namespace App\Exceptions;

use App\Exceptions\Domain\DeviceException;
use App\Exceptions\Infrastructure\CsvImportException;
use App\Exceptions\Infrastructure\ImageProcessingException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Log;
use Sentry\Laravel\Integration;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        DeviceException::class,
        CsvImportException::class,
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            Integration::captureUnhandledException($e);
        });
    }

    /**
     * Render an exception into an HTTP response.
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
