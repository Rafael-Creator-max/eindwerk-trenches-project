<?php

namespace App\Filament\Resources\CryptocurrencyResource\Pages;

use App\Filament\Resources\CryptocurrencyResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateCryptocurrency extends CreateRecord
{
    protected static string $resource = CryptocurrencyResource::class;
}
