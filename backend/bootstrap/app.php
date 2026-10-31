<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Point 4: Percaya Reverse Proxy Nginx untuk mendeteksi real client IP & Proto
        $middleware->trustProxies(at: '*');

        // Point 6: Registrasi Browser Security Headers Middleware secara global
        $middleware->append(\App\Http\Middleware\SecurityHeadersMiddleware::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        // Point 1: Rate Limiter Response (429 Too Many Requests + Header Retry-After)
        $exceptions->render(function (\Illuminate\Http\Exceptions\ThrottleRequestsException $e, Request $request) {
            $headers = $e->getHeaders();
            $retryAfter = $headers['Retry-After'] ?? 60;
            return response()->json([
                'message' => "Terlalu banyak percobaan masuk (Too Many Requests). Silakan coba lagi dalam {$retryAfter} detik.",
                'retry_after' => (int) $retryAfter,
            ], 429, $headers);
        });

        // Point 5: Obfuskasi Framework Fingerprint (404 Not Found generik)
        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'error' => 'Resource not found',
                ], 404);
            }
        });

        // Point 7: Standarisasi JSON Response 405 Method Not Allowed (Mencegah Leaking Nginx/Framework)
        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'message' => 'Method Not Allowed.',
                ], 405);
            }
        });
    })->create();
