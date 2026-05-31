<?php

namespace App\Filament\Resources\ReceivedEcfs\Pages;

use App\Filament\Resources\ReceivedEcfs\ReceivedEcfResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditReceivedEcf extends EditRecord
{
    protected static string $resource = ReceivedEcfResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
