<?php

namespace App\Jobs;

use App\Models\Ecf;
use App\Services\DGII\DgiiAuthService;
use App\Repositories\Contracts\EcfRepositoryInterface;
use App\Enums\EcfStatus;
use Filament\Notifications\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use PlatinumPlace\LaravelDgii\Facades\Dgii;

class VerifyTrackIdJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Ecf $ecf
    ) {}

    /**
     * Execute the job.
     */
    public function handle(DgiiAuthService $authService, EcfRepositoryInterface $ecfRepo): void
    {
        $this->ecf->load(['company', 'user']);
        $company = $this->ecf->company;

        if (!$this->ecf->track_id || ($this->ecf->dgii_status !== EcfStatus::EN_PROCESO && $this->ecf->dgii_status !== EcfStatus::ENVIADO)) {
            return;
        }

        try {
            // 1. Obtener Token (El servicio ahora usa el repositorio interno)
            // Nota: Para multi-tenancy asíncrono, podríamos necesitar pasar el ID al servicio
            // Pero siguiendo el código provisto, el servicio busca la empresa activa.
            $token = $authService->getToken();

            // 2. Consultar Estatus en la DGII
            $response = Dgii::findInvoice($company->environment->value, $token, $this->ecf->track_id);

            // 3. Analizar respuesta y actualizar estatus vía Repositorio
            $rawStatus = $response['status'] ?? 'En Proceso';
            $mappedStatus = $this->mapDgiiStatus($rawStatus);

            $ecfRepo->updateStatus($this->ecf, $mappedStatus);
            $ecfRepo->saveDgiiResponse($this->ecf, $response);

            // 4. Enviar Notificación al Usuario
            if ($this->ecf->user) {
                $statusLabel = $mappedStatus === EcfStatus::ACEPTADO ? 'Aceptada' : 'Rechazada';
                $color = $mappedStatus === EcfStatus::ACEPTADO ? 'success' : 'danger';

                Notification::make()
                    ->title("Factura e-NCF {$statusLabel}")
                    ->body("El comprobante {$this->ecf->encf} ha sido {$mappedStatus->value} por la DGII.")
                    ->icon($mappedStatus === EcfStatus::ACEPTADO ? 'heroicon-o-check-circle' : 'heroicon-o-x-circle')
                    ->color($color)
                    ->sendToDatabase($this->ecf->user);
            }

            // 5. Regla Crítica: Si es Aceptado, generar PDF
            if (in_array($mappedStatus, [EcfStatus::ACEPTADO, EcfStatus::ACEPTADO_CONDICIONAL])) {
                GenerateEcfPdfJob::dispatch($this->ecf->id);
            }

        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Map DGII response status to EcfStatus Enum.
     */
    protected function mapDgiiStatus(string $status): EcfStatus
    {
        return match (strtolower($status)) {
            'aceptado' => EcfStatus::ACEPTADO,
            'rechazado' => EcfStatus::RECHAZADO,
            'aceptado condicional' => EcfStatus::ACEPTADO_CONDICIONAL,
            default => EcfStatus::EN_PROCESO,
        };
    }
}
