<?php

namespace App\Services;

use App\Models\Flashcard;
use App\Models\UserFlashcard;
use App\Models\FlashcardReview;
use App\Models\User;
use Carbon\Carbon;

class FlashCardService
{
    /**
     * Get the next flashcard to review for a specific user
     */
    public function getNextFlashcard(User $user): ?UserFlashcard
    {
        // First, try to get cards that are ready for review (bin 1+)
        $readyCards = UserFlashcard::forUser($user->id)
            ->readyForReview()
            ->notHardToRemember()
            ->where('bin', '>', 0)
            ->with('flashcard')
            ->orderBy('bin', 'desc') // Higher bins first
            ->orderBy('next_review_at', 'asc') // Earlier times first
            ->first();

        if ($readyCards) {
            return $readyCards;
        }

        // If no cards are ready, get new cards from bin 0
        $newCard = UserFlashcard::forUser($user->id)
            ->where('bin', 0)
            ->notHardToRemember()
            ->with('flashcard')
            ->first();

        return $newCard;
    }

    /**
     * Process a flashcard review for a specific user
     */
    public function processReview(UserFlashcard $userFlashcard, bool $isCorrect): FlashcardReview
    {
        $binBefore = $userFlashcard->bin;

        if ($isCorrect) {
            $userFlashcard->moveToNextBin();
        } else {
            $userFlashcard->moveToBin1();
        }

        $binAfter = $userFlashcard->bin;

        // Create review record
        return FlashcardReview::create([
            'user_flashcard_id' => $userFlashcard->id,
            'is_correct' => $isCorrect,
            'bin_before' => $binBefore,
            'bin_after' => $binAfter,
            'reviewed_at' => Carbon::now(),
        ]);
    }

    /**
     * Get study status message for a specific user
     */
    public function getStudyStatus(User $user): string
    {
        $readyCards = UserFlashcard::forUser($user->id)
            ->readyForReview()
            ->notHardToRemember()
            ->where('bin', '>', 0)
            ->count();

        $newCards = UserFlashcard::forUser($user->id)
            ->where('bin', 0)
            ->notHardToRemember()
            ->count();

        $hardToRemember = UserFlashcard::forUser($user->id)
            ->where('is_hard_to_remember', true)
            ->count();

        $masteredCards = UserFlashcard::forUser($user->id)
            ->where('bin', 11)
            ->count();

        if ($readyCards > 0 || $newCards > 0) {
            return "You have {$readyCards} cards ready for review and {$newCards} new cards.";
        }

        if ($hardToRemember > 0 && $masteredCards > 0) {
            return "You have no more words to review; you are permanently done!";
        }

        return "You are temporarily done; please come back later to review more words.";
    }

    /**
     * Check if user is permanently done
     */
    public function isPermanentlyDone(User $user): bool
    {
        $totalCards = UserFlashcard::forUser($user->id)->count();
        $hardToRemember = UserFlashcard::forUser($user->id)
            ->where('is_hard_to_remember', true)
            ->count();
        $masteredCards = UserFlashcard::forUser($user->id)
            ->where('bin', 11)
            ->count();

        return $totalCards > 0 && ($hardToRemember + $masteredCards) === $totalCards;
    }

    /**
     * Get statistics for admin interface for a specific user
     */
    public function getStatistics(User $user): array
    {
        $totalCards = UserFlashcard::forUser($user->id)->count();
        $newCards = UserFlashcard::forUser($user->id)->where('bin', 0)->count();
        $readyForReview = UserFlashcard::forUser($user->id)
            ->readyForReview()
            ->notHardToRemember()
            ->count();
        $hardToRemember = UserFlashcard::forUser($user->id)
            ->where('is_hard_to_remember', true)
            ->count();
        $masteredCards = UserFlashcard::forUser($user->id)
            ->where('bin', 11)
            ->count();

        $binDistribution = [];
        for ($i = 0; $i <= 11; $i++) {
            $binDistribution[$i] = UserFlashcard::forUser($user->id)
                ->where('bin', $i)
                ->count();
        }

        return [
            'total_cards' => $totalCards,
            'new_cards' => $newCards,
            'ready_for_review' => $readyForReview,
            'hard_to_remember' => $hardToRemember,
            'mastered_cards' => $masteredCards,
            'bin_distribution' => $binDistribution,
        ];
    }

    /**
     * Ensure a user has a UserFlashcard record for a given flashcard
     */
    public function ensureUserFlashcard(User $user, Flashcard $flashcard): UserFlashcard
    {
        return UserFlashcard::firstOrCreate(
            [
                'user_id' => $user->id,
                'flashcard_id' => $flashcard->id,
            ],
            [
                'bin' => 0,
                'incorrect_count' => 0,
                'is_hard_to_remember' => false,
            ]
        );
    }

    /**
     * Initialize study for a user by creating UserFlashcard records for all flashcards
     */
    public function initializeStudy(User $user): int
    {
        // Use a more efficient approach with upsert to prevent race conditions
        $allFlashcards = Flashcard::all();
        $userFlashcardData = [];

        foreach ($allFlashcards as $flashcard) {
            $userFlashcardData[] = [
                'user_id' => $user->id,
                'flashcard_id' => $flashcard->id,
                'bin' => 0,
                'incorrect_count' => 0,
                'is_hard_to_remember' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Use upsert to handle duplicates gracefully
        UserFlashcard::upsert(
            $userFlashcardData,
            ['user_id', 'flashcard_id'], // Unique keys
            ['bin', 'incorrect_count', 'is_hard_to_remember', 'updated_at'] // Update these fields if record exists
        );

        return count($userFlashcardData);
    }
}
