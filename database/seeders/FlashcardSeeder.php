<?php

namespace Database\Seeders;

use App\Models\Flashcard;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FlashcardSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $flashcards = [
            [
                'word' => 'Luck',
                'definition' => 'The chance that brings good or bad results.',
            ],
            [
                'word' => 'Brief',
                'definition' => 'Lasting for only a short time.',
            ],
            [
                'word' => 'Common',
                'definition' => 'Happening often or found everywhere.',
            ],
            [
                'word' => 'Pleasant',
                'definition' => 'Giving a feeling of happiness or enjoyment.',
            ],
            [
                'word' => 'Fresh',
                'definition' => 'Clean, new, or like after rain.',
            ],
            [
                'word' => 'Journey',
                'definition' => 'The act of traveling from one place to another.',
            ],
            [
                'word' => 'Memory',
                'definition' => 'Something remembered from the past.',
            ],
            [
                'word' => 'Strong',
                'definition' => 'Able to recover quickly from difficulty.',
            ],
            [
                'word' => 'Clear',
                'definition' => 'Easy to understand or express.',
            ],
            [
                'word' => 'Effort',
                'definition' => 'Hard work to achieve something.',
            ],
        ];

        foreach ($flashcards as $flashcard) {
            Flashcard::create($flashcard);
        }
    }
}
