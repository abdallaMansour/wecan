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
        Schema::create('cancer_screening_centers', function (Blueprint $table) {
            $table->id();
            $table->string('hospital_logo')->nullable();
            $table->string('hospital_name_ar');
            $table->string('hospital_name_en');
            $table->string('phone_number')->nullable();
            $table->foreignId('country_id');
            $table->foreignId('region_id');
            $table->text('website')->nullable();
            $table->text('google_map_link');
            $table->boolean('visible')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cancer_screening_centers');
    }
};
