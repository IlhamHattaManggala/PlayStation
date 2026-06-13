<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OnsitePlayTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'playstation_unit_id',
        'started_at',
        'ended_at',
        'duration_minutes',
        'hourly_rate',
        'total_price',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
        ];
    }

    public function playstationUnit(): BelongsTo
    {
        return $this->belongsTo(PlaystationUnit::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(OnsitePlayOrder::class, 'onsite_play_transaction_id');
    }
}
