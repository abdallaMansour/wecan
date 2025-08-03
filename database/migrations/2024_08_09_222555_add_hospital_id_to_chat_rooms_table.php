<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddHospitalIdToChatRoomsTable extends Migration
{
    public function up()
    {
        Schema::table('chat_rooms', function (Blueprint $table) {
            $table->unsignedBigInteger('hospital_id')->nullable()->after('id');
            $table->foreign('hospital_id')->references('id')->on('hospitals')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('chat_rooms', function (Blueprint $table) {
            $table->dropForeign(['hospital_id']);
            $table->dropColumn('hospital_id');
        });
    }
}