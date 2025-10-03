<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;


return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id('review_id');
            $table->foreignId('reviewer_id')->constrained('users', 'user_id')->onDelete('cascade');
            $table->foreignId('reviewee_id')->constrained('users', 'user_id')->onDelete('cascade');
            $table->foreignId('opportunity_id')->nullable()->constrained('volunteer_opportunities', 'opportunity_id')->onDelete('set null');
            $table->tinyInteger('rating')->unsigned();
            $table->string('review_title', 100)->nullable();
            $table->text('review_text')->nullable();
            $table->enum('review_type', ['Volunteer to Organization', 'Organization to Volunteer']);
            $table->boolean('is_approved')->default(false);
            $table->integer('helpful_count')->default(0);
            $table->timestamp('created_at')->useCurrent();

            // Unique constraint
            $table->unique(['reviewer_id', 'reviewee_id', 'opportunity_id']);

            // Indexes
            $table->index('rating');

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
