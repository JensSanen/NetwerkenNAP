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
        Schema::create('poll_dates', function (Blueprint $table) {
            $table->integer('id')->autoIncrement()->primary();
            $table->integer('poll_id')->nullable(false);
            $table->date('date')->nullable(false);
            $table->timestamps();

            $table->foreign('poll_id')
                  ->references('id')
                  ->on('polls')
                  ->onDelete('cascade');
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('poll_dates');
    }
};
