<?php

namespace App\Observers;

use App\Models\Refinance;

class RefinanceObserver
{
    public function creating(Refinance $refinance): void
    {

    }

    /**
     * Handle the Refinance "created" event.
     */
    public function created(Refinance $refinance): void
    {
        //
    }

    /**
     * Handle the Refinance "updated" event.
     */
    public function updated(Refinance $refinance): void
    {
        //
    }

    /**
     * Handle the Refinance "deleted" event.
     */
    public function deleted(Refinance $refinance): void
    {
        //
    }

    /**
     * Handle the Refinance "restored" event.
     */
    public function restored(Refinance $refinance): void
    {
        //
    }

    /**
     * Handle the Refinance "force deleted" event.
     */
    public function forceDeleted(Refinance $refinance): void
    {
        //
    }
}
