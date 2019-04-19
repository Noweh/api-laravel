<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateQuestionnairesTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('questionnaires')) {
            Schema::create('questionnaires', function (Blueprint $table) {
                $table->increments('id');
                $table->timestamps();
                $table->boolean('published')->nullable();
                $table->integer('level')->unsigned();
                $table->integer("position")->unsigned();
            });
        }

        if (!Schema::hasTable('questionnaire_translations')) {
            Schema::create('questionnaire_translations', function (Blueprint $table) {
                $table->increments('id');
                $table->boolean('active')->nullable()->default(1);
                $table->integer('questionnaire_id')
                    ->unsigned()->nullable()->index('ndx_questionnaire_translations_questionnaire_id');
                $table->string('locale', 3)->nullable();
                $table->foreign('questionnaire_id', 'fk_questionnaire_translations_questionnaire_id')
                    ->references('id')->on('questionnaires')->onUpdate('NO ACTION')->onDelete('cascade');
                $table->text('title')->nullable();
                $table->text('description')->nullable();
            });
        }

        if (!Schema::hasTable('questionnaire_themes')) {
            Schema::create('questionnaire_themes', function (Blueprint $table) {
                $table->integer('questionnaire_id')->unsigned()->index('questionnaire_themes_questionnaire_id');
                $table->integer('theme_id')->unsigned()->index('questionnaire_themes_theme_id');
                $table->primary(['questionnaire_id','theme_id']);
                $table->foreign('questionnaire_id', 'fk_questionnaire_themes_questionnaire_id')
                    ->references('id')->on('questionnaires')->onUpdate('NO ACTION')->onDelete('CASCADE');
                $table->foreign('theme_id', 'fk_questionnaire_themes_theme_id')
                    ->references('id')->on('themes')->onUpdate('NO ACTION')->onDelete('CASCADE');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('questionnaires');
        Schema::dropIfExists('questionnaire_translations');
        Schema::dropIfExists('questionnaire_themes');
    }
}