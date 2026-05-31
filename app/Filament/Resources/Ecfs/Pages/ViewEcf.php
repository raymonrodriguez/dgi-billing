<?php

namespace App\Filament\Resources\Ecfs\Pages;

use App\Filament\Resources\Ecfs\EcfResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewEcf extends ViewRecord
{
    protected static string $resource = EcfResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
