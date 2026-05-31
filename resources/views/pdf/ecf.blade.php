<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Representación Impresa e-CF - {{ $ecf->encf }}</title>
    <style>
        @page { margin: 1cm; }
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 11px; color: #111; line-height: 1.4; margin: 0; padding: 0; }
        .container { width: 100%; }
        .header { width: 100%; margin-bottom: 20px; }
        .company-header { float: left; width: 60%; }
        .invoice-header { float: right; width: 38%; border: 1px solid #000; padding: 10px; text-align: center; background-color: #f9f9f9; }
        .clear { clear: both; }
        
        .section-box { border: 1px solid #ccc; padding: 8px; margin-bottom: 10px; }
        .section-title { font-weight: bold; text-transform: uppercase; border-bottom: 1px solid #eee; margin-bottom: 5px; font-size: 10px; color: #555; }
        
        .column-left { float: left; width: 49%; }
        .column-right { float: right; width: 49%; }
        
        .table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .table th { border-bottom: 2px solid #333; padding: 6px 4px; text-align: left; font-size: 10px; background: #eee; }
        .table td { border-bottom: 1px solid #eee; padding: 6px 4px; }
        
        .totals-container { float: right; width: 40%; margin-top: 15px; }
        .total-row { padding: 3px 0; clear: both; }
        .total-label { float: left; width: 60%; text-align: right; padding-right: 10px; }
        .total-value { float: right; width: 35%; text-align: right; font-weight: bold; }
        .grand-total { border-top: 1px solid #000; margin-top: 5px; padding-top: 5px; font-size: 13px; }
        
        .footer-qr { position: fixed; bottom: 0; width: 100%; padding-top: 20px; border-top: 1px solid #eee; }
        .qr-img { float: left; width: 120px; }
        .qr-text { float: left; margin-left: 20px; margin-top: 30px; font-family: monospace; font-size: 12px; }
        
        .text-bold { font-weight: bold; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        
        @php
            $tipoEcf = match($ecf->type) {
                '31' => 'Factura de Crédito Fiscal Electrónica',
                '32' => 'Factura de Consumo Electrónica',
                '33' => 'Nota de Débito Electrónica',
                '34' => 'Nota de Crédito Electrónica',
                '41' => 'Compras Electrónica',
                '43' => 'Gastos Menores Electrónicos',
                '44' => 'Regímenes Especiales Electrónica',
                '45' => 'Gubernamental Electrónica',
                '46' => 'Comprobante de Exportación Electrónico',
                '47' => 'Comprobante para Pagos al Exterior Electrónico',
                default => 'Comprobante Electrónico',
            };
        @endphp
    </style>
</head>
<body>
    <div class="container">
        <!-- ENCABEZADO -->
        <div class="header">
            <div class="company-header">
                @if($company->logo)
                    <div style="margin-bottom: 10px;">
                        <img src="{{ public_path('storage/' . $company->logo) }}" alt="Logo" style="max-height: 60px; max-width: 200px;">
                    </div>
                @endif
                <div style="font-size: 16px; font-weight: bold;">{{ $company->company_name }}</div>
                @if($company->trade_name) <div style="font-style: italic;">{{ $company->trade_name }}</div> @endif
                <div style="margin-top: 5px;">RNC: <span class="text-bold">{{ $company->tax_id }}</span></div>
                <div>{{ $company->address ?? 'Santo Domingo, República Dominicana' }}</div>
                <div>Tel: {{ $company->phone ?? 'N/A' }}</div>
            </div>
            <div class="invoice-header">
                <div style="font-size: 12px; font-weight: bold;">RNC EMISOR: {{ $company->tax_id }}</div>
                <div style="font-size: 11px; margin: 5px 0;">{{ strtoupper($tipoEcf) }}</div>
                <div style="font-size: 14px; font-weight: bold; color: #d32f2f;">e-NCF: {{ $ecf->encf }}</div>
            </div>
            <div class="clear"></div>
        </div>

        <!-- DATOS DEL COMPROBANTE Y RECEPTOR -->
        <div class="section-box">
            <div class="column-left">
                <div class="section-title">Datos del Receptor</div>
                <div><span class="text-bold">Razón Social:</span> {{ $contact->name }}</div>
                <div><span class="text-bold">RNC/Cédula:</span> {{ $contact->tax_id }}</div>
                @if($contact->address) <div><span class="text-bold">Dirección:</span> {{ $contact->address }}</div> @endif
            </div>
            <div class="column-right">
                <div class="section-title">Información de Facturación</div>
                <div><span class="text-bold">Fecha Emisión:</span> {{ $ecf->issued_at->format('d/m/Y') }}</div>
                <div><span class="text-bold">Vence Secuencia:</span> {{ $sequenceExpiration ? $sequenceExpiration->format('d/m/Y') : 'N/A' }}</div>
                @if($ecf->type == '31')
                <div><span class="text-bold">Tipo Ingresos:</span> 01 - Ingresos por Operaciones (Fiscal)</div>
                @endif
            </div>
            <div class="clear"></div>
        </div>

        <!-- DETALLE DE BIENES O SERVICIOS -->
        <table class="table">
            <thead>
                <tr>
                    <th class="text-center" style="width: 10%;">Cant.</th>
                    <th>Descripción</th>
                    <th class="text-right" style="width: 15%;">Precio Unit.</th>
                    <th class="text-right" style="width: 15%;">ITBIS</th>
                    <th class="text-right" style="width: 15%;">Monto Ítem</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $item)
                @php
                    // Cálculo aproximado del ITBIS por ítem para la representación impresa
                    // En un sistema real, esto debería venir desglosado desde la tabla ecf_taxes vinculada al item
                    $itemItbis = $item->subtotal * 0.18; // Ejemplo simplificado al 18%
                @endphp
                <tr>
                    <td class="text-center">{{ number_format($item->quantity, 2) }}</td>
                    <td>{{ $item->description }}</td>
                    <td class="text-right">{{ number_format($item->price, 2) }}</td>
                    <td class="text-right">{{ number_format($itemItbis, 2) }}</td>
                    <td class="text-right">{{ number_format($item->subtotal, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- TOTALES -->
        <div class="totals-container">
            <div class="total-row">
                <span class="total-label">Subtotal:</span>
                <span class="total-value">RD$ {{ number_format($ecf->total_amount - $ecf->total_tax, 2) }}</span>
            </div>
            <div class="total-row">
                <span class="total-label">Monto Exento:</span>
                <span class="total-value">RD$ 0.00</span>
            </div>
            <div class="total-row">
                <span class="total-label">Total ITBIS (18%):</span>
                <span class="total-value">RD$ {{ number_format($ecf->total_tax, 2) }}</span>
            </div>
            <div class="total-row grand-total">
                <span class="total-label text-bold">TOTAL:</span>
                <span class="total-value">RD$ {{ number_format($ecf->total_amount, 2) }}</span>
            </div>
            <div class="clear"></div>
        </div>
        <div class="clear"></div>

        <!-- PIE DE PÁGINA CON QR -->
        <div class="footer-qr">
            <div class="qr-img">
                <img src="data:image/png;base64, {!! $qrCode !!}" alt="QR DGII" width="100">
            </div>
            <div class="qr-text">
                <div class="text-bold">Código de Seguridad: {{ $ecf->security_code }}</div>
                <div style="font-size: 9px; margin-top: 5px; color: #666;">
                    Representación Impresa de un Comprobante Fiscal Electrónico.<br>
                    Consulte la validez de este documento en el portal de la DGII.
                </div>
            </div>
            <div class="clear"></div>
        </div>
    </div>
</body>
</html>
