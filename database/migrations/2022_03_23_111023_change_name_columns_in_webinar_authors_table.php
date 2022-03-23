<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeNameColumnsInWebinarAuthorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::rename('webinar_authors', 'webinar_trainers');
        Schema::table('webinar_trainers', function (Blueprint $table) {
            $table->dropForeign('webinar_authors_author_id_foreign');
            $table->dropForeign('webinar_authors_webinar_id_foreign');
            $table->renameColumn('author_id', 'trainer_id');
            $table->foreign('trainer_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('webinar_id')->references('id')->on('webinars')->cascadeOnDelete();
            $table->renameIndex('webinar_authors_pkey', 'webinar_trainers_pkey');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::rename('webinar_trainers', 'webinar_authors');
        Schema::table('webinar_authors', function (Blueprint $table) {
            $table->dropForeign('webinar_trainers_trainer_id_foreign');
            $table->dropForeign('webinar_trainers_webinar_id_foreign');
            $table->renameColumn('trainer_id', 'author_id');
            $table->foreign('trainer_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('webinar_id')->references('id')->on('webinars')->cascadeOnDelete();
            $table->renameIndex('webinar_trainers_pkey', 'webinar_authors_pkey');
        });
    }
}
