<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('volunteer_activities', function (Blueprint $table) {
            $table->id('activity_id');
            $table->foreignId('volunteer_id')->constrained('users', 'user_id')->onDelete('cascade');
            $table->foreignId('opportunity_id')->constrained('volunteer_opportunities', 'opportunity_id')->onDelete('cascade');
            $table->foreignId('org_id')->constrained('organizations', 'org_id')->onDelete('cascade');
            $table->date('activity_date');
            $table->decimal('hours_worked', 4, 2);
            $table->text('activity_description')->nullable();
            $table->enum('status', ['Pending', 'Verified', 'Disputed'])->default('Pending');
            $table->foreignId('verified_by')->nullable()->constrained('users', 'user_id')->onDelete('set null');
            $table->timestamp('verified_date')->nullable();
            $table->text('impact_notes')->nullable();
            $table->timestamp('created_at')->useCurrent();
            
            // Indexes
            $table->index(['volunteer_id', 'activity_date']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('volunteer_activities');
    }
};
