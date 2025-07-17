<?php

namespace App\Filament\Resources\VehicleLogResource\Pages;

use App\Filament\Resources\VehicleLogResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListVehicleLogs extends ListRecords
{
    protected static string $resource = VehicleLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
