<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Loan;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class DashboardStatsService
{
    public function getCurrentMonthStats(): array
    {
        $now = Carbon::now();
        $startOfMonth = $now->copy()->startOfMonth();
        $endOfMonth = $now->copy()->endOfMonth();

        return [
            'activeCustomers' => $this->getActiveCustomers(),
            'monthlyLoanBookTotal' => $this->getMonthlyLoanBookTotal(),
            'monthlyLoanInterest' => $this->getMonthlyLoanInterest(),
            'todaySales' => $this->getTodaySales(),
            'newLoansThisMonth' => $this->getNewLoansThisMonth($startOfMonth, $endOfMonth),
            'totalTopUpThisMonth' => $this->getTotalTopUpThisMonth($startOfMonth, $endOfMonth),
            'toppedAmount' => $this->getToppedAmount($startOfMonth, $endOfMonth),
            'totalRolledThisMonth' => $this->getTotalRolledThisMonth($startOfMonth, $endOfMonth),
        ];
    }

    public function getNextMonthStats(): array
    {
        $nextMonth = Carbon::now()->addMonth();
        $startOfNextMonth = $nextMonth->copy()->startOfMonth();
        $endOfNextMonth = $nextMonth->copy()->endOfMonth();

        return [
            'newLoansNextMonth' => $this->getNewLoansThisMonth($startOfNextMonth, $endOfNextMonth),
            'totalTopUpForNextMonth' => $this->getTotalTopUpThisMonth($startOfNextMonth, $endOfNextMonth),
            'toppedAmountForNextMonth' => $this->getToppedAmount($startOfNextMonth, $endOfNextMonth),
            'dueRollForNextMonth' => $this->getDueRollForNextMonth($startOfNextMonth, $endOfNextMonth),
        ];
    }

    public function getCollectionsStats(): array
    {
        $now = Carbon::now();
        $startOfMonth = $now->copy()->startOfMonth();
        $endOfMonth = $now->copy()->endOfMonth();

        $nextMonth = $now->copy()->addMonth();
        $startOfNextMonth = $nextMonth->copy()->startOfMonth();
        $endOfNextMonth = $nextMonth->copy()->endOfMonth();

        return [
            'collectedThisMonth' => $this->getCollectedAmount($startOfMonth, $endOfMonth),
            'collectedNextMonth' => $this->getCollectedAmount($startOfNextMonth, $endOfNextMonth),
        ];
    }

    private function getActiveCustomers(): int
    {
        return Customer::where('status', 'active')->count();
    }

    private function getMonthlyLoanBookTotal(): float
    {
        return Loan::withGlobalScope('active_loan', new \App\Scopes\ActiveLoanScope())
            ->where('given_date', '>=', Carbon::now()->startOfMonth())
            ->where('given_date', '<=', Carbon::now()->endOfMonth())
            ->sum('loan_amount');
    }

    private function getMonthlyLoanInterest(): float
    {
        return Loan::withGlobalScope('active_loan', new \App\Scopes\ActiveLoanScope())
            ->where('given_date', '>=', Carbon::now()->startOfMonth())
            ->where('given_date', '<=', Carbon::now()->endOfMonth())
            ->sum('loan_interest');
    }

    private function getTodaySales(): float
    {
        return Loan::withGlobalScope('active_loan', new \App\Scopes\ActiveLoanScope())
            ->whereDate('given_date', Carbon::today())
            ->sum('loan_amount');
    }

    private function getNewLoansThisMonth(Carbon $startDate, Carbon $endDate): float
    {
        return Loan::withGlobalScope('active_loan', new \App\Scopes\ActiveLoanScope())
            ->where('new_loan', true)
            ->where('given_date', '>=', $startDate)
            ->where('given_date', '<=', $endDate)
            ->sum('loan_amount');
    }

    private function getTotalTopUpThisMonth(Carbon $startDate, Carbon $endDate): float
    {
        return Loan::withGlobalScope('active_loan', new \App\Scopes\ActiveLoanScope())
            ->where('top_up', true)
            ->where('given_date', '>=', $startDate)
            ->where('given_date', '<=', $endDate)
            ->count();
    }

    private function getToppedAmount(Carbon $startDate, Carbon $endDate): float
    {
        return Loan::withGlobalScope('active_loan', new \App\Scopes\ActiveLoanScope())
            ->where('top_up', true)
            ->where('given_date', '>=', $startDate)
            ->where('given_date', '<=', $endDate)
            ->sum('loan_amount');
    }

    private function getTotalRolledThisMonth(Carbon $startDate, Carbon $endDate): float
    {
        return Loan::withGlobalScope('active_loan', new \App\Scopes\ActiveLoanScope())
            ->where('old_loan', true)
            ->where('given_date', '>=', $startDate)
            ->where('given_date', '<=', $endDate)
            ->sum('loan_amount');
    }

    private function getDueRollForNextMonth(Carbon $startDate, Carbon $endDate): float
    {
        return Loan::withGlobalScope('active_loan', new \App\Scopes\ActiveLoanScope())
            ->where('due_date', '>=', $startDate)
            ->where('due_date', '<=', $endDate)
            ->sum('loan_amount');
    }

    private function getCollectedAmount(Carbon $startDate, Carbon $endDate): float
    {
        return Payment::where('date_received', '>=', $startDate)
            ->where('date_received', '<=', $endDate)
            ->sum('amount');
    }
}
