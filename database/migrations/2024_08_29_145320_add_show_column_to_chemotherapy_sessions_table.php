<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


class AddShowColumnToChemotherapySessionsTable extends Migration
{
    public function up()
    {
        Schema::table('chemotherapy_sessions', function (Blueprint $table) {
            $table->boolean('show')->default(false);
        });
    }

    public function down()
    {
        Schema::table('chemotherapy_sessions', function (Blueprint $table) {
            $table->dropColumn('show');
        });
    }
}
