<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::query()->create([
            'name' => 'Admin',
            'email' => 'admin@mail.com',
            'password' => Hash::make('password'),
            'account_type' => 'admin'
        ]);

        User::query()->create([
            'name' => 'Patient',
            'email' => 'patient@mail.com',
            'password' => Hash::make('password'),
            'account_type' => 'patient'
        ]);


        User::query()->create([
            'name' => 'Doctor',
            'email' => 'doctor@mail.com',
            'password' => Hash::make('password'),
            'account_type' => 'doctor'
        ]);
    }
}
