<?php

namespace App\Filament\Resources\CryptocurrencyResource\Pages;

use App\Filament\Resources\CryptocurrencyResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCryptocurrencies extends ListRecords
{
    protected static string $resource = CryptocurrencyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
