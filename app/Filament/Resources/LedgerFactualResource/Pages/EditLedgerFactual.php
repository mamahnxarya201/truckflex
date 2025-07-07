<?php

namespace App\Filament\Resources\LedgerFactualResource\Pages;

use App\Filament\Resources\LedgerFactualResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLedgerFactual extends EditRecord
{
    protected static string $resource = LedgerFactualResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
