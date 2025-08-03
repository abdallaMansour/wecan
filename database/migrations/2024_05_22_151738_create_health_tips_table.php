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
        Schema::create('health_tips', function (Blueprint $table) {
            $table->id();
            $table->dateTime('publish_datetime');
            $table->string('title_ar')->nullable();
            $table->string('title_en')->nullable();
            $table->text('details_ar')->nullable();
            $table->text('details_en')->nullable();
            $table->json('attachments')->nullable();
            $table->text('link')->nullable();
            $table->enum('tip_type', ['Medication Tips', 'General Tips', 'Nutrition Tips', 'Dosage Tips', 'Other'])->default('General Tips');
            $table->foreignId('user_id');
            $table->boolean('visible')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('health_tips');
    }
};
