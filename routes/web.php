<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Honeypot Routes (Layer 6)
|--------------------------------------------------------------------------
| These paths are probed by every opportunistic scanner hitting the internet.
| Legitimate users never visit them. Any hit:
|   1. Logs the requester IP with [HONEYPOT] marker to storage/logs/blocked.log
|   2. Adds the IP to the temp_blocklist cache key for 24 h
|   3. Returns a fake 200 empty response (wastes scanner time, triggers next probe)
|
| The IpBlocklist middleware then blocks all subsequent requests from that IP.
| NOTE: Route closures are intentional — these routes must not be cached.
*/

$honeypots = [
    '.env',
    '.git/config',
    '.git/HEAD',
    'wp-login.php',
    'wp-admin',
    'wp-admin/admin-ajax.php',
    'phpmyadmin',
    'phpmyadmin/index.php',
    'administrator',
    'administrator/index.php',
    'admin/config.php',
];

foreach ($honeypots as $path) {
    Route::get("/{$path}", function (Request $request) {
        $ip = (string) $request->ip();

        Log::channel('blocked')->warning('[HONEYPOT]', [
            'ip'   => $ip,
            'ua'   => $request->userAgent(),
            'path' => $request->path(),
        ]);

        // Block all further requests from this IP for 24 hours.
        Cache::put("temp_blocklist:{$ip}", true, now()->addHours(24));

        return response('', 200, ['Content-Type' => 'text/html; charset=UTF-8']);
    })->name("honeypot.{$path}");
}
