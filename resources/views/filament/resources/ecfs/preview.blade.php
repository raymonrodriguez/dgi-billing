<div class="p-4 bg-white border border-gray-200 rounded-lg shadow-sm">
    <div class="flex justify-end mb-4 no-print">
        <button onclick="window.print()" class="px-4 py-2 text-sm font-medium text-white bg-primary-600 rounded-md hover:bg-primary-700">
            Imprimir Pantalla
        </button>
    </div>

    <!-- Incluimos el mismo Blade que usamos para el PDF pero adaptado para web -->
    <div id="invoice-preview" style="background: white; color: black; padding: 20px; font-family: sans-serif; line-height: 1.2;">
        @include('pdf.ecf', [
            'ecf' => $ecf,
            'company' => $company,
            'contact' => $contact,
            'items' => $items,
            'qrCode' => $qrCode,
            'sequenceExpiration' => $sequenceExpiration
        ])
    </div>
</div>

<style>
    @media print {
        .no-print, header, nav, aside { display: none !important; }
        body { background: white !important; }
        #invoice-preview { border: none !important; box-shadow: none !important; }
    }
</style>
