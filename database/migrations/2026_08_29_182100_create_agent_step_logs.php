<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('agent_step_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('chat_message_id')->constrained('chat_messages')->cascadeOnDelete();
            $table->enum('step_type', ['planning', 'tool_execution', 'data_processing', 'synthesis']);
            $table->string('tool_name')->nullable();
            $table->string('endpoint')->nullable();
            $table->enum('status', ['pending', 'success', 'failed'])->default('pending');
            $table->jsonb('payload_data')->nullable();
            $table->text('error_message')->nullable();
            $table->integer('duration_ms')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agent_step_logs');
    }
};
