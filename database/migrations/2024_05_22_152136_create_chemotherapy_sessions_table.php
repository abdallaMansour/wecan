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
        Schema::create('chemotherapy_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('session_number');
            $table->dateTime('session_datetime');
            $table->text('instructions')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('user_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chemotherapy_sessions');
    }
};
