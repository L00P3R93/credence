<?php

namespace App\Providers;

use App\Models\Lead;
use App\Models\Loan;
use App\Models\User;
use App\Observers\LeadObserver;
use App\Observers\LoanObserver;
use App\Observers\UserObserver;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Model::unguard();
        Model::automaticallyEagerLoadRelationships();

        if (app()->environment('production')) {
            URL::forceScheme('https');
        }

        $this->configureRateLimiters();

        User::observe(UserObserver::class);
        Lead::observe(LeadObserver::class);
        Loan::observe(LoanObserver::class);
    }

    private function configureRateLimiters(): void
    {
        // Applied to the web middleware group via bootstrap/app.php.
        RateLimiter::for('global', function (Request $request) {
            return Limit::perMinute(60)->by($request->ip());
        });

        // Available for login / password-reset routes if not already handled
        // by Filament's built-in WithRateLimiting (which it is, at 5 attempts).
        RateLimiter::for('auth', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip());
        });

        // Reserve for future API routes.
        RateLimiter::for('api', function (Request $request) {
            return $request->user()
                ? Limit::perMinute(30)->by($request->user()->id)
                : Limit::perMinute(10)->by($request->ip());
        });
    }
}
