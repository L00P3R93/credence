<?php

namespace App\Models;

use App\Enums\ProductFrequency;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, Auditable, SoftDeletes;

    protected $table = 'products';

    protected $casts = [
        'frequency' => ProductFrequency::class,
        'is_active' => 'boolean',
        'rolls_over' => 'boolean',
    ];
}
