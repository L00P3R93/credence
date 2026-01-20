<?php

namespace App\Models;

use App\Enums\RefinanceStatus;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Refinance extends Model
{
    /** @use HasFactory<\Database\Factories\RefinanceFactory> */
    use HasFactory, Auditable;

    protected $table = 'refinances';

    protected function casts(): array
    {
        return [
            'status' => RefinanceStatus::class,
            'amount' => 'decimal:2',
        ];
    }

    public function loan(): BelongsTo
    {
        return $this->belongsTo(Loan::class);
    }
}
