<?php

namespace App\Models;

use App\Enums\LoanStatus;
use App\Traits\Auditable;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;
use Filament\Support\Concerns\HasMediaFilter;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Loan extends Model implements HasMedia
{
    use Auditable, InteractsWithMedia, SoftDeletes;

    protected $table = 'loans';

    protected $guarded = [
        'this_due',
        'next_due'
    ];

    protected $casts = [
        'given_date' => 'date',
        'due_date' => 'date',
        'loan_amount' => 'decimal:2',
        'loan_interest' => 'decimal:2',
        'processing_fee' => 'decimal:2',
        'loan_total' => 'decimal:2',
        'loan_period' => 'integer',
        'new_loan' => 'boolean',
        'old_loan' => 'boolean',
        'top_up' => 'boolean',
        'status' => LoanStatus::class,
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'agent');
    }

    public function tempAgent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'temp_agent');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function collectionAgent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'collection_agent');
    }

    public function collectionOfficer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'collection_officer');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function bank(): BelongsTo
    {
        return $this->belongsTo(Bank::class);
    }

    public function bankBranch(): BelongsTo
    {
        return $this->belongsTo(BankBranch::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('loan-documents')
            ->acceptsMimeTypes([
                'application/pdf',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'image/jpeg',
                'image/png',
                'text/plain',
            ])
            ->useDisk('public');
    }

    public static function getValidationRules(): array
    {
        return [
            'customer_id' => 'required|exists:customers,id',
            'given_date' => 'required|date',
            'due_date' => 'required|date|after:given_date',
            'loan_amount' => 'required|numeric|min:5000',
            'loan_interest' => 'required|numeric|min:0',
            'processing_fee' => 'required|numeric|min:0',
            'loan_total' => 'required|numeric|min:0',
            'loan_period' => 'required|integer|min:1|max:12',
            'agent' => 'required|exists:users,id',
            'temp_agent' => 'nullable|exists:users,id',
            'collection_agent' => 'nullable|exists:users,id',
            'collection_officer' => 'nullable|exists:users,id',
            'status' => 'required|in:' . implode(',', array_column(LoanStatus::cases(), 'value')),
            'created_by' => 'required|exists:users,id',
            'product_id' => 'required|exists:products,id',
            'bank_id' => 'required|exists:banks,id',
            'bank_branch_id' => 'required|exists:bank_branches,id',
        ];
    }

    public function calculate($next_due = false, $this_due = false, $dd = 0): void
    {
        $this->loan_interest = $this->monthly_interest();
        $this->processing_fee = $this->processing_fee();
        $this->loan_total = $this->loan_total();
        $this->due_date = $this->loan_due_date($next_due, $this_due, $dd);
    }

    public function installment(): float
    {
        $monthly_principal = $this->monthly_principal();
        $monthly_interest = $this->monthly_interest();
        return $monthly_principal + $monthly_interest;
    }

    private function monthly_interest(): float
    {
        return ceil($this->product->rate * $this->loan_amount);
    }

    private function monthly_principal(): float
    {
        return round($this->loan_amount / $this->loan_period);
    }

    private function processing_fee(): float
    {
        return $this->loan_amount * 0.05;
    }

    private function loan_total(): float
    {
        return $this->installment() * $this->loan_period;
    }

    private function loan_due_date($next_due = false, $this_due = false, $dd = 0)
    {
        // Get today's date or use provided day
        $today = $dd > 0 ? $dd : now()->day;

        //Get bank details
        $bank = $this->bank;
        $payday = $bank->payday;
        $weekendPushes = $bank->weekend_pushes;

        // Adjust payday for weekends
        $payDate = now()->setDay($payday);
        $payDayOfWeek = $payDate->dayOfWeek;
        if($payDayOfWeek == CarbonInterface::SATURDAY) {
            $realPayday = $payday + ($weekendPushes == 0 ? -1 : 1);
        } elseif ($payDayOfWeek == CarbonInterface::SUNDAY) {
            $realPayday = $payday + ($weekendPushes == 0 ? -2 : 1);
        } elseif ($payDayOfWeek === CarbonInterface::MONDAY && $bank->id == 4) {
            $realPayday = $payday - 3;
        } else {
            $realPayday = $payday;
        }

        // Calculate due date
        $dueDate = null;
        if ($payday > 0) {
            if ($today > 0 && $today <= $realPayday - 3) {
                $dueDate = $next_due ? now()->addMonthNoOverflow()->setDay($payday) : now()->setDay($realPayday);
            } else {
                $dueDate = $this_due ? now()->setDay($realPayday) : now()->addMonthNoOverflow()->setDay($payday);
            }
        }

        return $dueDate;
    }
}
