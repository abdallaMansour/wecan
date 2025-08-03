<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAccountTypeToHospitalUserAttachmentsTable extends Migration
{
    public function up()
    {
        Schema::table('hospital_user_attachments', function (Blueprint $table) {
            $table->string('account_type')->after('user_id');
        });
    }

    public function down()
    {
        Schema::table('hospital_user_attachments', function (Blueprint $table) {
            $table->dropColumn('account_type');
        });
    }
}