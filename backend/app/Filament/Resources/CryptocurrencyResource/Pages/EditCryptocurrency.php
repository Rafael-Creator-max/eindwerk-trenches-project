<?php

namespace App\Filament\Resources\CryptocurrencyResource\Pages;

use App\Filament\Resources\CryptocurrencyResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCryptocurrency extends EditRecord
{
    protected static string $resource = CryptocurrencyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
