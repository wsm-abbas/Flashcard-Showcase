<?php

namespace Database\Factories;

use App\Models\UserFlashcard;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserFlashcardFactory extends Factory
{
    protected $model = UserFlashcard::class;

    public function definition()
    {
        return [
            'user_id' => \App\Models\User::factory(),
            'flashcard_id' => \App\Models\Flashcard::factory(),
            'bin' => 0,
            'incorrect_count' => 0,
            'is_hard_to_remember' => false,
        ];
    }
}
