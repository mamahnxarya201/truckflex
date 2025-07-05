<?php

namespace App\Filament\Resources\RackLevelResource\Pages;

use App\Filament\Resources\RackLevelResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRackLevel extends EditRecord
{
    protected static string $resource = RackLevelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
