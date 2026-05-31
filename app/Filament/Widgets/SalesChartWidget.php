<?php

namespace App\Filament\Widgets;

use App\Models\Ecf;
use Filament\Facades\Filament;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class SalesChartWidget extends ChartWidget
{
    protected ?string $heading = 'Ventas Diarias (Últimos 7 días)';
    
    protected ?string $maxHeight = '250px';

    protected function getData(): array
    {
        $tenant = Filament::getTenant();

        if (!$tenant) {
            return [];
        }

        $data = [];
        $labels = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $labels[] = $date->format('d M');
            
            $sum = $tenant->ecfs()
                ->whereDate('issued_at', $date->toDateString())
                ->whereIn('dgii_status', ['Aceptado', 'Aceptado Condicional'])
                ->sum('total_amount');
                
            $data[] = (float) $sum;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Ventas (RD$)',
                    'data' => $data,
                    'borderColor' => '#f59e0b',
                    'fill' => 'start',
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
