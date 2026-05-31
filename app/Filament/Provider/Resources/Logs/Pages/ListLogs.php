<?php

namespace App\Filament\Provider\Resources\Logs\Pages;

use App\Filament\Provider\Resources\Logs\LogResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListLogs extends ListRecords
{
    protected static string $resource = LogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
