<?php

namespace App\Filament\Resources\FlashcardReviewResource\Pages;

use App\Filament\Resources\FlashcardReviewResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditFlashcardReview extends EditRecord
{
    protected static string $resource = FlashcardReviewResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
