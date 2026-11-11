<?php

namespace App\Filament\Resources\UserFlashcardResource\Pages;

use App\Filament\Resources\UserFlashcardResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListUserFlashcards extends ListRecords
{
    protected static string $resource = UserFlashcardResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
