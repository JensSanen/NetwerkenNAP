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
        Schema::create('votes', function (Blueprint $table) {
            $table->integer('id')->autoIncrement()->primary();
            $table->integer('participant_id')->nullable(false);
            $table->integer('poll_date_id')->nullable(false);
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('participant_id')
                  ->references('id')
                  ->on('participants')
                  ->onDelete('cascade');

            $table->foreign('poll_date_id')
                  ->references('id')
                  ->on('poll_dates')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('votes');
    }
};
