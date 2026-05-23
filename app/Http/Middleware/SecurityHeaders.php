<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (! config('security.headers_enabled', true)) {
            return $response;
        }

        // Only set HSTS over HTTPS to avoid breaking plain-HTTP dev environments.
        if ($request->secure()) {
            $response->headers->set(
                'Strict-Transport-Security',
                'max-age=31536000; includeSubDomains; preload'
            );
        }

        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // SAMEORIGIN (not DENY) — Filament uses same-origin iframes for some widgets.
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');

        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        $response->headers->set(
            'Permissions-Policy',
            'camera=(), microphone=(), geolocation=()'
        );

        // CSP in report-only mode. Filament requires unsafe-inline/unsafe-eval for
        // Alpine.js and Livewire. Tune this before switching to enforce mode.
        // ws:/wss: covers Livewire's Pusher/Echo connections if ever enabled.
        $response->headers->set(
            'Content-Security-Policy-Report-Only',
            implode('; ', [
                "default-src 'self'",
                "script-src 'self' 'unsafe-inline' 'unsafe-eval'",
                "style-src 'self' 'unsafe-inline'",
                "img-src 'self' data: https:",
                "font-src 'self' data:",
                "connect-src 'self' ws: wss:",
                "frame-ancestors 'self'",
            ])
        );

        return $response;
    }
}
