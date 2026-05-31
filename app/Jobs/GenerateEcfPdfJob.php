<?php

namespace App\Jobs;

use App\Models\Ecf;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Barryvdh\DomPDF\Facade\Pdf;

class GenerateEcfPdfJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $ecfId
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $ecf = Ecf::with(['company', 'contact', 'items', 'taxes', 'payments'])->find($this->ecfId);

        if (!$ecf) {
            return;
        }

        // 1. Construir la URL del Código QR según las reglas de la DGII
        $qrUrl = $this->buildQrUrl($ecf);

        // 2. Generar el Código QR en base64
        $qrCodeBase64 = base64_encode(QrCode::format('png')->size(150)->margin(0)->generate($qrUrl));

        // Obtener fecha de vencimiento de la secuencia (última activa del mismo tipo)
        $sequence = \App\Models\EcfSequence::where('company_id', $ecf->company_id)
            ->where('type', $ecf->type)
            ->where('is_active', true)
            ->first();

        // 3. Renderizar y generar el PDF
        $pdf = Pdf::loadView('pdf.ecf', [
            'ecf' => $ecf,
            'company' => $ecf->company,
            'contact' => $ecf->contact,
            'items' => $ecf->items,
            'qrCode' => $qrCodeBase64,
            'sequenceExpiration' => $sequence?->expiration_date,
        ]);

        // 4. Guardar en storage y actualizar pdf_path
        $directory = "pdfs/{$ecf->company_id}";
        $fileName = "{$ecf->encf}.pdf";
        $filePath = "{$directory}/{$fileName}";

        Storage::disk('public')->put($filePath, $pdf->output());

        $ecf->update([
            'pdf_path' => $filePath,
        ]);
    }

    /**
     * Build the QR URL based on DGII rules.
     */
    protected function buildQrUrl(Ecf $ecf): string
    {
        $company = $ecf->company;
        $env = $company->environment;
        
        // Determinar si es RFCE (Resumen de Factura de Consumo)
        $isRfce = ($ecf->type === '32' && $ecf->total_amount < 250000);

        if ($isRfce) {
            // Regla B: RFCE
            $baseUrl = "https://fc.dgii.gov.do/{$env}/consultatimbrefc";
            $params = [
                'rncemisor' => $company->tax_id,
                'encf' => $ecf->encf,
                'montototal' => (float) $ecf->total_amount,
                'codigoseguridad' => $ecf->security_code,
            ];
        } else {
            // Regla A: e-CF estándar
            $baseUrl = "https://ecf.dgii.gov.do/{$env}/consultatimbre";
            $params = [
                'rncemisor' => $company->tax_id,
                'rnccomprador' => $ecf->contact->tax_id,
                'encf' => $ecf->encf,
                'fechaemision' => $ecf->issued_at->format('d-m-Y'),
                'montototal' => (float) $ecf->total_amount,
                'fechafirma' => $ecf->updated_at->format('d-m-Y H:i:s'), // Aproximación a la firma
                'codigoseguridad' => $ecf->security_code,
            ];
        }

        return $baseUrl . '?' . http_build_query($params);
    }
}
