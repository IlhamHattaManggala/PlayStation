<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OnsitePlayOrder extends Model
{
    protected $fillable = [
        'onsite_play_transaction_id',
        'product_id',
        'quantity',
        'price',
        'total_price',
    ];

    protected $casts = [
        'price' => 'float',
        'total_price' => 'float',
        'quantity' => 'integer',
    ];

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(OnsitePlayTransaction::class, 'onsite_play_transaction_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
