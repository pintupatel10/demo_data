<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAttendanceDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('attendance_details', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('attendance_id');
            $table->string('rfid_no');
            $table->integer('school_id');
            $table->integer('student_id');
            $table->string('student_name');
            $table->string('class_name');
            $table->string('class_division');
            $table->time('school_in_time');
            $table->time('school_out_time');
            $table->date('attendance_date');
            $table->time('attendance_time');
            $table->integer('device_id');
            $table->integer('staff_id');
            $table->string('staff_name');
            $table->string('staff_role');
            $table->integer('on_leave');
            $table->string('status')->comment("in_active,active");
            $table->string('latittude');
            $table->string('longitude');
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
        Schema::drop('attendance_details');
    }
}
