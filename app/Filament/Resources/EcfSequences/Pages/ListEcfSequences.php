<?php

namespace App\Filament\Resources\EcfSequences\Pages;

use App\Filament\Resources\EcfSequences\EcfSequenceResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListEcfSequences extends ListRecords
{
    protected static string $resource = EcfSequenceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
