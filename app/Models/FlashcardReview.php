<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FlashcardReview extends Model
{
    protected $fillable = [
        'user_flashcard_id',
        'is_correct',
        'bin_before',
        'bin_after',
        'reviewed_at',
    ];

    protected $casts = [
        'is_correct' => 'boolean',
        'reviewed_at' => 'datetime',
    ];

    /**
     * Get the user flashcard that was reviewed
     */
    public function userFlashcard(): BelongsTo
    {
        return $this->belongsTo(UserFlashcard::class);
    }
}
