<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('webinars', function (Blueprint $table) {
            $table->boolean('analyze_enabled')->default(false);
        });
    }

    public function down()
    {
        Schema::table('webinars', function (Blueprint $table) {
            $table->dropColumn('analyze_enabled');
        });
    }
};
