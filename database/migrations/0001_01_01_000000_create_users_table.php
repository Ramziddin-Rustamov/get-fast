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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('father_name')->nullable();
            $table->string('email')->unique();
            $table->string('phone')->unique();
            $table->string('password')->nullable();
            $table->string('image')->nullable()->default('default.jpg');
            $table->string('region_id')->nullable();
            $table->string('district_id')->nullable();
            $table->string('quarter_id')->nullable();
            $table->string('home')->nullable();
            $table->enum('role', ['client', 'driver', 'admin'])->default('client');
            $table->boolean('is_verified')->default(false);
            $table->string('verification_code')->nullable();
            $table->string('driving_licence_number')->nullable();
            $table->string('driving_licence_expiry')->nullable();
            $table->string('birth_date')->nullable();
            $table->enum('driving_verification_status', ['none', 'pending', 'approved', 'rejected', 'blocked'])->default('none');
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('phone')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
