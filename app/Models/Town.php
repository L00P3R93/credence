<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Town extends Model
{
    use HasFactory, Auditable;

    protected $table = 'towns';

    public function county(): BelongsTo
    {
        return $this->belongsTo(County::class, 'county_id');
    }
}
