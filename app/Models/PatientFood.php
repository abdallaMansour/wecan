<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PatientFood extends Model
{
    use HasFactory;

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
