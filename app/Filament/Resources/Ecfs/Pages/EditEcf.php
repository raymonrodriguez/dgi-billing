<?php

namespace App\Filament\Resources\Ecfs\Pages;

use App\Filament\Resources\Ecfs\EcfResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Enums\Width;

class EditEcf extends EditRecord
{
    protected static string $resource = EcfResource::class;

    public function getMaxContentWidth(): Width | string | null
    {
        return Width::Full;
    }

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
