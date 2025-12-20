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

            // Automatic indexed foreign keys
            $table->foreignId('driver_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('vehicle_id')->constrained('vehicles')->onDelete('cascade');

            $table->foreignId('start_region_id')->nullable()->constrained('regions')->nullOnDelete();
            $table->foreignId('end_region_id')->nullable()->constrained('regions')->nullOnDelete();

            $table->foreignId('start_district_id')->nullable()->constrained('districts')->nullOnDelete();
            $table->foreignId('end_district_id')->nullable()->constrained('districts')->nullOnDelete();

            $table->foreignId('start_quarter_id')->nullable()->constrained('quarters')->nullOnDelete();
            $table->foreignId('end_quarter_id')->nullable()->constrained('quarters')->nullOnDelete();

            $table->foreignId('start_point_id')->nullable()->constrained('points')->nullOnDelete();
            $table->foreignId('end_point_id')->nullable()->constrained('points')->nullOnDelete();

            $table->timestamp('start_time')->index(); 
            $table->timestamp('end_time')->nullable();

            $table->decimal('price_per_seat', 8, 2);
            $table->integer('total_seats')->default(4);

            $table->integer('available_seats')->index();

            $table->enum('status', ['active', 'completed', 'cancelled', 'expired', 'full'])
                ->default('active')
                ->index(); 

            $table->timestamp('expired_at')->nullable();
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
