<?php

namespace App\Filament\Resources\HospitalResource\Pages;

use App\Filament\Resources\HospitalResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;


use Illuminate\Support\Facades\DB;
class EditHospital extends EditRecord
{
    protected static string $resource = HospitalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    // protected function mutateFormDataBeforeSave(array $data): array
    // {
    //     Log::info('Mutating form data before save', ['data' => $data]);

    //     // Remove password fields from the data array as they shouldn't be saved to the Hospital model
    //     unset($data['new_password']);
    //     unset($data['new_password_confirmation']);

    //     return $data;
    // }

    // protected function afterSave(): void
    // {
    //     $data = $this->form->getState();
    //     Log::info('After save method called', ['data' => $data]);

    //     // Handle password change
    //     if (!empty($data['password'])) {
    //         $user = User::where('email', $this->record->email)->first();
    //         if ($user) {
    //             try {
    //                 $user->update([
    //                     'password' => $data['password'],
    //                 ]);
    //                 Log::info('User password updated', ['user_id' => $user->id]);
    //                 Notification::make()
    //                     ->title('Password updated successfully')
    //                     ->success()
    //                     ->send();
    //             } catch (\Exception $e) {
    //                 Log::error('Failed to update user password', ['error' => $e->getMessage()]);
    //                 Notification::make()
    //                     ->title('Failed to update password')
    //                     ->body('An error occurred while updating the password.')
    //                     ->danger()
    //                     ->send();
    //             }
    //         } else {
    //             Log::warning('User not found for password update', ['email' => $this->record->email]);
    //             Notification::make()
    //                 ->title('User not found')
    //                 ->body('Unable to update password: user account not found.')
    //                 ->warning()
    //                 ->send();
    //         }
    //     }
    // }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        DB::beginTransaction();
        try {
            // Separate hospital and user data
            $hospitalData = array_diff_key($data, array_flip(['password', 'password_confirmation', 'user_name']));
            
            // Update the hospital
            $record->update($hospitalData);

            // Find and update the associated user
            $user = User::where('account_type', 'hospital')->where('hospital_id', $record->id)->first();
            if ($user) {

                $user_check = User::where('email', $record->email)->get();
                if ($user_check && ($user_check->count() > 1 || $user_check->first()?->id != $user->id)) {
                    Notification::make()
                        ->title('Email already exists')
                        ->body('The email you entered is already in use by another hospital. Please use a different email.')
                        ->danger()
                        ->send();
                    return $record;
                }

                $userData = [
                    'name' => $data['user_name'] ?? $user->name,
                    'email' => $data['email'],
                ];

                if (!empty($data['password'])) {
                    $userData['password'] = $data['password'];
                }

                $user->update($userData);
                Log::info('User updated', ['user_id' => $user->id]);
            } else {
                Log::warning('Associated user not found', ['hospital_id' => $record->id, 'email' => $record->email]);
            }

            DB::commit();
            Log::info('Hospital and associated user updated successfully', ['hospital_id' => $record->id]);

            Notification::make()
                ->title('Hospital and user information updated successfully')
                ->success()
                ->send();

            return $record;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update hospital and user', ['error' => $e->getMessage()]);
            
            Notification::make()
                ->title('Update failed')
                ->body('An error occurred while updating the hospital and user information.')
                ->danger()
                ->send();

            throw $e;
        }
    }
}