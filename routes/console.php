<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Prune expired database sessions daily. Laravel's built-in GC lottery (2/100) is
// probabilistic — this scheduled delete is reliable under high traffic.
Schedule::call(function () {
    $lifetime = (int) config('session.lifetime') * 60; // minutes → seconds
    DB::table(config('session.table', 'sessions'))
        ->where('last_activity', '<', now()->subSeconds($lifetime)->timestamp)
        ->delete();
})->daily()->name('session:prune')->withoutOverlapping();
