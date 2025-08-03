<?php

namespace App\Traits;

use App\Models\Favorite;

trait Favoritable
{
    public function favorites()
    {
        return $this->morphMany(Favorite::class, 'favorable');
    }

    public function favorite($userId)
    {
        if (!$this->favorites()->where('user_id', $userId)->exists()) {
            $this->favorites()->create([
                'user_id' => $userId,
                'favorable_id' => $this->id,
                'favorable_type' => get_class($this)
            ]);
        }
    }

    public function unfavorite($userId)
    {
        $this->favorites()->where('user_id', $userId)->delete();
    }

    public function isFavorited($userId)
    {
        return $this->favorites()->where('user_id', $userId)->exists();
    }
}