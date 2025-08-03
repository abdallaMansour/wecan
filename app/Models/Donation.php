<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Donation extends Model
{
    use HasFactory;

    protected $appends = ['telecom_logo_path'];

    public function getTelecomLogoPathAttribute($value)
    {
        return $this->attributes['telecom_logo'] ? url('/storage/' . $this->attributes['telecom_logo']) : '';
    }

    public function charity(): BelongsTo
    {
        return $this->belongsTo(Charity::class);
    }
}
