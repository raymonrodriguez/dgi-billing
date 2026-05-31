<?php

namespace App\Jobs;

use App\Models\Ecf;
use App\Services\DgiiAuthService;
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
    public function handle(DgiiAuthService $authService): void
    {
        $this->ecf->load(['company', 'user']);
        $company = $this->ecf->company;

        if (!$this->ecf->track_id || $this->ecf->dgii_status !== 'En Proceso') {
            return;
        }

        try {
            // 1. Obtener Token válido
            $token = $authService->getToken($company->id);

            // 2. Consultar Estatus en la DGII
            $response = Dgii::findInvoice($company->environment, $token, $this->ecf->track_id);

            // 3. Analizar respuesta y actualizar estatus
            $rawStatus = $response['status'] ?? 'En Proceso';
            $mappedStatus = $this->mapDgiiStatus($rawStatus);

            $this->ecf->update([
                'dgii_status' => $mappedStatus,
                'dgii_messages' => $response['messages'] ?? ($response['error'] ?? null),
                'dgii_response' => array_merge($this->ecf->dgii_response ?? [], ['verification_detail' => $response]),
            ]);

            // 4. Enviar Notificación al Usuario
            if ($this->ecf->user) {
                $statusLabel = $mappedStatus === 'Aceptado' ? 'Aceptada' : 'Rechazada';
                $color = $mappedStatus === 'Aceptado' ? 'success' : 'danger';

                Notification::make()
                    ->title("Factura e-NCF {$statusLabel}")
                    ->body("El comprobante {$this->ecf->encf} ha sido {$mappedStatus} por la DGII.")
                    ->icon($mappedStatus === 'Aceptado' ? 'heroicon-o-check-circle' : 'heroicon-o-x-circle')
                    ->color($color)
                    ->sendToDatabase($this->ecf->user);
            }

            // 5. Regla Crítica: Si es Aceptado, generar PDF
            if (in_array($mappedStatus, ['Aceptado', 'Aceptado Condicional'])) {
                GenerateEcfPdfJob::dispatch($this->ecf->id);
            }

            // 6. Si es Rechazado, manejar lógica adicional
            if ($mappedStatus === 'Rechazado') {
                // TODO: Notificar vía Email o activar alerta en el panel
            }

        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Map DGII response status to internal database status.
     */
    protected function mapDgiiStatus(string $status): string
    {
        return match (strtolower($status)) {
            'aceptado' => 'Aceptado',
            'rechazado' => 'Rechazado',
            'aceptado condicional' => 'Aceptado Condicional',
            default => 'En Proceso',
        };
    }
}
