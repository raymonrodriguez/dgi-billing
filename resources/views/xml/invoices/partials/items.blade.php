    <DetallesItems>
        @foreach($items as $index => $item)
        @php
            $ecf = $item->ecf;
            $rate = (float) $ecf->exchange_rate;
            // Valores en DOP (Convertidos)
            $priceDop = $item->price * $rate;
            $subtotalDop = $item->subtotal * $rate;
            $discountDop = $item->discount * $rate;
        @endphp
        <Item>
            <NumeroLinea>{{ $index + 1 }}</NumeroLinea>
            <IndicadorFacturacion>{{ $item->billing_indicator->value }}</IndicadorFacturacion>
            <NombreItem><![CDATA[{{ $item->description }}]]></NombreItem>
            <CantidadItem>{{ number_format($item->quantity, 2, '.', '') }}</CantidadItem>
            <PrecioUnitarioItem>{{ number_format($priceDop, 2, '.', '') }}</PrecioUnitarioItem>
            <DescuentoMonto>{{ number_format($discountDop, 2, '.', '') }}</DescuentoMonto>
            <MontoItem>{{ number_format($subtotalDop, 2, '.', '') }}</MontoItem>
            
            @php $additionalTaxes = $item->getCalculatedAdditionalTaxes(); @endphp
            @if(!empty($additionalTaxes))
            <TablaImpuestoAdicional>
                @foreach($additionalTaxes as $tax)
                <ImpuestoAdicional>
                    <TipoImpuesto>{{ $tax['code'] }}</TipoImpuesto>
                    <TasaImpuestoAdicional>{{ number_format($tax['rate'], 2, '.', '') }}</TasaImpuestoAdicional>
                    <MontoImpuestoAdicional>{{ number_format($tax['amount'] * $rate, 2, '.', '') }}</MontoImpuestoAdicional>
                </ImpuestoAdicional>
                @endforeach
            </TablaImpuestoAdicional>
            @endif
        </Item>
        @endforeach
    </DetallesItems>
