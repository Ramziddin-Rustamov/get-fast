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
            $table->foreignId('driver_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('vehicle_id')->constrained('vehicles')->onDelete('cascade');
            $table->foreignId('start_point_id')->nullable()->constrained('points')->nullOnDelete();
            $table->foreignId('end_point_id')->nullable()->constrained('points')->nullOnDelete();
            $table->foreignId('start_quarter_id')->nullable();
            $table->string('end_quarter_id')->nullable();
            $table->timestamp('start_time');
            $table->timestamp('end_time')->nullable();
            $table->decimal('price_per_seat', 8, 2);
            $table->integer('total_seats')->default(4);
            $table->integer('available_seats');
            $table->enum('status', ['active', 'completed', 'cancelled','expired','full'])->default('active');
            $table->timestamp('expired_at')->nullable(); // Add the expired_at column it equal to end time
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
