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
        Schema::table('users', function (Blueprint $table) {
            // remove the 'name' column
            // $table->dropColumn('name');

            // Add new name ar , en column
            // $table->string('name_ar')->nullable();
            $table->string('name_en')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Add the 'name' column back
            // $table->string('name')->nullable();

            // Remove the new name ar , en column
            // $table->dropColumn('name_ar');
            $table->dropColumn('name_en');
        });
    }
};
