<?php

namespace Tests\Feature;

use App\Models\Flashcard;
use App\Models\User;
use App\Models\UserFlashcard;
use App\Services\FlashCardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FlashCardServiceTest extends TestCase
{
    use RefreshDatabase;

    protected FlashCardService $service;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new FlashCardService();
        $this->user = User::factory()->create();
    }

    /**
     * Test that cards progress through long intervals correctly
     * This tests the actual user experience of cards showing up after months
     */
    public function test_long_term_card_progression(): void
    {
        $flashcard = Flashcard::factory()->create();

        // Start at bin 9 (25 days)
        $userFlashcard = UserFlashcard::factory()->create([
            'user_id' => $this->user->id,
            'flashcard_id' => $flashcard->id,
            'bin' => 9,
        ]);

        // Correct answer should move to bin 10 (4 months)
        $review = $this->service->processReview($userFlashcard, true);
        $this->assertTrue($review->is_correct);
        $this->assertEquals(10, $userFlashcard->fresh()->bin);

        // Another correct answer should move to bin 11 (mastered)
        $userFlashcard->bin = 10;
        $userFlashcard->save();
        $review = $this->service->processReview($userFlashcard, true);
        $this->assertTrue($review->is_correct);
        $this->assertEquals(11, $userFlashcard->fresh()->bin);
    }
}
