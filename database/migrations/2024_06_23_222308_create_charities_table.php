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
        Schema::create('charities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('country_id');
            $table->string('charity_logo_ar')->nullable();
            $table->string('charity_logo_en')->nullable();
            $table->string('charity_name_ar');
            $table->string('charity_name_en');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('charities');
    }
};
