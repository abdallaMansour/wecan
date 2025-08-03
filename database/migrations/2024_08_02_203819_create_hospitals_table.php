<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHospitalsTable extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('hospitals')) {
            Schema::create('hospitals', function (Blueprint $table) {
                $table->id();
                $table->string('hospital_name');
                $table->string('hospital_logo')->nullable();
                $table->string('user_name');
                $table->string('email');
                $table->string('contact_number');
                $table->foreignId('country_id')->constrained();
                $table->foreignId('city_id')->constrained();
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('hospitals');
    }
}