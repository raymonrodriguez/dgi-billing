<?xml version="1.0" encoding="UTF-8"?>
<eCF xmlns="http://dgii.gov.do/empresa/e-cf">
    <Encabezado>
        <Version>1.0</Version>
        <IdDoc>
            <TipoeCF>31</TipoeCF>
            <eNCF>{{ $ecf->encf }}</eNCF>
            <FechaEmision>{{ $ecf->issued_at->format('d-m-Y') }}</FechaEmision>
            <IndicadorMontoGravado>1</IndicadorMontoGravado>
            <TipoIngresos>01</TipoIngresos>
        </IdDoc>
        <Emisor>
            <RNCEmisor>{{ $ecf->company->tax_id }}</RNCEmisor>
            <RazonSocialEmisor><![CDATA[{{ $ecf->company->company_name }}]]></RazonSocialEmisor>
            <NombreComercial><![CDATA[{{ $ecf->company->trade_name ?? $ecf->company->company_name }}]]></NombreComercial>
            <DireccionEmisor><![CDATA[{{ $ecf->company->address ?? 'Santo Domingo, RD' }}]]></DireccionEmisor>
            <FechaInicioOperacion>01-01-2020</FechaInicioOperacion>
        </Emisor>
        <Receptor>
            <RNCReceptor>{{ $ecf->contact->tax_id }}</RNCReceptor>
            <RazonSocialReceptor><![CDATA[{{ $ecf->contact->name }}]]></RazonSocialReceptor>
            <DireccionReceptor><![CDATA[{{ $ecf->contact->address ?? 'N/A' }}]]></DireccionReceptor>
        </Receptor>
        <Totales>
            <MontoTotal>{{ number_format($ecf->total_amount, 2, '.', '') }}</MontoTotal>
            <TotalITBIS>{{ number_format($ecf->total_tax, 2, '.', '') }}</TotalITBIS>
            <TotalITBIS18>{{ number_format($ecf->total_tax, 2, '.', '') }}</TotalITBIS18>
            <TotalITBISRetenido>0.00</TotalITBISRetenido>
            <TotalISRRetenido>0.00</TotalISRRetenido>
        </Totales>
    </Encabezado>
    <DetallesItems>
        @foreach($ecf->items as $index => $item)
        <Item>
            <NumeroLinea>{{ $index + 1 }}</NumeroLinea>
            <IndicadorFacturacion>{{ $item->billing_indicator->value }}</IndicadorFacturacion>
            <NombreItem><![CDATA[{{ $item->description }}]]></NombreItem>
            <CantidadItem>{{ number_format($item->quantity, 2, '.', '') }}</CantidadItem>
            <PrecioUnitarioItem>{{ number_format($item->price, 2, '.', '') }}</PrecioUnitarioItem>
            <DescuentoMonto>{{ number_format($item->discount, 2, '.', '') }}</DescuentoMonto>
            <MontoItem>{{ number_format($item->subtotal, 2, '.', '') }}</MontoItem>
        </Item>
        @endforeach
    </DetallesItems>
</eCF>
