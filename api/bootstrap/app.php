<?php

use App\Exceptions\Domain\DeviceException;
use App\Exceptions\Infrastructure\CsvImportException;
use App\Exceptions\Infrastructure\ImageProcessingException;
use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\CorrelationIdMiddleware;
use App\Http\Middleware\EncryptCookies;
use App\Http\Middleware\TrustProxies;
use App\Http\Middleware\VerifyCsrfToken;
use App\Models\RentalHist;
use App\Providers\RouteServiceProvider;
use App\Services\ReturnDeadlineNotificationService;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Sentry\Laravel\Integration;
use Symfony\Component\HttpFoundation\Response;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->replace(\Illuminate\Http\Middleware\TrustProxies::class, TrustProxies::class);
        $middleware->trimStrings(['password', 'password_confirmation']);
        $middleware->redirectUsersTo(RouteServiceProvider::HOME);

        $middleware->web(
            append: [CorrelationIdMiddleware::class],
            replace: [
                \Illuminate\Cookie\Middleware\EncryptCookies::class => EncryptCookies::class,
                ValidateCsrfToken::class => VerifyCsrfToken::class,
            ],
        );

        $middleware->throttleApi();
        $middleware->api(
            append: [CorrelationIdMiddleware::class],
        );

        $middleware->alias([
            'admin' => AdminMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // 追加Exceptionの登録
        $exceptions->dontReport([
            DeviceException::class,
            CsvImportException::class,
            ImageProcessingException::class,
        ]);

        // セッションへ保存しない入力の追加
        $exceptions->dontFlash([
            'password',
            'password_confirmation',
            'email',
        ]);

        $exceptions->report(function (\Throwable $e): void {
            Integration::captureUnhandledException($e);
        });

        // API リクエスト（api/* もしくは JSON 期待）の判定。
        // SPA(React) からの呼び出しはリダイレクトでなく JSON で返す。
        $wantsJson = static fn (Request $request): bool => $request->is('api/*') || $request->expectsJson();

        $exceptions->render(function (DeviceException $e, Request $request) use ($wantsJson) {
            Log::channel('error')->error('device.exception.unhandled', [
                'error_message' => $e->getMessage(),
                'error_class' => $e::class,
                'context' => $e->getContext(),
            ]);

            if ($wantsJson($request)) {
                return response()->json([
                    'message' => $e->getMessage(),
                    'context' => $e->getContext(),
                ], 422);
            }

            return redirect()->back()->with('error_message', $e->getMessage());
        });

        $exceptions->render(function (ImageProcessingException $e, Request $request) use ($wantsJson) {
            Log::channel('error')->error('image.processing.exception.unhandled', [
                'error_message' => $e->getMessage(),
                'error_class' => $e::class,
                'context' => $e->getContext(),
            ]);

            if ($wantsJson($request)) {
                return response()->json([
                    'message' => $e->getMessage(),
                    'context' => $e->getContext(),
                ], 422);
            }

            return redirect()->back()->with('error_message', $e->getMessage());
        });

        $exceptions->respond(function (Response $response, \Throwable $e, Request $request) {
            // 419(CSRF/セッション切れ)は Blade のみログインへリダイレクト。
            // API はトークン方式のため 419 リダイレクトは行わず JSON のまま返す。
            if ($response->getStatusCode() === 419 && ! ($request->is('api/*') || $request->expectsJson())) {
                return redirect('login');
            }

            return $response;
        });
    })
    ->withSchedule(function (Schedule $schedule): void {
        $schedule->call(function (): void {
            $checkDays = [0, 3];
            $rental = new RentalHist();
            $notificationService = new ReturnDeadlineNotificationService();

            foreach ($checkDays as $day) {
                $message = $rental->deadLineCheck($day);

                if ($message) {
                    $notificationService->send($message);
                }
            }
        })->dailyAt('09:30');

        $schedule->command('backup:clean --disable-notifications')->weekly();
        $schedule->command('backup:run --disable-notifications --only-db')->weekly();
    })
    ->create();
