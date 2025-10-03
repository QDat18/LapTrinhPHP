<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id('notification_id');
            $table->foreignId('user_id')->constrained('users', 'user_id')->onDelete('cascade');
            $table->enum('notification_type', ['Application', 'Message', 'Video Call', 'Review', 'System', 'Opportunity']);
            $table->string('title', 150);
            $table->text('content')->nullable();
            $table->integer('related_id')->nullable();
            $table->enum('related_type', ['application', 'opportunity', 'message', 'call', 'user'])->nullable();
            $table->string('action_url')->nullable();
            $table->boolean('is_read')->default(false);
            $table->enum('priority', ['low', 'medium', 'high'])->default('medium');
            $table->timestamp('created_at')->useCurrent();
            
            // Index
            $table->index(['user_id', 'is_read', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};