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
        Schema::create('reviews', function (Blueprint $table) {
            $table->id(); // Unique review ID
            $table->unsignedBigInteger('trip_id')->nullable(); // Yo'nalish IDsi
            $table->unsignedBigInteger('reviewer_id')->nullable(); // Sharh yozgan foydalanuvchi IDsi
            $table->unsignedBigInteger('reviewed_user_id')->nullable(); // Sharhga tegishli foydalanuvchi IDsi
            $table->tinyInteger('rating')->unsigned()->default(1); // Reyting (1-5 ball)
            $table->text('comment')->nullable(); // Sharh matni
            $table->timestamps(); // created_at va updated_at

            // Chet el kalitlar va bog‘lanishlar
            $table->foreign('trip_id')->references('id')->on('trips')->onDelete('set null');
            $table->foreign('reviewer_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('reviewed_user_id')->references('id')->on('users')->onDelete('set null');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
