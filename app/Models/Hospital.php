<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Hospital extends Model
{
    use HasFactory;

    protected $fillable = [
        'hospital_name',
        'hospital_logo',
        'user_name',
        'email',
        'contact_number',
        'country_id',
        'city', // Changed from city_id to city
    ];

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }
    public function attachedDoctors()
    {
        return $this->belongsToMany(User::class, 'hospital_user_attachments', 'hospital_id', 'user_id')
                    ->where('account_type', 'doctor')
                    ->withPivot('status', 'sender_id');
    }

    public function attachedPatients()
    {
        return $this->belongsToMany(User::class, 'hospital_user_attachments', 'hospital_id', 'user_id')
                    ->where('account_type', 'patient')
                    ->withPivot('status', 'sender_id');
    }
    public function user()
{
    return $this->belongsTo(User::class);
}
}