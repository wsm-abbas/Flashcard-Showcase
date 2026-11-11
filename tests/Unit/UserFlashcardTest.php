<?php

namespace Tests\Unit;

use App\Models\UserFlashcard;
use App\Models\User;
use App\Models\Flashcard;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserFlashcardTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Flashcard $flashcard;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->flashcard = Flashcard::factory()->create();
    }

    /**
     * Test that cards scheduled for months/weeks later are correctly calculated
     * This is the critical edge case that cannot be tested manually
     */
    public function test_long_term_scheduling(): void
    {
        // Test bin 9 (25 days) - card should show up after 25 days
        $userFlashcard = UserFlashcard::factory()->create([
            'user_id' => $this->user->id,
            'flashcard_id' => $this->flashcard->id,
            'bin' => 9,
        ]);

        $nextReview = $userFlashcard->calculateNextReviewTime();
        $this->assertNotNull($nextReview);

        // Should be approximately 25 days from now
        $actualDays = Carbon::now()->diffInDays($nextReview);
        $this->assertGreaterThanOrEqual(24, $actualDays);
        $this->assertLessThanOrEqual(26, $actualDays);

        // Test bin 10 (4 months) - card should show up after 120 days
        $userFlashcard->bin = 10;
        $nextReview = $userFlashcard->calculateNextReviewTime();
        $this->assertNotNull($nextReview);

        $actualDays = Carbon::now()->diffInDays($nextReview);
        $this->assertGreaterThanOrEqual(119, $actualDays);
        $this->assertLessThanOrEqual(121, $actualDays);
    }

    /**
     * Test that mastered cards (bin 11) never show up again
     */
    public function test_mastered_cards_never_show(): void
    {
        $userFlashcard = UserFlashcard::factory()->create([
            'user_id' => $this->user->id,
            'flashcard_id' => $this->flashcard->id,
            'bin' => 11,
        ]);

        $nextReview = $userFlashcard->calculateNextReviewTime();
        $this->assertNull($nextReview);
    }
}
