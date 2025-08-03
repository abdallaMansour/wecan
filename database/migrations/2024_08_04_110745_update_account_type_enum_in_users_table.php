<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Step 1: Convert enum to varchar
        Schema::table('users', function (Blueprint $table) {
            $table->string('account_type', 20)->change();
        });

        // Step 2: Update the column type to the new enum
        DB::statement("ALTER TABLE users MODIFY COLUMN account_type ENUM('admin', 'patient', 'doctor', 'hospital') DEFAULT 'admin'");
    }

    public function down(): void
    {
        // Revert the changes
        DB::statement("ALTER TABLE users MODIFY COLUMN account_type ENUM('admin', 'patient', 'doctor') DEFAULT 'admin'");
    }
};