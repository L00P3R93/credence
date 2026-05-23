<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\IpUtils;
use Symfony\Component\HttpFoundation\Response;

class IpBlocklist
{
    // Paths exempt from IP blocking (monitoring, health checks).
    private const EXEMPT_PATHS = [
        'up',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        if ($request->is(self::EXEMPT_PATHS)) {
            return $next($request);
        }

        $ip = (string) $request->ip();

        // Dynamic list: honeypot hits are cached here for 24 h.
        if (Cache::has("temp_blocklist:{$ip}")) {
            return $this->block($request, $ip, 'temp_blocklist (honeypot hit)');
        }

        // Static list from config/security.php — supports single IPs and CIDR ranges.
        $blockedIps = array_values(array_filter((array) config('security.blocked_ips', [])));

        if (! empty($blockedIps) && IpUtils::checkIp($ip, $blockedIps)) {
            return $this->block($request, $ip, 'config blocklist');
        }

        return $next($request);
    }

    private function block(Request $request, string $ip, string $reason): Response
    {
        Log::channel('blocked')->warning('IP_BLOCKED', [
            'ip'     => $ip,
            'ua'     => $request->userAgent(),
            'path'   => $request->path(),
            'reason' => $reason,
        ]);

        // Empty body — reveals nothing about the stack.
        return response('', Response::HTTP_FORBIDDEN);
    }
}
