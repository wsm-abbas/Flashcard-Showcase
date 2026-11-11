<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;
use App\Enums\BinInterval;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserFlashcard extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'flashcard_id',
        'bin',
        'next_review_at',
        'incorrect_count',
        'is_hard_to_remember',
    ];

    protected $casts = [
        'next_review_at' => 'datetime',
        'is_hard_to_remember' => 'boolean',
    ];

    /**
     * Get the user that owns this flashcard progress
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the flashcard
     */
    public function flashcard(): BelongsTo
    {
        return $this->belongsTo(Flashcard::class);
    }

    /**
     * Get all reviews for this user flashcard
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(FlashcardReview::class);
    }

    /**
     * Check if this user flashcard is ready for review
     */
    public function isReadyForReview(): bool
    {
        if ($this->is_hard_to_remember) {
            return false;
        }

        if ($this->bin === 0) {
            return true; // New cards are always ready
        }

        return $this->next_review_at && $this->next_review_at->isPast();
    }

    /**
     * Calculate next review time based on bin
     */
    public function calculateNextReviewTime(): ?Carbon
    {
        $interval = BinInterval::getInterval($this->bin);

        if ($interval === null || $interval === -1) {
            return null; // Never review
        }

        return Carbon::now()->addSeconds($interval);
    }

    /**
     * Move user flashcard to next bin (correct answer)
     */
    public function moveToNextBin(): void
    {
        if ($this->bin < 11) {
            $this->bin++;
        }
        $this->next_review_at = $this->calculateNextReviewTime();
        $this->save();
    }

    /**
     * Move user flashcard back to bin 1 (incorrect answer)
     */
    public function moveToBin1(): void
    {
        $this->bin = 1;
        $this->incorrect_count++;
        $this->next_review_at = $this->calculateNextReviewTime();

        // Check if should be moved to "hard to remember" bin
        if ($this->incorrect_count >= 10) {
            $this->is_hard_to_remember = true;
            $this->next_review_at = null;
        }

        $this->save();
    }

    /**
     * Scope for user flashcards ready for review
     */
    public function scopeReadyForReview($query)
    {
        return $query->where(function ($q) {
            $q->where('bin', 0) // New cards
              ->orWhere(function ($subQ) {
                  $subQ->where('bin', '>', 0)
                       ->where('next_review_at', '<=', Carbon::now())
                       ->where('is_hard_to_remember', false);
              });
        });
    }

    /**
     * Scope for user flashcards that are not hard to remember
     */
    public function scopeNotHardToRemember($query)
    {
        return $query->where('is_hard_to_remember', false);
    }

    /**
     * Scope for a specific user
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }
}
