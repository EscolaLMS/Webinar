<?php

use EscolaLms\Webinar\Enum\WebinarStatusEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWebinarsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('webinars', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('author_id')->unsigned()->nullable();
            $table->integer('base_price')->nullable();
            $table->string('name', 255);
            $table->string('status')->default(WebinarStatusEnum::DRAFT);
            $table->text('description');
            $table->string('duration')->nullable();
            $table->dateTime('active_from')->nullable();
            $table->dateTime('active_to')->nullable();
            $table->timestamps();

            $table->foreign('author_id')->references('id')->on('users')->onDelete('SET NULL');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('webinars');
    }
}
