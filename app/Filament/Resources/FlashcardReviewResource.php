<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FlashcardReviewResource\Pages;
use App\Filament\Resources\FlashcardReviewResource\RelationManagers;
use App\Models\FlashcardReview;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class FlashcardReviewResource extends Resource
{
    protected static ?string $model = FlashcardReview::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = 'Review History';

    protected static ?string $modelLabel = 'Flashcard Review';

    protected static ?string $pluralModelLabel = 'Flashcard Reviews';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_flashcard_id')
                    ->relationship('userFlashcard', 'id')
                    ->getOptionLabelFromRecordUsing(fn ($record) => "User: {$record->user->name} | Card: {$record->flashcard->word}")
                    ->required()
                    ->searchable(),
                Forms\Components\Toggle::make('is_correct')
                    ->default(true),
                Forms\Components\TextInput::make('bin_before')
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('bin_after')
                    ->numeric()
                    ->default(0),
                Forms\Components\DateTimePicker::make('reviewed_at')
                    ->default(now()),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('userFlashcard.user.name')
                    ->label('User')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('userFlashcard.flashcard.word')
                    ->label('Flashcard')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('userFlashcard.flashcard.definition')
                    ->label('Definition')
                    ->limit(30)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 30) {
                            return null;
                        }
                        return $state;
                    }),
                Tables\Columns\IconColumn::make('is_correct')
                    ->label('Correct')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
                Tables\Columns\TextColumn::make('bin_before')
                    ->label('Bin Before')
                    ->badge()
                    ->color('gray'),
                Tables\Columns\TextColumn::make('bin_after')
                    ->label('Bin After')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        '0' => 'gray',
                        '1', '2', '3' => 'red',
                        '4', '5', '6' => 'yellow',
                        '7', '8', '9' => 'blue',
                        '10', '11' => 'green',
                    }),
                Tables\Columns\TextColumn::make('reviewed_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('user')
                    ->relationship('userFlashcard.user', 'name')
                    ->searchable(),
                Tables\Filters\TernaryFilter::make('is_correct')
                    ->label('Correct Answer'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('reviewed_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFlashcardReviews::route('/'),
            'create' => Pages\CreateFlashcardReview::route('/create'),
            'edit' => Pages\EditFlashcardReview::route('/{record}/edit'),
        ];
    }
}
