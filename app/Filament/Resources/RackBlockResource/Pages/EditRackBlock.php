<?php

namespace App\Filament\Resources\RackBlockResource\Pages;

use App\Filament\Resources\RackBlockResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRackBlock extends EditRecord
{
    protected static string $resource = RackBlockResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
