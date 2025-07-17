<?php

namespace App\Filament\Resources\VehicleLogResource\Pages;

use App\Filament\Resources\VehicleLogResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditVehicleLog extends EditRecord
{
    protected static string $resource = VehicleLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()->visible(function () {
                /** @var \App\Models\User $user */
                $user = auth()->user();
                
                // Only admins and managers can delete
                return !$user->hasRole('driver');
            }),
        ];
    }
    
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
