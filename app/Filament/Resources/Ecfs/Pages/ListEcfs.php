<?php

namespace App\Filament\Resources\Ecfs\Pages;

use App\Filament\Resources\Ecfs\EcfResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListEcfs extends ListRecords
{
    protected static string $resource = EcfResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'Todas' => Tab::make(),
            'Aceptadas' => Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query->whereIn('dgii_status', ['Aceptado', 'Aceptado Condicional'])),
            'En Proceso' => Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query->where('dgii_status', 'En Proceso')),
            'Rechazadas' => Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query->where('dgii_status', 'Rechazado')),
        ];
    }
}
