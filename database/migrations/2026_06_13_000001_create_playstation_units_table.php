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
        Schema::create('playstation_units', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type'); // PS3, PS4, PS5, etc.
            $table->enum('status', ['Tersedia', 'Bermain', 'Disewa', 'Maintenance'])->default('Tersedia');
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('playstation_units');
    }
};
