<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = [
        'name',
        'category',
        'price',
        'stock',
    ];

    protected $casts = [
        'price' => 'float',
        'stock' => 'integer',
    ];

    public function onsitePlayOrders(): HasMany
    {
        return $this->hasMany(OnsitePlayOrder::class);
    }
}
