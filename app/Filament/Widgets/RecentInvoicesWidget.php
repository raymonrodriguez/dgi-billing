<?php

namespace App\Filament\Widgets;

use App\Enums\EcfStatus;
use App\Models\Ecf;
use Filament\Facades\Filament;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseTableWidget;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\ViewAction;

class RecentInvoicesWidget extends BaseTableWidget
{
    protected static ?string $heading = 'Últimas Facturas Emitidas';

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        $tenant = Filament::getTenant();

        return $table
            ->query(
                Ecf::query()
                    ->where('company_id', $tenant?->id)
                    ->latest()
                    ->limit(5)
            )
            ->paginated(false)
            ->columns([
                TextColumn::make('encf')
                    ->label('e-NCF')
                    ->searchable(),
                TextColumn::make('contact.name')
                    ->label('Cliente'),
                TextColumn::make('total_amount')
                    ->label('Total')
                    ->money('DOP'),
                TextColumn::make('dgii_status')
                    ->label('Estatus')
                    ->badge()
                    ->color(fn (EcfStatus $state): string => match ($state) {
                        EcfStatus::ACEPTADO => 'success',
                        EcfStatus::RECHAZADO => 'danger',
                        EcfStatus::ACEPTADO_CONDICIONAL, EcfStatus::EN_PROCESO => 'warning',
                        default => 'gray',
                    }),
                TextColumn::make('issued_at')
                    ->label('Fecha')
                    ->date('d/m/Y'),
            ])
            ->actions([
                ViewAction::make()
                    ->url(fn (Ecf $record): string => "/admin/{$tenant->id}/ecfs/{$record->id}"),
            ]);
    }
}
