<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('email_logs', function (Blueprint $table) {
            $table->id('log_id');
            $table->enum('recipient_type', ['all', 'volunteers', 'organizations', 'active', 'single']);
            $table->integer('recipient_count')->default(0);
            $table->string('subject');
            $table->integer('sent_by');
            $table->timestamp('sent_at');
            $table->timestamps();
            
            $table->foreign('sent_by')->references('user_id')->on('users')->onDelete('cascade');
            $table->index('sent_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_logs');
    }
};