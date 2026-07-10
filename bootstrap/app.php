<?php

use App\Http\Middleware\SecurityHeaders;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        health: '/up',
    )
    ->withSchedule(function (Schedule $schedule) {
        $schedule->command('stock:check-alerts')->hourly();

        $schedule->call(function () {
            \App\Services\NotificationService::nettoyerAnciennes(30);
        })->dailyAt('02:00');

        $schedule->command('subscriptions:check --notify')->dailyAt('03:00');
    })
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->append(SecurityHeaders::class);

        $middleware->replace(
            \Illuminate\Http\Middleware\TrustProxies::class,
            \App\Http\Middleware\TrustProxies::class
        );
        $middleware->replace(
            \Illuminate\Foundation\Http\Middleware\TrimStrings::class,
            \App\Http\Middleware\TrimStrings::class
        );
        $middleware->replace(
            \Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance::class,
            \App\Http\Middleware\PreventRequestsDuringMaintenance::class
        );
        $middleware->replace(
            \Illuminate\Cookie\Middleware\EncryptCookies::class,
            \App\Http\Middleware\EncryptCookies::class
        );
        $middleware->replace(
            \Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class,
            \App\Http\Middleware\VerifyCsrfToken::class
        );

        $middleware->alias([
            'auth' => \App\Http\Middleware\Authenticate::class,
            'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
            'auth.session' => \Illuminate\Session\Middleware\AuthenticateSession::class,
            'cache.headers' => \Illuminate\Http\Middleware\SetCacheHeaders::class,
            'can' => \Illuminate\Auth\Middleware\Authorize::class,
            'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
            'password.confirm' => \Illuminate\Auth\Middleware\RequirePassword::class,
            'signed' => \App\Http\Middleware\ValidateSignature::class,
            'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
            'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
            'super_admin' => \App\Http\Middleware\SuperAdminMiddleware::class,
            'boutique' => \App\Http\Middleware\BoutiqueMiddleware::class,
            'theme' => \App\Http\Middleware\ThemeMiddleware::class,
            'subscription' => \App\Http\Middleware\EnsureSubscriptionIsActive::class,
            'tenant' => \App\Http\Middleware\SetTenantContext::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (TokenMismatchException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Votre session a expiré. Veuillez rafraîchir la page et réessayer.',
                ], 419);
            }

            return redirect()->back()
                ->withInput($request->except(['_token', '_method', 'password', 'password_confirmation']))
                ->with('warning', 'Votre session a expiré. Veuillez réessayer.');
        });
    })
    ->create();
