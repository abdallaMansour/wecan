<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Charity extends Model
{
    use HasFactory;

    protected $appends = ['charity_logo_en_path', 'charity_logo_ar_path'];

    public function getCharityLogoEnPathAttribute($value)
    {
        return $this->attributes['charity_logo_en'] ? url('/storage/' . $this->attributes['charity_logo_en']) : '';
    }

    public function getCharityLogoArPathAttribute($value)
    {
        return $this->attributes['charity_logo_ar'] ? url('/storage/' . $this->attributes['charity_logo_ar']) : '';
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }


    public function donations(): HasMany
    {
        return $this->HasMany(Donation::class);
    }
}
