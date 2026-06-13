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
        Schema::create('rental_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('playstation_unit_id')->constrained('playstation_units')->onDelete('cascade');
            $table->string('renter_name');
            $table->string('phone');
            $table->string('identity_card_path')->nullable();
            $table->date('rental_start_date');
            $table->date('rental_end_date');
            $table->decimal('rental_days', 5, 1);
            $table->decimal('daily_rate', 12, 2);
            $table->boolean('include_tv')->default(false);
            $table->decimal('tv_price', 12, 2)->default(0);
            $table->decimal('total_price', 12, 2);
            $table->enum('status', ['Disewa', 'Dikembalikan'])->default('Disewa');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rental_transactions');
    }
};
