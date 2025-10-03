<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('organizations', function (Blueprint $table) {
            $table->id('org_id');
            $table->foreignId('user_id')->constrained('users', 'user_id')->onDelete('cascade');
            $table->string('organization_name', 150);
            $table->enum('organization_type', ['NGO', 'NPO', 'Charity', 'School', 'Hospital', 'Community Group']);
            $table->text('description')->nullable();
            $table->text('mission_statement')->nullable();
            $table->string('website', 100)->nullable();
            $table->string('contact_person', 100)->nullable();
            $table->string('registration_number', 50)->nullable();
            $table->enum('verification_status', ['Pending', 'Verified', 'Rejected'])->default('Pending');
            $table->year('founded_year')->nullable();
            $table->integer('volunteer_count')->default(0);
            $table->decimal('rating', 3, 2)->default(0.00);
            $table->integer('total_opportunities')->default(0);
            $table->timestamps();
            
            // Indexes
            $table->index('verification_status');
            $table->index('rating');
            $table->fullText(['organization_name', 'description']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('organizations');
    }
};