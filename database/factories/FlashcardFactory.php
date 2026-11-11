<?php

namespace Database\Factories;

use App\Models\Flashcard;
use Illuminate\Database\Eloquent\Factories\Factory;

class FlashcardFactory extends Factory
{
    protected $model = Flashcard::class;

    public function definition()
    {
        return [
            'word' => $this->faker->word(),
            'definition' => $this->faker->sentence(),
        ];
    }
}
