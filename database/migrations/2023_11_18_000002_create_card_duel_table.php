<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCardDuelTable extends Migration
{
    public function up()
    {
        Schema::create('card_duel', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('duel_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('card_id');
            $table->timestamps();

            $table->foreign('duel_id')->references('id')->on('duels')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('card_id')->references('id')->on('cards')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('card_duel');
    }
}
