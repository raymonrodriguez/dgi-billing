<?xml version="1.0" encoding="UTF-8"?>
<eCF xmlns="http://dgii.gov.do/empresa/e-cf">
    <Encabezado>
        <Version>1.0</Version>
        <IdDoc>
            <TipoeCF>34</TipoeCF>
            <eNCF>{{ $ecf->encf }}</eNCF>
            <FechaEmision>{{ $ecf->issued_at->format('d-m-Y') }}</FechaEmision>
            <IndicadorMontoGravado>1</IndicadorMontoGravado>
        </IdDoc>
        <Emisor>
            <RNCEmisor>{{ $ecf->company->tax_id }}</RNCEmisor>
            <RazonSocialEmisor><![CDATA[{{ $ecf->company->company_name }}]]></RazonSocialEmisor>
            <NombreComercial><![CDATA[{{ $ecf->company->trade_name ?? $ecf->company->company_name }}]]></NombreComercial>
            <DireccionEmisor><![CDATA[{{ $ecf->company->address ?? 'Santo Domingo, RD' }}]]></DireccionEmisor>
        </Emisor>
        <Receptor>
            <RNCReceptor>{{ $ecf->contact->tax_id }}</RNCReceptor>
            <RazonSocialReceptor><![CDATA[{{ $ecf->contact->name }}]]></RazonSocialReceptor>
        </Receptor>
        @include("xml.invoices.partials.totals", ["ecf" => $ecf])
    </Encabezado>
    @include("xml.invoices.partials.items", ["items" => $ecf->items])
    <InformacionReferencia>
        <Referencia>
            <NCFModificado>{{ $ecf->ncf_referencia ?? '' }}</NCFModificado>
            <CodigoModificacion>1</CodigoModificacion>
        </Referencia>
    </InformacionReferencia>
</eCF>
