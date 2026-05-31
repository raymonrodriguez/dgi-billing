<?php

namespace App\Filament\Resources\Contingencies\Pages;

use App\Filament\Resources\Contingencies\ContingencyResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListContingencies extends ListRecords
{
    protected static string $resource = ContingencyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
