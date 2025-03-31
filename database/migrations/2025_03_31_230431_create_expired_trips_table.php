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
        Schema::create('expired_trips', function (Blueprint $table) {
            $table->id();
            $table->foreignId('driver_id')->nullable();
            $table->foreignId('vehicle_id')->nullable();
            $table->foreignId('start_quarter_id')->nullable();
            $table->string('end_quarter_id')->nullable();
            $table->timestamp('start_time')->nullable();
            $table->timestamp('end_time')->nullable();
            $table->decimal('price_per_seat', 8, 2)->nullable();
            $table->integer('total_seats')->default(4);
            $table->integer('available_seats')->nullable();
            $table->enum('status', ['active', 'completed', 'cancelled','expired','full'])->default('expired');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expired_trips');
    }
};
