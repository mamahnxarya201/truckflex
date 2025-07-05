<?php

namespace App\Filament\Resources\RackLevelResource\Pages;

use App\Filament\Resources\RackLevelResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRackLevels extends ListRecords
{
    protected static string $resource = RackLevelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
