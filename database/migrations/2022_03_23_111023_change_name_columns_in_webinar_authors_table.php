<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeNameColumnsInWebinarAuthorsTable extends Migration
{
    public function up(): void
    {
        Schema::rename('webinar_authors', 'webinar_trainers');
        Schema::table('webinar_trainers', function (Blueprint $table) {
            $table->dropForeign('webinar_authors_author_id_foreign');
            $table->dropForeign('webinar_authors_webinar_id_foreign');
            $table->renameColumn('author_id', 'trainer_id');
            $table->foreign('trainer_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('webinar_id')->references('id')->on('webinars')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::rename('webinar_trainers', 'webinar_authors');
        Schema::table('webinar_authors', function (Blueprint $table) {
            $table->dropForeign('webinar_trainers_trainer_id_foreign');
            $table->dropForeign('webinar_trainers_webinar_id_foreign');
            $table->renameColumn('trainer_id', 'author_id');
            $table->foreign('author_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('webinar_id')->references('id')->on('webinars')->cascadeOnDelete();
        });
    }
}
