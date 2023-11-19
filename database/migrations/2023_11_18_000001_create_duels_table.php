<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDuelsTable extends Migration
{
    public function up()
    {
        Schema::create('duels', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user1_id');
            $table->unsignedBigInteger('user2_id');
            $table->unsignedBigInteger('winner_id')->nullable();
            $table->unsignedInteger('round')->default(1);
            $table->boolean('active')->default(true);
            $table->integer('user_1_points')->default(0);
            $table->integer('user_2_points')->default(0);
            $table->timestamps();

            $table->foreign('user1_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('user2_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('winner_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('duels');
    }
}
