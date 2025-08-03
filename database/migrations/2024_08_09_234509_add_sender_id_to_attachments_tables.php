<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSenderIdToAttachmentsTables extends Migration
{
    public function up()
    {
        Schema::table('hospital_user_attachments', function (Blueprint $table) {
            $table->unsignedBigInteger('sender_id')->nullable()->after('status');
        });

        Schema::table('user_attachments', function (Blueprint $table) {
            $table->unsignedBigInteger('sender_id')->nullable()->after('status');
        });
    }

    public function down()
    {
        Schema::table('hospital_user_attachments', function (Blueprint $table) {
            $table->dropColumn('sender_id');
        });

        Schema::table('user_attachments', function (Blueprint $table) {
            $table->dropColumn('sender_id');
        });
    }
}