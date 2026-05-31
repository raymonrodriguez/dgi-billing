<?php

namespace App\Filament\Resources\Ecfs\Pages;

use App\Filament\Resources\Ecfs\EcfResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Enums\Width;

class ViewEcf extends ViewRecord
{
    protected static string $resource = EcfResource::class;

    public function getMaxContentWidth(): Width | string | null
    {
        return Width::Full;
    }

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
