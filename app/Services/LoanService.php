<?php

namespace App\Services;

use App\Enums\CustomerStatus;
use App\Enums\LoanStatus;
use App\Enums\RefinanceStatus;
use App\Models\Customer;
use App\Models\Loan;
use App\Models\Refinance;
use App\Models\User;
use Carbon\Carbon;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LoanService
{
    /**
     * Create a new loan
     * @throws \Throwable
     */
    public function createLoan(array $data): Loan
    {
        return DB::transaction(function () use ($data) {
            $loan = Loan::create($data);

            // Calculate loan details
            $loan->calculate();
            $loan->save();

            // Update customer status
            $this->updateCustomerLoanStatus($loan->customer, true);

            Log::info('Loan created', [
                'loan_id' => $loan->id,
                'customer_id' => $loan->customer_id,
                'amount' => $loan->loan_amount,
                'created_by' => $data['created_by'] ?? null
            ]);

            return $loan;
        });
    }

    /**
     * Process a loan refinance
     * @throws \Throwable
     */
    public function processRefinance(Loan $existingLoan, float $refinanceAmount, User $user): array
    {
        return DB::transaction(function () use ($existingLoan, $refinanceAmount, $user) {
            $customer = $existingLoan->customer;

            // Validate eligibility
            if (!$this->validateRefinanceEligibility($existingLoan, $refinanceAmount)) {
                throw new \InvalidArgumentException('Loan does not qualify for refinance');
            }

            // Mark existing loan as cleared
            $this->clearExistingLoan($existingLoan, $user);

            // Create new loan with refinance amount
            $newLoan = $this->createRefinancedLoan($existingLoan, $refinanceAmount, $user);

            // Create refinance record
            $refinance = $this->createRefinanceRecord($newLoan, $refinanceAmount);

            // Update customer status
            $this->updateCustomerLoanStatus($customer, true);

            Log::info('Loan refinance processed', [
                'original_loan_id' => $existingLoan->id,
                'new_loan_id' => $newLoan->id,
                'refinance_id' => $refinance->id,
                'customer_id' => $customer->id,
                'previous_principal' => $existingLoan->principalBalance(),
                'refinance_amount' => $refinanceAmount,
                'new_loan_amount' => $newLoan->loan_amount,
                'top_up' => $this->getRefinanceType($existingLoan),
                'new_due_date' => $newLoan->due_date,
                'old_due_date' => $existingLoan->due_date,
                'processed_by' => $user->id
            ]);

            return [
                'success' => true,
                'new_loan' => $newLoan,
                'refinance' => $refinance,
                'message' => 'Loan refinance processed successfully'
            ];
        });
    }

    /**
     * Validate loan eligibility for refinance
     */
    public function validateRefinanceEligibility(Loan $loan, float $requestedAmount): bool
    {
        $customer = $loan->customer;

        // Check if customer is active
        if ($customer->status !== CustomerStatus::ACTIVE) {
            return false;
        }

        // Check if loan is in a status that allows re-finance
        $allowedStatuses = [
            LoanStatus::DISBURSED,
            LoanStatus::OVERDUE,
            LoanStatus::PAST_OVERDUE
        ];

        if (!in_array($loan->status, $allowedStatuses)) {
            return false;
        }

        $refinanceAmount = $loan->refinanceAmount();

        // Check if requested amount is within limits
        return $requestedAmount >= 1000 && $requestedAmount <= $refinanceAmount;
    }

    /**
     * Get maximum refinance amount for a loan
     */
    public function getMaxRefinanceAmount(Loan $loan): float
    {
        return $loan->refinanceAmount();
    }

    /**
     * Clear existing loan during refinance
     */
    private function clearExistingLoan(Loan $existingLoan, User $user): void
    {
        $userName = $user->name ?? 'System';
        $customer = $existingLoan->customer;
        $topUp = $this->getRefinanceType($existingLoan);

        $existingLoan->update([
            'status' => LoanStatus::CLEARED,
            'remarks' => "Loan cleared due to refinance by {$userName} on " . now()->format('Y-m-d H:i:s'),
            'top_up' => $topUp,
        ]);

        // Mark Customer has no loan
        $this->updateCustomerLoanStatus($customer, false);
    }

    private function getRefinanceType(Loan $existingLoan): int
    {
        $totalPaid = $existingLoan->payments()->sum('amount');
        return ($totalPaid >= $existingLoan->loan_interest || $existingLoan->status == LoanStatus::DUE_ROLL) ? 2 : 1;
    }

    /**
     * Create refinance record
     */
    private function createRefinanceRecord(Loan $newLoan, float $refinanceAmount): Refinance
    {
        return Refinance::create([
            'loan_id' => $newLoan->id,
            'amount' => $refinanceAmount,
            'status' => RefinanceStatus::APPROVED,
            'due_date' => $newLoan->due_date,
        ]);
    }

    /**
     * Create new loan from refinance
     */
    private function createRefinancedLoan(Loan $originalLoan, float $refinanceAmount, User $user): Loan
    {
        $newLoanAmount = $refinanceAmount + $originalLoan->principalBalance();
        $topUp = $this->getRefinanceType($originalLoan);
        $nextDue = $thisDue = false;
        if ($topUp == 1) $thisDue = true;
        elseif ($topUp == 2) $nextDue = true;

        $loanData = [
            'customer_id' => $originalLoan->customer_id,
            'given_date' => now(),
            'loan_amount' => $newLoanAmount,
            'loan_period' => 1,
            'agent' => $originalLoan->agent,
            'temp_agent' => $originalLoan->agent,
            'status' => LoanStatus::PENDING_VERIFICATION,
            'created_by' => $user->id,
            'product_id' => $originalLoan->product_id,
            'bank_id' => $originalLoan->bank_id,
            'bank_branch_id' => $originalLoan->bank_branch_id,
            'new_loan' => false,
            'old_loan' => true,
            'top_up' => 0,
            'is_from_top_up' => true,
            'remarks' => "Loan refinance of KES " . number_format($refinanceAmount) . " created by {$user->name} on " . now()->format('Y-m-d H:i:s') . "<br>Previous Principal: KES " . number_format($originalLoan->principalBalance()) . ", New Loan: KES " . number_format($newLoanAmount),
        ];

        $newLoan = new Loan();
        $newLoan->loan_amount = $newLoanAmount;
        $newLoan->loan_period = 1;
        $newLoan->customer_id = $originalLoan->customer_id;
        $newLoan->product_id = $originalLoan->product_id;
        $newLoan->bank_id = $originalLoan->bank_id;
        $newLoan->bank_branch_id = $originalLoan->bank_branch_id;
        $newLoan->agent = $originalLoan->agent;
        $newLoan->temp_agent = $originalLoan->agent;
        // Then fill and save
        $newLoan->fill($loanData);

        // Calculate loan details
        $newLoan->calculate($nextDue, $thisDue);
        $newLoan->saveQuietly(); // Without trigger observers

        return $newLoan;
    }

    /**
     * Update customer loan status
     */
    private function updateCustomerLoanStatus(Customer $customer, bool $hasLoan): void
    {
        $customer->update(['has_loan' => $hasLoan]);
    }

    /**
     * Get loan statistics for dashboard
     */
    public function getLoanStats(Carbon $startDate, Carbon $endDate): array
    {
        $total_amount = Loan::whereBetween('due_date', [$startDate, $endDate])->whereIn('top_up', [0,2])->sum('loan_amount');
        $new_loans = Loan::where('new_loan', true)->whereBetween('due_date', [$startDate, $endDate])->whereIn('top_up', [0,2])->where('rolled', false)->sum('loan_amount');
        $old_loans = Loan::where('old_loan', true)->whereBetween('due_date', [$startDate, $endDate])->whereIn('top_up', [0,2])->where('rolled', false)->where('is_from_top_up', false)->sum('loan_amount');
        $top_ups = Refinance::whereBetween('due_date', [$startDate, $endDate])->where('status', [RefinanceStatus::APPROVED])->sum('amount');
        $topped_amounts = $total_amount - ($new_loans + $old_loans + $top_ups);
        return [
            'total_loans' => Loan::whereBetween('due_date', [$startDate, $endDate])->whereIn('top_up', [0,2])->count(),
            'total_amount' => $total_amount,
            'new_loans' => $new_loans,
            'old_loans' => $old_loans,
            'top_up_amounts' => $topped_amounts,
            'rolled_amounts' => Loan::whereBetween('due_date', [$startDate, $endDate])->whereIn('top_up', [0,2])->where('rolled', true)->sum('loan_amount'),
        ];
    }

    /**
     * Calculate loan eligibility for new loan
     */
    public function checkNewLoanEligibility(Customer $customer, float $requestedAmount): array
    {
        $response = [
            'eligible' => false,
            'reason' => '',
            'max_amount' => 0
        ];

        // Check customer status
        if ($customer->status !== 'active') {
            $response['reason'] = 'Customer is not active';
            return $response;
        }

        // Check if customer has existing loan
        if ($customer->has_loan) {
            $response['reason'] = 'Customer already has an active loan';
            return $response;
        }

        // Check loan limit
        if ($requestedAmount > $customer->loan_limit) {
            $response['reason'] = 'Requested amount exceeds customer limit';
            $response['max_amount'] = $customer->loan_limit;
            return $response;
        }

        // Check minimum amount
        if ($requestedAmount < 1000) {
            $response['reason'] = 'Minimum loan amount is KES 1,000';
            return $response;
        }

        $response['eligible'] = true;
        $response['max_amount'] = $customer->loan_limit;
        return $response;
    }
}
