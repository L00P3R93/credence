<?php

namespace App\Providers;

use App\Models\Lead;
use App\Models\Loan;
use App\Models\User;
use App\Observers\LeadObserver;
use App\Observers\LoanObserver;
use App\Observers\UserObserver;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

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
        Model::unguard();
        Model::automaticallyEagerLoadRelationships();
        if(app()->environment('production')) {
            URL::forceScheme('https');
        }
        User::observe(UserObserver::class);
        Lead::observe(LeadObserver::class);
        Loan::observe(LoanObserver::class);
    }
}
