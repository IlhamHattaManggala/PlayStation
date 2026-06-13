<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RentalTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'playstation_unit_id',
        'renter_name',
        'phone',
        'identity_card_path',
        'rental_start_date',
        'rental_end_date',
        'rental_days',
        'daily_rate',
        'include_tv',
        'tv_price',
        'total_price',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'rental_start_date' => 'date',
            'rental_end_date' => 'date',
            'rental_days' => 'float',
            'include_tv' => 'boolean',
            'tv_price' => 'float',
        ];
    }

    public function playstationUnit(): BelongsTo
    {
        return $this->belongsTo(PlaystationUnit::class);
    }

    public function getIdentityCardUrlAttribute()
    {
        if (!$this->identity_card_path) {
            return null;
        }
        if (str_starts_with($this->identity_card_path, 'images/jaminan') || str_starts_with($this->identity_card_path, 'http')) {
            return asset($this->identity_card_path);
        }
        return asset('storage/' . $this->identity_card_path);
    }
}
