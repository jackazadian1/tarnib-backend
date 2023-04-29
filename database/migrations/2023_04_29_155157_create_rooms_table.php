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
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->string('room_id')->nullable();
            $table->string('player_1')->default('');
            $table->string('player_2')->default('');
            $table->string('player_3')->default('');
            $table->string('player_4')->default('');
            $table->string('player_1_token')->nullable();
            $table->string('player_2_token')->nullable();
            $table->string('player_3_token')->nullable();
            $table->string('player_4_token')->nullable();
            $table->integer('round_id')->nullable();
            $table->integer('team_1_score')->nullable();
            $table->integer('team_2_score')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};
