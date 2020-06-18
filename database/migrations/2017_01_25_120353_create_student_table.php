<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStudentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('students', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('roll_no');
            $table->integer('school_id');
            $table->integer('class_id');
            $table->string('division');
            $table->string('name');
            $table->text('address');
            $table->string('birthdate');
            $table->string('blood_group');
            $table->string('mobile');
            $table->time('school_time');
            $table->string('parents_name');
            $table->text('notes');
            $table->string('rfid_no');
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
        Schema::drop('students');
    }
}
