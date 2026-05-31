<?xml version="1.0" encoding="UTF-8"?>
<AprobacionComercial xmlns="http://dgii.gov.do/RutinaAprobacionComercial">
    <Encabezado>
        <RNCEmisor>{{ $rncEmisor }}</RNCEmisor>
        <RNCReceptor>{{ $rncReceptor }}</RNCReceptor>
        <eNCF>{{ $encf }}</eNCF>
        <FechaAprobacion>{{ now()->format('d-m-Y H:i:s') }}</FechaAprobacion>
        <ResultadoAprobacion>{{ $status }}</ResultadoAprobacion>
    </Encabezado>
</AprobacionComercial>
