<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCcolumnsYTForWebinarTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('webinars', function (Blueprint $table) {
            $table->string('yt_id', 30)->nullable();
            $table->string('yt_url', 255)->nullable();
            $table->string('yt_stream_url', 255)->nullable();
            $table->string('yt_stream_key', 255)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('webinars', function (Blueprint $table) {
            $table->dropColumn(['yt_url', 'yt_stream_url', 'yt_stream_key', 'yt_id']);
        });
    }
}
