<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'app_name',
        'logo',
        'favicon',
        'address',
        'phone',
        'description',
        'tv_rental_price',
    ];

    protected function casts(): array
    {
        return [
            'tv_rental_price' => 'float',
        ];
    }

    public function getLogoUrlAttribute()
    {
        if (!$this->logo) {
            return null;
        }
        if (str_starts_with($this->logo, 'images/logos') || str_starts_with($this->logo, 'http')) {
            return asset($this->logo);
        }
        return asset('storage/' . $this->logo);
    }

    public function getFaviconUrlAttribute()
    {
        if (!$this->favicon) {
            return null;
        }
        if (str_starts_with($this->favicon, 'images/logos') || str_starts_with($this->favicon, 'http')) {
            return asset($this->favicon);
        }
        return asset('storage/' . $this->favicon);
    }
}
