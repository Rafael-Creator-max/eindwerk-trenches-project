<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CryptocurrencyResource\Pages;
use App\Filament\Resources\CryptocurrencyResource\RelationManagers;
use App\Models\Cryptocurrency;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Database\Eloquent\Model;
use Filament\Tables\Actions\Action;

class CryptocurrencyResource extends Resource
{
    protected static ?string $model = Cryptocurrency::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
            Tables\Columns\TextColumn::make('name')->sortable()->searchable(),
            Tables\Columns\TextColumn::make('symbol')->sortable(),
            Tables\Columns\TextColumn::make('current_price')->label('Price')->money('usd'),
            Tables\Columns\TextColumn::make('market_cap')->label('Market Cap')->numeric()->sortable(),
            Tables\Columns\TextColumn::make('volume_24h')->label('24h Volume')->numeric()->sortable(),
            Tables\Columns\TextColumn::make('price_change_24h')->label('24h Change')->numeric()->sortable(),
                
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Action::make('reload_cryptos')
                    ->label('Reload cryptos')
                    ->icon('heroicon-m-arrow-path')
                    ->url(url('/fetch-and-store-cryptos'))
                    ->openUrlInNewTab()
                    ->extraAttributes([
                        'onclick' => 'setTimeout(function(){ window.location.reload() }, 6000)'
                    ]),
            ])
            ->actions([])
            ->bulkActions([]);
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
            'index' => Pages\ListCryptocurrencies::route('/'),
            'create' => Pages\CreateCryptocurrency::route('/create'),
            'edit' => Pages\EditCryptocurrency::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }
}
