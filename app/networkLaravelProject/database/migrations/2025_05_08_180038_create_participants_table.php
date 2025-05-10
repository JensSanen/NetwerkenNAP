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
        Schema::create('participants', function (Blueprint $table) {
            $table->integer('id')->autoIncrement()->primary();
            $table->string('email', 255)->nullable(false);
            $table->integer('poll_id')->nullable(false);
            $table->boolean('has_voted')->default(false);
            $table->string('vote_token', 16)->nullable(false);
            $table->timestamps();

            // Foreign key constraint
            $table->foreign('poll_id')
                  ->references('id')
                  ->on('polls')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('participants');
    }
};
