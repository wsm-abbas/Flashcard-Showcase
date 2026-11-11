<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Enums\BinInterval;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Flashcard extends Model
{
    use HasFactory;
    protected $fillable = [
        'word',
        'definition',
    ];

    protected $casts = [
        // No user-specific casts needed anymore
    ];

    /**
     * Get all user flashcard relationships for this flashcard
     */
    public function userFlashcards(): HasMany
    {
        return $this->hasMany(UserFlashcard::class);
    }

    /**
     * Get all users who have this flashcard
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_flashcards')
            ->withPivot(['bin', 'next_review_at', 'incorrect_count', 'is_hard_to_remember'])
            ->withTimestamps();
    }

    /**
     * Get the time intervals for each bin
     */
    public static function getBinIntervals(): array
    {
        return BinInterval::getAllIntervals();
    }
}
