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
        Schema::create('flashcard_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_flashcard_id')->constrained()->onDelete('cascade');
            $table->boolean('is_correct');
            $table->integer('bin_before')->nullable(); // Bin before this review
            $table->integer('bin_after')->nullable(); // Bin after this review
            $table->timestamp('reviewed_at');
            $table->timestamps();

            $table->index(['user_flashcard_id', 'reviewed_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('flashcard_reviews');
    }
};
