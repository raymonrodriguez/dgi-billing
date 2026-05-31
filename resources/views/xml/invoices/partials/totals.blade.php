        <Totales>
            <MontoTotal>{{ number_format($ecf->toDop((float) $ecf->total_amount), 2, '.', '') }}</MontoTotal>
            <TotalITBIS>{{ number_format($ecf->toDop((float) $ecf->total_tax), 2, '.', '') }}</TotalITBIS>
            <TotalITBIS18>{{ number_format($ecf->toDop((float) $ecf->total_tax), 2, '.', '') }}</TotalITBIS18>
            
            @if($ecf->isForeignCurrency())
            <TipoMoneda>{{ $ecf->currency->value }}</TipoMoneda>
            <TipoCambio>{{ number_format((float) $ecf->exchange_rate, 4, '.', '') }}</TipoCambio>
            
            <MontoTotalOtraMoneda>{{ number_format((float) $ecf->total_amount, 2, '.', '') }}</MontoTotalOtraMoneda>
            <TotalITBISOtraMoneda>{{ number_format((float) $ecf->total_tax, 2, '.', '') }}</TotalITBISOtraMoneda>
            @endif
        </Totales>
