<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('conversations', function (Blueprint $table) {
            $table->id('conversation_id');
            $table->enum('conversation_type', ['direct', 'group', 'opportunity_chat'])->default('direct');
            $table->string('title', 100)->nullable();
            $table->foreignId('opportunity_id')->nullable()->constrained('volunteer_opportunities', 'opportunity_id')->onDelete('cascade');
            $table->foreignId('created_by')->constrained('users', 'user_id')->onDelete('cascade');
            $table->timestamp('last_message_at')->useCurrent();
            $table->boolean('is_active')->default(true);
            $table->timestamp('created_at')->useCurrent();
            
            // Index
            $table->index('last_message_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conversations');
    }
};