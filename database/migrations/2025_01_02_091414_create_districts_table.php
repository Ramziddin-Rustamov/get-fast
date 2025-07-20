<?php

use Illuminate\Support\Facades\DB;
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
        if(!Schema::hasTable('districts')){

            Schema::create('districts', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('region_id');
                $table->foreign('region_id')->references('id')->on('regions')->onDelete('cascade');
                $table->string('name_uz');
                $table->string('name_ru');
                $table->string('name_en');
                $table->timestamps();
            });
    
            $sql = file_get_contents(public_path('db/districts.sql'));
            $queries = explode(';', $sql);
            foreach ($queries as $query) {
                if (!empty(trim($query))) {
                    DB::statement($query);
                }
            }
            
           }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('districts');
    }
};
