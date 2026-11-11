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
        Schema::create('user_flashcards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('flashcard_id')->constrained()->onDelete('cascade');
            $table->integer('bin')->default(0); // 0-11 bins for spaced repetition
            $table->timestamp('next_review_at')->nullable();
            $table->integer('incorrect_count')->default(0); // Track lifetime incorrect answers
            $table->boolean('is_hard_to_remember')->default(false); // Moved to "hard to remember" bin
            $table->timestamps();

            $table->unique(['user_id', 'flashcard_id']); // Each user can only have one instance of each word
            $table->index(['user_id', 'bin', 'next_review_at']);
            $table->index(['user_id', 'is_hard_to_remember']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_flashcards');
    }
};
