<?php

namespace App\Jobs;

use App\Models\ReceivedEcf;
use App\Services\DGII\DgiiAuthService;
use App\Enums\CommercialApprovalStatus;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;
use PlatinumPlace\LaravelDgii\Facades\Dgii;

class SendCommercialApprovalJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $receivedEcfId,
        public string $status // '1' para Aceptado, '2' para Rechazado
    ) {
    }

    /**
     * Execute the job.
     */
    public function handle(DgiiAuthService $authService): void
    {
        $receivedEcf = ReceivedEcf::with('company')->find($this->receivedEcfId);

        if (!$receivedEcf) {
            return;
        }

        $company = $receivedEcf->company;

        try {
            // 1. Obtener Token
            $token = $authService->getToken();

            // 2. Recuperar certificado y clave
            $certContent = Storage::get($company->certificate);
            $certPassword = $company->cert_password;

            // 3. Construir XML de Aprobación Comercial (ACECF)
            $xmlContent = View::make('xml.approvals.xml', [
                'rncEmisor' => $receivedEcf->rnc_emisor,
                'rncReceptor' => $company->tax_id,
                'encf' => $receivedEcf->encf,
                'status' => $this->status,
            ])->render();

            // 4. Firmar digitalmente el XML
            $signedXml = Dgii::signXmlContent($certContent, $certPassword, $xmlContent);

            // 5. Guardar el XML firmado
            $fileName = "responses/acecf/{$company->tax_id}/ACECF_{$receivedEcf->encf}_" . now()->timestamp . ".xml";
            Storage::put($fileName, $signedXml);
            $xmlPath = Storage::path($fileName);

            // 6. Enviar a la DGII
            $response = Dgii::sendCommercialApproval($company->environment->value, $token, $xmlPath);

            // 7. Actualizar estatus
            $receivedEcf->update([
                'commercial_approval_status' => ($this->status === '1') ? CommercialApprovalStatus::APROBADO : CommercialApprovalStatus::RECHAZADO,
                'acecf_sent' => true,
            ]);

        } catch (\Exception $e) {
            throw $e;
        }
    }
}
