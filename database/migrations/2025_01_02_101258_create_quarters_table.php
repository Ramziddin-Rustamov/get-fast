<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('quarters', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('district_id');
            $table->string('name');
            $table->timestamps();
            $table->foreign('district_id')->references('id')->on('districts')->onDelete('cascade');
        });

        $sql = file_get_contents(public_path('db/quarters.sql'));
        $queries = explode(';', $sql);
        foreach ($queries as $query) {
            if (!empty(trim($query))) {
                DB::statement($query);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quarters');
    }
};
