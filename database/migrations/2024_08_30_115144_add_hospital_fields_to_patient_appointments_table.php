<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('patient_appointments', function (Blueprint $table) {
            $table->boolean('is_hospital')->default(false);
            $table->unsignedBigInteger('hospital_id')->nullable();
            $table->foreign('hospital_id')->references('id')->on('hospitals')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('patient_appointments', function (Blueprint $table) {
            $table->dropForeign(['hospital_id']);
            $table->dropColumn(['is_hospital', 'hospital_id']);
        });
    }
};