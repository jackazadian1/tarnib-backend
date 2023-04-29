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
        Schema::create('rounds', function (Blueprint $table) {
            $table->id();
            $table->string('room_id');
            $table->text('player_1_cards')->nullable();
            $table->text('player_2_cards')->nullable();
            $table->text('player_3_cards')->nullable();
            $table->text('player_4_cards')->nullable();
            $table->integer('turn')->nullable();
            $table->integer('team_1_score')->nullable();
            $table->integer('team_2_score')->nullable();
            $table->integer('dealer')->nullable();
            $table->string('tarnib')->nullable();
            $table->integer('goal')->nullable();
            $table->text('bids_data')->nullable();
            $table->text('current_play')->nullable();
            $table->integer('player_turn')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rounds');
    }
};
