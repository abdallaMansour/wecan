<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');

            // Additional required fields for patients
            $table->foreignId('country_id')->nullable(); // Only for patients
            $table->string('preferred_language')->default('ar');

            // Additional fields for all user types
            $table->enum('account_type', ['admin', 'patient', 'doctor'])->default('admin');
            $table->enum('account_status', ['active', 'cancelled', 'banned'])->default('active');

            // Additional optional fields for doctors
            $table->string('profession_ar')->nullable();
            $table->string('profession_en')->nullable();
            $table->string('hospital_ar')->nullable();
            $table->string('hospital_en')->nullable();
            $table->string('contact_number')->nullable();
            $table->unsignedInteger('experience_years')->nullable();
            $table->string('profile_picture')->nullable();
            $table->boolean('show_info_to_patients')->default(true);

            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
