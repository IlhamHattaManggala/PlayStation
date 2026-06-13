<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PlaystationUnit extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'status',
        'description',
    ];

    public function onsitePlayTransactions(): HasMany
    {
        return $this->hasMany(OnsitePlayTransaction::class);
    }

    public function rentalTransactions(): HasMany
    {
        return $this->hasMany(RentalTransaction::class);
    }
}
