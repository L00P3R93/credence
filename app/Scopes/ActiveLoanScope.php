<?php

namespace App\Scopes;

use App\Enums\LoanStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class ActiveLoanScope implements Scope
{

    /**
     * @inheritDoc
     */
    public function apply(Builder $builder, Model $model): void
    {
        $builder->whereIn('status', [
            LoanStatus::DUE_ROLL,
            LoanStatus::CLEARED,
            LoanStatus::DISBURSED,
            LoanStatus::OVERDUE,
            LoanStatus::PAST_OVERDUE,
        ]);
    }
}
