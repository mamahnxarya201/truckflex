<?php

namespace App\Filament\Resources\LedgerFactualResource\Pages;

use App\Filament\Resources\LedgerFactualResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLedgerFactuals extends ListRecords
{
    protected static string $resource = LedgerFactualResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
