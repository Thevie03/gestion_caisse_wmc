<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class RateLimitLogin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $key = 'login.' . $request->ip();

        // Limiter à 5 tentatives par 15 minutes
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);

            return back()->withErrors([
                'email' => "Trop de tentatives de connexion. Veuillez réessayer dans " . ceil($seconds / 60) . " minute(s)."
            ])->withInput($request->only('email'));
        }

        RateLimiter::hit($key, 900); // 15 minutes

        return $next($request);
    }
}


