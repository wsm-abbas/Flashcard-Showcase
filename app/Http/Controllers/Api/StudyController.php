<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Flashcard;
use App\Models\User;
use App\Models\UserFlashcard;
use App\Services\FlashCardService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class StudyController extends Controller
{
    protected FlashCardService $flashCardService;

    public function __construct(FlashCardService $flashCardService)
    {
        $this->flashCardService = $flashCardService;
    }

    /**
     * Get the next flashcard to study
     */
    public function getNextCard(Request $request): JsonResponse
    {
        try {
            $userEmail = $request->input('user_email');
            if (!$userEmail) {
                return response()->json([
                    'error' => 'User email required',
                    'message' => 'Please provide a user_email.',
                ], 400);
            }

            $user = User::where('email', $userEmail)->firstOrFail();
            $userFlashcard = $this->flashCardService->getNextFlashcard($user);

            if (!$userFlashcard) {
                return response()->json([
                    'message' => $this->flashCardService->getStudyStatus($user),
                    'is_permanently_done' => $this->flashCardService->isPermanentlyDone($user),
                    'card' => null,
                ]);
            }

            return response()->json([
                'card' => [
                    'id' => $userFlashcard->id,
                    'word' => $userFlashcard->flashcard->word,
                    'definition' => $userFlashcard->flashcard->definition,
                    'bin' => $userFlashcard->bin,
                    'next_review_at' => $userFlashcard->next_review_at?->toISOString(),
                    'incorrect_count' => $userFlashcard->incorrect_count,
                    'is_hard_to_remember' => $userFlashcard->is_hard_to_remember,
                ],
                'message' => null,
                'is_permanently_done' => false,
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting next card', ['error' => $e->getMessage()]);
            return response()->json([
                'error' => 'Failed to retrieve next card',
                'message' => 'An error occurred while loading the next card. Please try again.',
            ], 500);
        }
    }

    /**
     * Submit a review result
     */
    public function submitReview(Request $request): JsonResponse
    {
        try {
            $userEmail = $request->input('user_email');
            if (!$userEmail) {
                return response()->json([
                    'error' => 'User email required',
                    'message' => 'Please provide a user_email.',
                ], 400);
            }

            $validated = $request->validate([
                'user_email' => 'required|email',
                'user_flashcard_id' => 'required|integer|exists:user_flashcards,id',
                'is_correct' => 'required|boolean',
            ]);

            $user = User::where('email', $userEmail)->firstOrFail();

            // Ensure the user flashcard belongs to the specified user
            $userFlashcard = UserFlashcard::where('id', $validated['user_flashcard_id'])
                ->where('user_id', $user->id)
                ->firstOrFail();

            $review = $this->flashCardService->processReview($userFlashcard, $validated['is_correct']);

            Log::info('Review submitted', [
                'user_email' => $validated['user_email'],
                'user_flashcard_id' => $userFlashcard->id,
                'is_correct' => $validated['is_correct'],
                'bin_before' => $review->bin_before,
                'bin_after' => $review->bin_after,
            ]);

            return response()->json([
                'success' => true,
                'review' => [
                    'id' => $review->id,
                    'is_correct' => $review->is_correct,
                    'bin_before' => $review->bin_before,
                    'bin_after' => $review->bin_after,
                    'reviewed_at' => $review->reviewed_at->toISOString(),
                ],
                'user_flashcard' => [
                    'id' => $userFlashcard->id,
                    'bin' => $userFlashcard->bin,
                    'next_review_at' => $userFlashcard->next_review_at?->toISOString(),
                    'incorrect_count' => $userFlashcard->incorrect_count,
                    'is_hard_to_remember' => $userFlashcard->is_hard_to_remember,
                ],
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'error' => 'Validation failed',
                'message' => 'Invalid input data',
                'errors' => $e->errors(),
            ], 422);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error' => 'User flashcard not found',
                'message' => 'The requested flashcard could not be found.',
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error submitting review', [
                'error' => $e->getMessage(),
                'user_flashcard_id' => $request->input('user_flashcard_id'),
                'is_correct' => $request->input('is_correct'),
            ]);
            return response()->json([
                'error' => 'Failed to submit review',
                'message' => 'An error occurred while processing your review. Please try again.',
            ], 500);
        }
    }

    /**
     * Get study statistics
     */
    public function getStatistics(Request $request): JsonResponse
    {
        try {
            $userEmail = $request->input('user_email');
            if (!$userEmail) {
                return response()->json([
                    'error' => 'User email required',
                    'message' => 'Please provide a user_email.',
                ], 400);
            }

            $user = User::where('email', $userEmail)->firstOrFail();
            $stats = $this->flashCardService->getStatistics($user);

            return response()->json([
                'statistics' => $stats,
                'study_status' => $this->flashCardService->getStudyStatus($user),
                'is_permanently_done' => $this->flashCardService->isPermanentlyDone($user),
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting statistics', ['error' => $e->getMessage()]);
            return response()->json([
                'error' => 'Failed to retrieve statistics',
                'message' => 'An error occurred while loading statistics. Please try again.',
            ], 500);
        }
    }

    /**
     * Get study status message
     */
    public function getStudyStatus(Request $request): JsonResponse
    {
        try {
            $userEmail = $request->input('user_email');
            if (!$userEmail) {
                return response()->json([
                    'error' => 'User email required',
                    'message' => 'Please provide a user_email.',
                ], 400);
            }

            $user = User::where('email', $userEmail)->firstOrFail();
            return response()->json([
                'message' => $this->flashCardService->getStudyStatus($user),
                'is_permanently_done' => $this->flashCardService->isPermanentlyDone($user),
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting study status', ['error' => $e->getMessage()]);
            return response()->json([
                'error' => 'Failed to retrieve study status',
                'message' => 'An error occurred while checking study status. Please try again.',
            ], 500);
        }
    }

    /**
     * Initialize study session (create user if needed and set up flashcards)
     */
    public function initializeStudy(Request $request): JsonResponse
    {
        try {
            $userEmail = $request->input('user_email');
            if (!$userEmail) {
                return response()->json([
                    'error' => 'User email required',
                    'message' => 'Please provide a user_email.',
                ], 400);
            }

            // Find or create user with better error handling
            $user = User::firstOrCreate(
                ['email' => $userEmail],
                [
                    'name' => 'Student ' . substr($userEmail, 0, 8),
                    'password' => bcrypt('dummy_password')
                ]
            );

            // Initialize flashcards for the user (handles duplicates gracefully)
            $createdCount = $this->flashCardService->initializeStudy($user);

            return response()->json([
                'success' => true,
                'message' => "Study session ready with {$createdCount} flashcards.",
                'created_count' => $createdCount,
                'user' => [
                    'name' => $user->name,
                    'email' => $user->email,
                ]
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            // Handle database constraint violations (duplicates)
            if ($e->getCode() == 23000) { // Integrity constraint violation
                Log::info('Duplicate initialization attempt', ['user_email' => $userEmail]);
                return response()->json([
                    'success' => true,
                    'message' => 'Study session already initialized.',
                    'created_count' => 0,
                    'user' => [
                        'name' => 'Student ' . substr($userEmail, 0, 8),
                        'email' => $userEmail,
                    ]
                ]);
            }
            throw $e; // Re-throw if it's not a duplicate constraint error
        } catch (\Exception $e) {
            Log::error('Error initializing study', ['error' => $e->getMessage(), 'user_email' => $userEmail]);
            return response()->json([
                'error' => 'Failed to initialize study',
                'message' => 'An error occurred while setting up your study session. Please try again.',
            ], 500);
        }
    }
}
