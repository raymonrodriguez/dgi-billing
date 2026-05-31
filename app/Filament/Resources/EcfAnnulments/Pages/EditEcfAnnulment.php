<?php

namespace App\Filament\Resources\EcfAnnulments\Pages;

use App\Filament\Resources\EcfAnnulments\EcfAnnulmentResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditEcfAnnulment extends EditRecord
{
    protected static string $resource = EcfAnnulmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
