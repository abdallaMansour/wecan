<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\Favoritable;

class CancerScreeningCenter extends Model
{
    use HasFactory, Favoritable;

    protected $appends = ['hospital_logo_path'];

    public function getHospitalLogoPathAttribute($value)
    {
        return $this->attributes['hospital_logo'] ? url('/storage/' . $this->attributes['hospital_logo']) : '';
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }


    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }
}
