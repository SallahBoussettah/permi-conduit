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
        // Table for AI chat questions and answers managed by admin
        Schema::create('ai_chat_faqs', function (Blueprint $table) {
            $table->id();
            $table->string('trigger_phrase');
            $table->text('question');
            $table->text('answer');
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });

        // Table for chat conversations
        Schema::create('chat_conversations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('candidate_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('inspector_id')->nullable()->constrained('users')->onDelete('set null');
            $table->enum('status', ['active', 'inspector_joined', 'closed'])->default('active');
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
        });

        // Table for chat messages
        Schema::create('chat_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained('chat_conversations')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->boolean('is_from_ai')->default(false);
            $table->text('message');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chat_messages');
        Schema::dropIfExists('chat_conversations');
        Schema::dropIfExists('ai_chat_faqs');
    }
}; 