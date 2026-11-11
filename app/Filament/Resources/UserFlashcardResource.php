<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserFlashcardResource\Pages;
use App\Filament\Resources\UserFlashcardResource\RelationManagers;
use App\Models\UserFlashcard;
use App\Enums\BinInterval;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UserFlashcardResource extends Resource
{
    protected static ?string $model = UserFlashcard::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationLabel = 'User Progress';

    protected static ?string $modelLabel = 'User Flashcard Progress';

    protected static ?string $pluralModelLabel = 'User Flashcard Progress';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required()
                    ->searchable(),
                Forms\Components\Select::make('flashcard_id')
                    ->relationship('flashcard', 'word')
                    ->required()
                    ->searchable(),
                Forms\Components\Select::make('bin')
                    ->options(function () {
                        $options = [];
                        for ($i = 0; $i <= 11; $i++) {
                            $options[$i] = BinInterval::getBinName($i);
                        }
                        return $options;
                    })
                    ->default(0),
                Forms\Components\DateTimePicker::make('next_review_at'),
                Forms\Components\TextInput::make('incorrect_count')
                    ->numeric()
                    ->default(0),
                Forms\Components\Toggle::make('is_hard_to_remember')
                    ->default(false),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('flashcard.word')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('flashcard.definition')
                    ->limit(30)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 30) {
                            return null;
                        }
                        return $state;
                    }),
                Tables\Columns\TextColumn::make('bin')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        '0' => 'gray',
                        '1', '2', '3' => 'red',
                        '4', '5', '6' => 'yellow',
                        '7', '8', '9' => 'blue',
                        '10', '11' => 'green',
                    }),
                Tables\Columns\TextColumn::make('next_review_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('incorrect_count')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_hard_to_remember')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('user')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload()
                    ->multiple()
                    ->placeholder('Select users to filter...'),
                Tables\Filters\SelectFilter::make('bin')
                    ->options(function () {
                        $options = [];
                        for ($i = 0; $i <= 11; $i++) {
                            $options[$i] = BinInterval::getBinName($i);
                        }
                        return $options;
                    })
                    ->multiple()
                    ->placeholder('Select bins to filter...'),
                Tables\Filters\TernaryFilter::make('is_hard_to_remember')
                    ->label('Hard to Remember'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
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
            'index' => Pages\ListUserFlashcards::route('/'),
            'create' => Pages\CreateUserFlashcard::route('/create'),
            'edit' => Pages\EditUserFlashcard::route('/{record}/edit'),
        ];
    }
}
