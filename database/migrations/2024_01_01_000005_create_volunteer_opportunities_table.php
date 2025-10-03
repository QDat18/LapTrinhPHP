<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('volunteer_opportunities', function (Blueprint $table) {
            $table->id('opportunity_id');
            $table->foreignId('org_id')->constrained('organizations', 'org_id')->onDelete('cascade');
            $table->foreignId('category_id')->nullable()->constrained('categories', 'category_id')->onDelete('set null');
            $table->string('title', 200);
            $table->text('description');
            $table->text('requirements')->nullable();
            $table->text('benefits')->nullable();
            $table->string('location', 200)->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->enum('time_commitment', ['1-2 hours', '3-5 hours', '6-8 hours', 'Full day', 'Multiple days'])->nullable();
            $table->enum('schedule_type', ['One-time', 'Weekly', 'Monthly', 'Flexible'])->nullable();
            $table->integer('volunteers_needed')->default(1);
            $table->integer('volunteers_registered')->default(0);
            $table->integer('min_age')->default(16);
            $table->json('required_skills')->nullable();
            $table->enum('experience_needed', ['No experience', 'Some experience', 'Experienced'])->default('No experience');
            $table->enum('status', ['Active', 'Paused', 'Completed', 'Cancelled'])->default('Active');
            $table->date('application_deadline')->nullable();
            $table->integer('view_count')->default(0);
            $table->integer('application_count')->default(0);
            $table->timestamps();
            
            // Indexes
            $table->index(['status', 'start_date']);
            $table->index('location');
            $table->index('category_id');
            $table->fullText(['title', 'description']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('volunteer_opportunities');
    }
};