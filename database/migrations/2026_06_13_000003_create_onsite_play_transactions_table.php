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
        Schema::create('onsite_play_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('playstation_unit_id')->constrained('playstation_units')->onDelete('cascade');
            $table->dateTime('started_at');
            $table->dateTime('ended_at')->nullable();
            $table->integer('duration_minutes')->nullable();
            $table->decimal('hourly_rate', 12, 2);
            $table->decimal('total_price', 12, 2)->nullable();
            $table->enum('status', ['Berjalan', 'Selesai'])->default('Berjalan');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('onsite_play_transactions');
    }
};
