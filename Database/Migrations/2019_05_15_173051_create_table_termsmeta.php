<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableTermsmeta extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('termmeta', function (Blueprint $table) {
            $table->increments('id_termmeta');
            $table->string('meta_key');
            $table->longText('meta_value')->nullable();
            $table->unsignedInteger('term_id');
            $table->timestamps();
        });

        Schema::table('termmeta', function($table){
            $table->foreign('term_id')->references(\Gdevilbat\SpardaCMS\Modules\Taxonomy\Entities\Terms::getPrimaryKey())->on('terms')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('termmeta');
    }
}
