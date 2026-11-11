<?php

namespace App\Filament\Resources\UserFlashcardResource\Pages;

use App\Filament\Resources\UserFlashcardResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditUserFlashcard extends EditRecord
{
    protected static string $resource = UserFlashcardResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
