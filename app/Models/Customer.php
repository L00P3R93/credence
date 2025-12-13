<?php

namespace App\Models;

use App\Enums\CustomerStatus;
use App\Enums\Gender;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Customer extends Model implements HasMedia
{
    use Auditable, SoftDeletes, InteractsWithMedia;

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

    public function loans(): HasMany
    {
        return $this->hasMany(Loan::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('profile-photo')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/jpg'])
            ->useDisk('public')
            ->singleFile();

        $this->addMediaCollection('documents')
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

    public function canGetNewLoan(): bool
    {
        return $this->status === CustomerStatus::ACTIVE && $this->loan_limit > 5000 && $this->loans()->whereNotIn('status', ['cleared', 'canceled', 'deleted'])->doesntExist();
    }


}
