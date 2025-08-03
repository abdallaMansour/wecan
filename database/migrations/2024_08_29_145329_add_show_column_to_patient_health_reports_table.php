<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


class AddShowColumnToPatientHealthReportsTable extends Migration
{
    public function up()
    {
        Schema::table('patient_health_reports', function (Blueprint $table) {
            $table->boolean('show')->default(false);
        });
    }

    public function down()
    {
        Schema::table('patient_health_reports', function (Blueprint $table) {
            $table->dropColumn('show');
        });
    }
}