<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supporter extends Model
{
    use HasFactory;

    protected $appends = ['image_path'];

    public function getImagePathAttribute($value)
    {
        return $this->attributes['image'] ? url('/storage/' . $this->attributes['image']) : '';
    }
}
