<?php

namespace App\Observers;

use App\Enums\LeadStatus;
use App\Models\Lead;

class LeadObserver
{
    /**
     * Handle the Lead "created" event.
     */
    public function creating(Lead $lead): void
    {
        $lead->user_id = auth()->user()->id;
        $lead->status = LeadStatus::LEAD;
    }

    /**
     * Handle the Lead "updated" event.
     */
    public function updated(Lead $lead): void
    {
        //
    }

    /**
     * Handle the Lead "deleted" event.
     */
    public function deleted(Lead $lead): void
    {
        //
    }

    /**
     * Handle the Lead "restored" event.
     */
    public function restored(Lead $lead): void
    {
        //
    }

    /**
     * Handle the Lead "force deleted" event.
     */
    public function forceDeleted(Lead $lead): void
    {
        //
    }
}
