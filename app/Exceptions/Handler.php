<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Session\TokenMismatchException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
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
            //
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
        // Gérer l'erreur 419 (CSRF token mismatch) en redirigeant avec un message
        if ($exception instanceof TokenMismatchException) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Votre session a expiré. Veuillez rafraîchir la page et réessayer.'
                ], 419);
            }

            return redirect()->back()
                ->withInput($request->except(['_token', '_method', 'password', 'password_confirmation']))
                ->with('warning', 'Votre session a expiré. Veuillez réessayer.');
        }

        return parent::render($request, $exception);
    }
}
