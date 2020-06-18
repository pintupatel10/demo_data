<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('email');
            $table->string('password');
            $table->integer('school_id');
            $table->integer('class_id');
            $table->string('division');
            $table->text('address');
            $table->string('birthdate');
            $table->string('blood_group');
            $table->string('mobile');
            $table->time('school_time');
            $table->string('parents_name');
            $table->text('notes');
            $table->string('rfid_no');
            $table->string('status')->comment("in_active,active");
            $table->string('role')->comment("admin,staff,student");
            $table->string('language');
            $table->string('location');
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });

        DB::table('users')->insert([
            'name' => 'admin',
            'email' => 'admin@admin.com',
            'password' => bcrypt('123456'),
            'role' => 'admin',
            'status' => 'active'
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('users');
    }
}
