<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('system_analytics', function (Blueprint $table) {
            $table->id('analytics_id');
            $table->string('metric_name', 50);
            $table->integer('metric_value');
            $table->date('record_date');
            $table->string('category', 30)->default('general');
            $table->json('metadata')->nullable();
            $table->timestamp('created_at')->useCurrent();
            
            // Unique constraint
            $table->unique(['metric_name', 'record_date', 'category']);
            
            // Index
            $table->index(['record_date', 'category']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('system_analytics');
    }
};