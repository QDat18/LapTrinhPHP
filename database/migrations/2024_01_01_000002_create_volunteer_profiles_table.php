<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('volunteer_profiles', function (Blueprint $table) {
            $table->id('profile_id');
            $table->foreignId('user_id')->constrained('users', 'user_id')->onDelete('cascade');
            $table->string('occupation', 100)->nullable();
            $table->enum('education_level', ['High School', 'Diploma', 'Bachelor', 'Master', 'PhD'])->nullable();
            $table->string('university', 100)->nullable();
            $table->text('bio')->nullable();
            $table->json('skills')->nullable();
            $table->text('interests')->nullable();
            $table->enum('availability', ['Weekdays', 'Weekends', 'Flexible', 'Full-time'])->nullable();
            $table->text('volunteer_experience')->nullable();
            $table->integer('total_volunteer_hours')->default(0);
            $table->decimal('volunteer_rating', 3, 2)->default(0.00);
            $table->string('preferred_location', 100)->nullable();
            $table->enum('transportation', ['Motorbike', 'Car', 'Public Transport', 'Walking'])->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index('volunteer_rating');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('volunteer_profiles');
    }
};