<?php

namespace App\Filament\Resources\VehicleLogResource\Pages;

use App\Filament\Resources\VehicleLogResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateVehicleLog extends CreateRecord
{
    protected static string $resource = VehicleLogResource::class;
    
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
