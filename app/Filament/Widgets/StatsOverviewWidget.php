<?php

namespace App\Filament\Widgets;

use Filament\Facades\Filament;
use Filament\Widgets\StatsOverviewWidget as BaseStatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverviewWidget extends BaseStatsOverviewWidget
{
    protected function getStats(): array
    {
        $tenant = Filament::getTenant();

        if (!$tenant) {
            return [];
        }

        $totalMonth = $tenant->ecfs()
            ->whereIn('dgii_status', ['Aceptado', 'Aceptado Condicional'])
            ->whereMonth('issued_at', now()->month)
            ->whereYear('issued_at', now()->year)
            ->sum('total_amount');

        $inProcess = $tenant->ecfs()
            ->where('dgii_status', 'En Proceso')
            ->count();

        $rejected = $tenant->ecfs()
            ->where('dgii_status', 'Rechazado')
            ->count();

        return [
            Stat::make('Facturado Mes', 'RD$ ' . number_format($totalMonth, 2))
                ->description('Total aceptado por la DGII')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),

            Stat::make('En Proceso', $inProcess)
                ->description('Pendientes de respuesta')
                ->descriptionIcon('heroicon-m-arrow-path')
                ->color('warning'),

            Stat::make('Rechazadas', $rejected)
                ->description('Comprobantes con errores')
                ->descriptionIcon('heroicon-m-x-circle')
                ->color('danger'),
        ];
    }
}
