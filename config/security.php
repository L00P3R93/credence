<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Security Headers
    |--------------------------------------------------------------------------
    | Toggle the SecurityHeaders middleware. Set to false to disable all
    | custom security headers (e.g. for local debugging).
    */
    'headers_enabled' => env('SECURITY_HEADERS_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Bot Blocking
    |--------------------------------------------------------------------------
    | Toggle the BlockBots middleware globally. Disable for local dev or
    | when an upstream WAF (Cloudflare) is already handling bot traffic.
    */
    'block_bots' => env('SECURITY_BLOCK_BOTS', true),

    /*
    |--------------------------------------------------------------------------
    | IP Blocklist
    |--------------------------------------------------------------------------
    | Supports single IPs and CIDR ranges. Checked by the IpBlocklist
    | middleware on every request. Managed here; never commit secrets.
    */
    'blocked_ips' => array_filter(
        explode(',', env('SECURITY_BLOCKED_IPS', implode(',', [
            '66.206.12.210',
            '5.255.111.197',
            '185.220.100.240',
            '192.36.109.108',
            '192.36.109.125',
            '104.248.157.201',
            '80.94.95.202',
            '146.70.194.230',
            '35.173.48.119',
            '173.249.63.49',
            '176.65.139.229',
        ])))
    ),

    /*
    |--------------------------------------------------------------------------
    | Trusted Proxies
    |--------------------------------------------------------------------------
    | IPs or CIDR ranges of load balancers / CDN edge nodes that sit in
    | front of this app. '*' trusts all proxies — tighten to actual
    | Cloudflare / load-balancer ranges in production.
    |
    | Cloudflare IPv4: https://www.cloudflare.com/ips-v4
    | Cloudflare IPv6: https://www.cloudflare.com/ips-v6
    */
    'trusted_proxies' => env('TRUSTED_PROXIES', '*'),

];
