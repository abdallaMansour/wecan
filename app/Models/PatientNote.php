<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\Favoritable;
class PatientNote extends Model
{
    use HasFactory, Favoritable;

    protected $casts = [
        'attachments' => 'array'
    ];

    protected $appends = ['attachments_paths'];

    public function getAttachmentsPathsAttribute($value)
    {
        $imagePaths = $this->attachments;

        if (!is_array($imagePaths)) {
            return [];
        }
        $fullPaths = array_map(function ($path) {
            return url('/storage/' . $path);
        }, $imagePaths);

        return $fullPaths;
    }


    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
