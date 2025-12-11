<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class County extends Model
{
    use Auditable;

    protected $table = 'counties';

    public function towns(): HasMany
    {
        return $this->hasMany(Town::class, 'county_id');
    }
}
