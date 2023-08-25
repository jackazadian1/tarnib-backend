<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('poker_players', function (Blueprint $table) {
            $table->id();
            $table->integer('room_id');
            $table->string('name');
            $table->integer('buy_in_amount');
            $table->integer('cash_out_amount');
            $table->timestamps();
        });     
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('poker_players');
    }
};
