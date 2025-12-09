<?php

namespace App\Models;

use App\Enums\CustomerStatus;
use App\Enums\Gender;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use HasFactory, Auditable, SoftDeletes;

    protected $table = 'customers';

    protected $casts = [
        'dob' => 'date',
        'gender' => Gender::class,
        'status' => CustomerStatus::class,
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

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function address(): HasOne
    {
        return $this->hasOne(Address::class);
    }
}
