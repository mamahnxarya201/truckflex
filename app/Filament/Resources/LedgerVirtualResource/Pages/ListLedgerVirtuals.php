<?php

namespace App\Filament\Resources\LedgerVirtualResource\Pages;

use App\Filament\Resources\LedgerVirtualResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLedgerVirtuals extends ListRecords
{
    protected static string $resource = LedgerVirtualResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
