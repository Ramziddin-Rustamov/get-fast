<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Pivot: qaysi safar (parcel) qaysi turdagi pochtalarni qabul qiladi.
     */
    public function up(): void
    {
        Schema::create('parcel_parcel_type', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parcel_id')->constrained()->cascadeOnDelete();
            $table->foreignId('parcel_type_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['parcel_id', 'parcel_type_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parcel_parcel_type');
    }
};
