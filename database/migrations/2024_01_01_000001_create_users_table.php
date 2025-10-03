<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id('user_id');
            $table->string('email', 100)->unique();
            $table->string('password');
            $table->string('first_name', 50);
            $table->string('last_name', 50);
            $table->string('phone', 15)->nullable();
            $table->date('date_of_birth')->nullable();
            $table->enum('gender', ['Male', 'Female', 'Other'])->nullable();
            $table->string('city', 50)->nullable();
            $table->string('district', 50)->nullable();
            $table->text('address')->nullable();
            $table->enum('user_type', ['Volunteer', 'Organization', 'Admin']);
            $table->string('avatar_url')->nullable();
            $table->boolean('is_verified')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_login_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
            
            // Indexes
            $table->index('user_type');
            $table->index(['city', 'district']);
            $table->index(['is_active', 'is_verified']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};