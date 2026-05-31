<?php

namespace App\Filament\Resources\ReceivedEcfs\Pages;

use App\Filament\Resources\ReceivedEcfs\ReceivedEcfResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListReceivedEcfs extends ListRecords
{
    protected static string $resource = ReceivedEcfResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
