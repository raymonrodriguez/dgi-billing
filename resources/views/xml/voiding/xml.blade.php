<?xml version="1.0" encoding="UTF-8"?>
<ANECF xmlns="http://dgii.gov.do/anulacion">
    <Encabezado>
        <RNCEmisor>{{ $rncEmisor }}</RNCEmisor>
        <CantidadeNCFAnulados>{{ $cantidad }}</CantidadeNCFAnulados>
    </Encabezado>
    <DetalleAnulacion>
        <TipoeCF>{{ $tipoEcf }}</TipoeCF>
        <SecuenciaeNCFDesde>{{ $secuenciaDesde }}</SecuenciaeNCFDesde>
        <SecuenciaeNCFHasta>{{ $secuenciaHasta }}</SecuenciaeNCFHasta>
    </DetalleAnulacion>
    <MotivoAnulacion>{{ $motivo }}</MotivoAnulacion>
</ANECF>
