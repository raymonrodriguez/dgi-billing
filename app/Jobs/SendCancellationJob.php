<?php

namespace App\Jobs;

use App\Models\EcfAnnulment;
use App\Services\DGII\DgiiAuthService;
use App\Enums\AnnulmentStatus;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use PlatinumPlace\LaravelDgii\Facades\Dgii;

class SendCancellationJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public EcfAnnulment $annulment
    ) {
    }

    /**
     * Execute the job.
     */
    public function handle(DgiiAuthService $authService): void
    {
        $annulment = $this->annulment->load('company');
        $company = $annulment->company;

        try {
            // 1. Obtener Token
            $token = $authService->getToken();

            // 2. Recuperar certificado y clave
            $certContent = Storage::get($company->certificate);
            $certPassword = $company->cert_password;

            // 3. Formatear array de datos
            $data = [
                'RNCEmisor' => $company->tax_id,
                'CantidadeNCFAnulados' => (int) $annulment->quantity,
                'DetalleAnulacion' => [
                    [
                        'TipoeCF' => $annulment->type,
                        'SecuenciaeNCFDesde' => (int) $annulment->start_sequence,
                        'SecuenciaeNCFHasta' => (int) $annulment->end_sequence,
                    ]
                ],
                'MotivoAnulacion' => $annulment->reason,
            ];

            // 4. Renderizar y firmar el XML
            $xmlFirmado = Dgii::renderCancellationRange($certContent, $certPassword, $data);

            // 5. Guardar XML
            $fileName = "annulments/{$company->tax_id}/ANNUL_{$annulment->type}_{$annulment->id}.xml";
            Storage::put($fileName, $xmlFirmado);
            $xmlPath = Storage::path($fileName);

            // 6. Enviar a la DGII
            $response = Dgii::sendCancellationRange($company->environment->value, $token, $xmlPath);

            // 7. Actualizar estado
            $annulment->update([
                'status' => AnnulmentStatus::ENVIADO,
                'xml_path' => $fileName,
                'response' => $response,
            ]);

        } catch (\Exception $e) {
            $annulment->update([
                'status' => AnnulmentStatus::ERROR,
                'response' => ['error' => $e->getMessage()],
            ]);
            throw $e;
        }
    }
}
