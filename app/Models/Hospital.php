<?php

namespace App\Models;

use App\Mail\HospitalStatusChanged;
use Illuminate\Support\Facades\Mail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

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
        'city',
        'account_status',
        'password',
    ];

    protected $casts = [
        'password' => 'hashed',
    ];

    public function activate() {
        Mail::to($this->email)->send(new HospitalStatusChanged($this));
        $this->update(['account_status' => 'active']);
    }

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