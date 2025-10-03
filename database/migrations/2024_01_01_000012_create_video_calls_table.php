<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('video_calls', function (Blueprint $table) {
            $table->id('call_id');
            $table->foreignId('conversation_id')->constrained('conversations', 'conversation_id')->onDelete('cascade');
            $table->foreignId('initiated_by')->constrained('users', 'user_id')->onDelete('cascade');
            $table->enum('call_type', ['audio', 'video'])->default('video');
            $table->enum('call_status', ['initiated', 'ringing', 'active', 'ended', 'missed', 'declined'])->default('initiated');
            $table->string('room_id', 100)->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->integer('duration')->default(0);
            $table->timestamp('created_at')->useCurrent();
            
            // Index
            $table->index(['call_status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('video_calls');
    }
};
