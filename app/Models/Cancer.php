<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Cancer extends Model
{
    use HasFactory;

    protected $fillable = [
        'cancer_image',
        'name_ar',
        'name_en',
        'visible',
    ];

    protected static function booted()
    {
        static::deleting(function ($cancer) {
            if ($cancer->cancer_image) {
                Storage::disk('public')->delete($cancer->cancer_image);
            }
        });
    }
}
