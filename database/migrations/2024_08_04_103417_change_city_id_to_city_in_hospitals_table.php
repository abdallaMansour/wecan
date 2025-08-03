<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeCityIdToCityInHospitalsTable extends Migration
{
    public function up()
    {
        Schema::table('hospitals', function (Blueprint $table) {
            $table->dropColumn('city_id');
            $table->string('city')->after('country_id'); // Add city column
        });
    }

    public function down()
    {
        Schema::table('hospitals', function (Blueprint $table) {
            $table->dropColumn('city');
            $table->unsignedBigInteger('city_id')->after('country_id'); // Revert back to city_id
        });
    }
}