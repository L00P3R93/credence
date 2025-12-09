<?php

namespace App\Models;

use App\Enums\Gender;
use App\Enums\LeadStatus;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Lead extends Model
{
    use HasFactory, Auditable, SoftDeletes;

    protected $table = 'leads';

    protected $casts = [
        'dob' => 'date',
        'gender' => Gender::class,
        'status' => LeadStatus::class,
        'converted_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
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

    public function address(): HasOne
    {
        return $this->hasOne(Address::class);
    }

    public function convertedToCustomer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'converted_to_customer_id');
    }

    public function convertToCustomer(array $customerData = []): Customer
    {
        return DB::transaction(function () use ($customerData) {
            $customer = Customer::query()->create(array_merge([
               'lead_id' => $this->id,
               'name' => $this->name,
               'id_no' => $this->id_no,
               'phone' => $this->phone,
               'phone_alt' => $this->phone_alt,
               'gender' => $this->gender,
               'dob' => $this->dob,
               'work_email' => $this->work_email,
               'personal_email' => $this->personal_email,
               'user_id' => $this->user_id,
               'product_id' => $this->product_id,
               'bank_id' => $this->bank_id,
               'bank_branch_id' => $this->bank_branch_id,
            ],$customerData));

            // Update lead's address to point to the new customer
            if($this->address) {
                $this->address->update([
                    'lead_id' => null,
                    'customer_id' => $customer->id,
                ]);
            }

            // Update lead status
            $this->update([
               'status' => 'converted',
               'converted_to_customer_id' => $customer->id,
               'converted_at' => now()
            ]);

            return $customer;
        });
    }
}
