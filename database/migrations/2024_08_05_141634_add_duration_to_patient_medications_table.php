<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDurationToPatientMedicationsTable extends Migration
{
    public function up()
    {
        Schema::table('patient_medications', function (Blueprint $table) {
            $table->integer('duration')->nullable()->after('instructions');
        });
    }

    public function down()
    {
        Schema::table('patient_medications', function (Blueprint $table) {
            $table->dropColumn('duration');
        });
    }
}