<?php

use App\Http\Middleware\BlockBots;
use App\Http\Middleware\IpBlocklist;
use App\Http\Middleware\SecurityHeaders;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Trust proxies defined in TRUSTED_PROXIES env var (comma-separated IPs/CIDRs).
        // Tighten from '*' to actual Cloudflare / load-balancer CIDR ranges in production.
        $middleware->trustProxies(at: env('TRUSTED_PROXIES', '*'));

        // Prepend SecurityHeaders so it wraps the full stack and adds headers to
        // every response — including early 403s from IpBlocklist and BlockBots.
        $middleware->prepend(SecurityHeaders::class);

        // IP blocklist — static config + dynamic honeypot cache. Runs before UA checks.
        $middleware->append(IpBlocklist::class);

        // UA-based bot / scanner blocking. Runs after IP check.
        $middleware->append(BlockBots::class);

        // Global web rate limit: 60 req/min per IP (limiter defined in AppServiceProvider).
        $middleware->web(append: ['throttle:global']);

        $middleware->alias([
            'role'               => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission'         => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
