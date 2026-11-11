<?php

namespace App\Filament\Resources\FlashcardReviewResource\Pages;

use App\Filament\Resources\FlashcardReviewResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFlashcardReviews extends ListRecords
{
    protected static string $resource = FlashcardReviewResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
