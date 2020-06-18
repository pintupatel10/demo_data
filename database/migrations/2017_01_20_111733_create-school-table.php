<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSchoolTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('schools', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('medium')->comment("English, Gujarati, Hindi, Sanskrit, English & Gujarati, English & Hindi");
            $table->string('type')->comment("State, CBSC, IGCE");
            $table->string('phone');
            $table->string('mobile');
            $table->string('website');
            $table->string('image');
            $table->string('principal_name');
            $table->string('trustee_name');
            $table->string('detail');
            $table->integer('tot_strength');
            $table->time('start_time');
            $table->time('end_time');
            $table->time('week_start_time');
            $table->time('week_end_time');
            $table->string('refer_by');
            $table->string('market_by');
            $table->double('Latitude');
            $table->double('Longitude');
            $table->string('Address');
            $table->string('status')->comment("in_active,active");
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('schools');
    }
}
