<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Town extends Model
{
    use Auditable;

    protected $table = 'towns';

    public function county(): BelongsTo
    {
        return $this->belongsTo(County::class, 'county_id');
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class);
    }
}
