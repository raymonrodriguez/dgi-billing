<?xml version="1.0" encoding="UTF-8"?>
<RFCE xmlns="http://dgii.gov.do/empresa/rfce">
    <Encabezado>
        <Version>1.0</Version>
        <IdDoc>
            <eNCF>{{ $ecf->encf }}</eNCF>
            <FechaEmision>{{ $ecf->issued_at->format('d-m-Y') }}</FechaEmision>
        </IdDoc>
        <Emisor>
            <RNCEmisor>{{ $ecf->company->tax_id }}</RNCEmisor>
        </Emisor>
        <Comprador>
            @if($ecf->contact->tax_id && $ecf->contact->tax_id !== '00000000000')
            <RNCComprador>{{ $ecf->contact->tax_id }}</RNCComprador>
            @endif
        </Comprador>
        <Totales>
            <MontoTotal>{{ number_format($ecf->total_amount, 2, '.', '') }}</MontoTotal>
            <TotalITBIS>{{ number_format($ecf->total_tax, 2, '.', '') }}</TotalITBIS>
        </Totales>
    </Encabezado>
</RFCE>
