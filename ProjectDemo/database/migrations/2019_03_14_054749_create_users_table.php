<?php

use Illuminate\Support\Facades\Schema;
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
            $table->bigIncrements('id');
            $table->string('fullname');
            $table->string('username');
            $table->text('password');
            $tabel->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->text('birthday')->nullable();
            $table->string('address')->nullable();
            $table->string('job')->nullable();
            $table->text('avatar');
            $table->integer('id_blog')->index()->unsigned();
            $tabel->foreign('id_blog')->reference('id')->on('blogs');
            $tabel->interger('id_Comment')->index()->unsigned();
            $tabel->foreign('id_comment')->reference('id')->on('comments');
            $tabel->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
