<?php

namespace App\Filament\Resources\LedgerVirtualResource\Pages;

use App\Filament\Resources\LedgerVirtualResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLedgerVirtual extends EditRecord
{
    protected static string $resource = LedgerVirtualResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
