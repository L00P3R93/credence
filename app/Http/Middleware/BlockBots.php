<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class BlockBots
{
    // Case-insensitive patterns matched against the User-Agent header.
    private const BAD_UA_PATTERNS = [
        'python-requests',
        'aiohttp',
        'python/',
        'curl/',
        'wget/',
        'scrapy',
        'go-http-client',
        'httpx',
        'okhttp',
        'java/',
        'libwww-perl',
        'node-fetch',
        'axios/',
        'headless',
        'phantomjs',
        'selenium',
        'puppeteer',
        'playwright',
        'masscan',
        'nikto',
        'nmap',
        'sqlmap',
        'acunetix',
        'nessus',
        'zgrab',
    ];

    // Paths that must pass through even for blocked UAs (Livewire AJAX, health check).
    // Note: Filament panel is mounted at / (root) — livewire endpoint is /livewire/update.
    private const EXEMPT_PATHS = [
        'livewire/*',
        'up',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        if (! config('security.block_bots', true)) {
            return $next($request);
        }

        if ($request->is(self::EXEMPT_PATHS)) {
            return $next($request);
        }

        $ua = (string) ($request->userAgent() ?? '');

        if (trim($ua) === '') {
            return $this->block($request, 'empty user-agent');
        }

        if ($this->isBadUA($ua)) {
            return $this->block($request, "bad UA: {$ua}");
        }

        if ($this->isStaleUA($ua)) {
            return $this->block($request, "stale UA: {$ua}");
        }

        return $next($request);
    }

    private function isBadUA(string $ua): bool
    {
        $escaped  = array_map(static fn ($p) => preg_quote($p, '/'), self::BAD_UA_PATTERNS);
        $pattern  = '/(' . implode('|', $escaped) . ')/i';

        return (bool) preg_match($pattern, $ua);
    }

    private function isStaleUA(string $ua): bool
    {
        // Scanners routinely spoof old browser UAs. Real browsers are past these thresholds.
        if (preg_match('/Chrome\/(\d+)/i', $ua, $m) && (int) $m[1] < 100) {
            return true;
        }

        if (preg_match('/Firefox\/(\d+)/i', $ua, $m) && (int) $m[1] < 110) {
            return true;
        }

        return false;
    }

    private function block(Request $request, string $reason): Response
    {
        Log::channel('blocked')->warning('BOT_BLOCKED', [
            'ip'     => $request->ip(),
            'ua'     => $request->userAgent(),
            'path'   => $request->path(),
            'reason' => $reason,
        ]);

        // Plain text — do not leak framework name via error page.
        return response('Forbidden', Response::HTTP_FORBIDDEN, [
            'Content-Type' => 'text/plain; charset=UTF-8',
        ]);
    }
}
