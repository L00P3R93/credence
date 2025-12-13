<?php

namespace App\Observers;

use App\Models\Loan;

class LoanObserver
{
    public function creating(Loan $loan): void
    {
        // Mark as new loan
        $customer = $loan->customer;

        $loan->given_date = now();

        // Set default values
        $loan->fill([
            'bank_id' => $customer->bank_id,
            'bank_branch_id' => $customer->bank_branch_id,
            'product_id' => $customer->product_id,
            'agent' => $customer->user->id,
            'status' => 'pending_verification',
            'created_by' => auth()->id(),
        ]);

        if ($customer->loans()->where('status', '!=', ['canceled', 'deleted'])->count()  == 0){
            $loan->new_loan = true;
        } else {
            $loan->old_loan = true;
        }

        // Get due date preference from request
        $nextDue = request()->input('next_due', false);
        $thisDue = request()->input('this_due', false);

        // Calculate Loan Details
        $loan->calculate($nextDue, $thisDue);
    }

    /**
     * Handle the Loan "created" event.
     */
    public function created(Loan $loan): void
    {
        $loan->customer->update(['has_loan' => true]);
    }

    /**
     * Handle the Loan "updated" event.
     */
    public function updated(Loan $loan): void
    {
        //
    }

    /**
     * Handle the Loan "deleted" event.
     */
    public function deleted(Loan $loan): void
    {
        //
    }

    /**
     * Handle the Loan "restored" event.
     */
    public function restored(Loan $loan): void
    {
        //
    }

    /**
     * Handle the Loan "force deleted" event.
     */
    public function forceDeleted(Loan $loan): void
    {
        //
    }
}
