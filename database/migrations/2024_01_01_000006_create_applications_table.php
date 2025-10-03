<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('applications', function (Blueprint $table) {
            $table->id('application_id');
            $table->foreignId('opportunity_id')->constrained('volunteer_opportunities', 'opportunity_id')->onDelete('cascade');
            $table->foreignId('volunteer_id')->constrained('users', 'user_id')->onDelete('cascade');
            $table->text('motivation_letter')->nullable();
            $table->text('relevant_experience')->nullable();
            $table->text('availability_note')->nullable();
            $table->enum('status', ['Pending', 'Under Review', 'Accepted', 'Rejected', 'Withdrawn'])->default('Pending');
            $table->timestamp('applied_date')->useCurrent();
            $table->timestamp('reviewed_date')->nullable();
            $table->text('organization_notes')->nullable();
            $table->dateTime('interview_scheduled')->nullable();
            $table->timestamps();
            
            // Unique constraint
            $table->unique(['opportunity_id', 'volunteer_id']);
            
            // Indexes
            $table->index(['volunteer_id', 'status']);
            $table->index(['opportunity_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('applications');
    }
};