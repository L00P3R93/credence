<?php

namespace App\Observers;

use App\Enums\LoanStatus;
use App\Enums\PaymentStatus;
use App\Models\Loan;
use App\Models\Payment;

class PaymentObserver
{
    public function creating(Payment $payment): void
    {
        $payment->status = PaymentStatus::COMPLETED;
        $payment->date_received = now();

        if (auth()->check()) {
            $payment->added_by = auth()->id();
        }

        $loan = Loan::query()->find($payment->loan_id);
        if (!$loan) {
            abort(404, 'The specified loan does not exist.');
        }
        $payment->customer_id = $loan->customer->id;
    }
    /**
     * Handle the Payment "created" event.
     */
    public function created(Payment $payment): void
    {
        $this->loanIsCleared($payment);
    }

    /**
     * Handle the Payment "updated" event.
     */
    public function updated(Payment $payment): void
    {
        $this->loanIsCleared($payment);
    }

    /**
     * Handle the Payment "deleted" event.
     */
    public function deleted(Payment $payment): void
    {
        //
    }

    /**
     * Handle the Payment "restored" event.
     */
    public function restored(Payment $payment): void
    {
        //
    }

    /**
     * Handle the Payment "force deleted" event.
     */
    public function forceDeleted(Payment $payment): void
    {
        //
    }

    private function loanIsCleared($payment): void
    {
        $loan = Loan::query()->find($payment->loan_id);
        if (!$loan) {
            abort(404, 'The specified loan does not exist.');
        }
        $loanBalance = $loan->loanBalance();
        if ($loanBalance <= 0) {
            $loan->status = LoanStatus::CLEARED;
            $loan->save();
            $payment->customer_id = $loan->customer->id;
            $payment->status = PaymentStatus::CLEARED;
            $payment->save();
        }
    }
}
