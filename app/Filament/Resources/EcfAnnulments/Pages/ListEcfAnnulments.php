<?php

namespace App\Filament\Resources\EcfAnnulments\Pages;

use App\Filament\Resources\EcfAnnulments\EcfAnnulmentResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListEcfAnnulments extends ListRecords
{
    protected static string $resource = EcfAnnulmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
