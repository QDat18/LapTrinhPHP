<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->id('message_id');
            $table->foreignId('conversation_id')->constrained('conversations', 'conversation_id')->onDelete('cascade');
            $table->foreignId('sender_id')->constrained('users', 'user_id')->onDelete('cascade');
            $table->enum('message_type', ['text', 'image', 'file', 'video', 'opportunity_share'])->default('text');
            $table->text('content')->nullable();
            $table->string('attachment_url')->nullable();
            $table->string('attachment_name', 100)->nullable();
            $table->boolean('is_deleted')->default(false);
            $table->timestamp('sent_at')->useCurrent();
            
            // Index
            $table->index(['conversation_id', 'sent_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
