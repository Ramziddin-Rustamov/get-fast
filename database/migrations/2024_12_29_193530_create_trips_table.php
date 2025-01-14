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
        Schema::create('trips', function (Blueprint $table) {
            $table->id();
            $table->foreignId('driver_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('vehicle_id')->constrained('vehicles')->onDelete('cascade');
            $table->string('start_location');
            $table->string('end_location');
            $table->timestamp('start_time');
            $table->timestamp('end_time')->nullable();
            $table->decimal('price_per_seat', 8, 2);
            $table->integer('total_seats');
            $table->integer('available_seats');
            $table->enum('status', ['active', 'completed', 'cancelled','expired'])->default('active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trips');
    }
};
