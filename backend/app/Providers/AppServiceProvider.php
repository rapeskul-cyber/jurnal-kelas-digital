<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Http\Request;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Rate Limiter khusus untuk Login (Maks 5 request per menit per IP)
        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip())->response(function (Request $request, array $headers) {
                $retryAfter = $headers['Retry-After'] ?? 60;
                return response()->json([
                    'message' => "Terlalu banyak percobaan masuk (Too Many Requests). Silakan coba lagi dalam {$retryAfter} detik.",
                    'retry_after' => (int) $retryAfter,
                ], 429, $headers);
            });
        });

        // Global API limiter
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(120)->by($request->user()?->id ?: $request->ip());
        });
    }
}
