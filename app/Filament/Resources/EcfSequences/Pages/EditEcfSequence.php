<?php

namespace App\Filament\Resources\EcfSequences\Pages;

use App\Filament\Resources\EcfSequences\EcfSequenceResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditEcfSequence extends EditRecord
{
    protected static string $resource = EcfSequenceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
