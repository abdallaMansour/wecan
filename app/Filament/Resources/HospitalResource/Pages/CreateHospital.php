<?php

namespace App\Filament\Resources\HospitalResource\Pages;

use App\Filament\Resources\HospitalResource;
use Filament\Resources\Pages\CreateRecord;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CreateHospital extends CreateRecord
{
    protected static string $resource = HospitalResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        DB::beginTransaction();

        try {
            // Separate hospital and user data
            $hospitalData = array_diff_key($data, array_flip(['password', 'password_confirmation']));
            
            // Create the hospital first
            $hospital = static::getModel()::create($hospitalData);

            // Create the associated user
            $user = User::create([
                'name' => $data['user_name'],
                'email' => $data['email'],
                'password' => $data['password'], // This should already be hashed from the form
                'account_type' => 'hospital',
                'hospital_id' => $hospital->id, // Associate the user with the hospital
            ]);

            DB::commit();
            Log::info('Hospital and associated user created successfully', ['hospital_id' => $hospital->id, 'user_id' => $user->id]);

            return $hospital;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create hospital and user', ['error' => $e->getMessage()]);
            throw $e;
        }
    }
}