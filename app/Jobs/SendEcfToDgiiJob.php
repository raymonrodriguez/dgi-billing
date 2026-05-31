<?php

namespace App\Jobs;

use App\Models\Ecf;
use App\Services\DgiiAuthService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use PlatinumPlace\LaravelDgii\Facades\Dgii;

class SendEcfToDgiiJob implements ShouldQueue
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
    public function handle(DgiiAuthService $authService): void
    {
        $ecf = Ecf::with('company')->find($this->ecfId);

        if (!$ecf || !$ecf->signed_xml_path) {
            return;
        }

        $company = $ecf->company;
        $env = $company->environment;
        $xmlPath = Storage::path($ecf->signed_xml_path);

        try {
            // 1. Obtener Token válido
            $token = $authService->getToken($company->id);

            // 2. Aplicar Regla de Negocio Crítica: Factura de Consumo (32) < 250,000
            // Estas se envían al endpoint de Resumen (RFCE)
            if ($ecf->type === '32' && $ecf->total_amount < 250000) {
                $response = Dgii::sendRfce($env, $token, $xmlPath);
            } else {
                // Envío por la recepción estándar
                $response = Dgii::sendInvoice($env, $token, $xmlPath);
            }

            // 3. Guardar Track ID y actualizar estatus
            // El paquete devuelve ['trackId' => '...']
            $ecf->update([
                'track_id' => $response['trackId'] ?? null,
                'dgii_status' => 'En Proceso',
                'dgii_response' => $response,
            ]);

        } catch (\Exception $e) {
            // En caso de error, marcar como pendiente o fallido para reintentar
            $ecf->update([
                'dgii_status' => 'Rejected',
                'dgii_response' => ['error' => $e->getMessage()],
            ]);
            
            throw $e; // Permitir que la cola maneje el reintento si es necesario
        }
    }
}
