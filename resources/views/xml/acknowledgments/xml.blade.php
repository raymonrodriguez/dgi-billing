<?xml version="1.0" encoding="UTF-8"?>
<ARECF xmlns="http://dgii.gov.do/arecf">
    <DetalleAcuseRecibo>
        <Version>1.0</Version>
        <RNCEmisor>{{ $rncEmisor }}</RNCEmisor>
        <RNCReceptor>{{ $rncReceptor }}</RNCReceptor>
        <eNCF>{{ $encf }}</eNCF>
        <Estado>{{ $estado ?? '0' }}</Estado>
        <FechaHoraAcuseRecibo>{{ now()->format('d-m-Y H:i:s') }}</FechaHoraAcuseRecibo>
    </DetalleAcuseRecibo>
</ARECF>
