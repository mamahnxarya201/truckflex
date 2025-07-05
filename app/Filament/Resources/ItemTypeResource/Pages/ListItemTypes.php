<?php

namespace App\Filament\Resources\ItemTypeResource\Pages;

use App\Filament\Resources\ItemTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListItemTypes extends ListRecords
{
    protected static string $resource = ItemTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
