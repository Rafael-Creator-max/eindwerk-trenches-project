<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CryptocurrenciesRelationManager extends RelationManager
{
    protected static string $relationship = 'cryptocurrencies';
    protected static ?string $inverseRelationship = 'followers';

    protected static ?string $title = 'Followed Cryptocurrencies';

    public function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('symbol')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('name')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('slug')
                    ->sortable()
                    ->searchable(),
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    ->label('Follow')
                    ->recordTitleAttribute('name')
                    ->recordSelectSearchColumns(['name', 'symbol', 'slug'])
                    ->preloadRecordSelect()
                    ->recordSelectOptionsQuery(fn (Builder $query) => $query->orderBy('market_cap', 'desc')),
            ])
            ->actions([
                Tables\Actions\DetachAction::make()
                    ->label('Unfollow'),
            ])
            ->bulkActions([
                Tables\Actions\DetachBulkAction::make()
                    ->label('Unfollow selected'),
            ]);
    }
}
