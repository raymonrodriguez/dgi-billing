<?php

namespace App\Jobs;

use App\Services\DGII\DgiiEmissionService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Exception;

class SendEcfToDgiiJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Número de reintentos automáticos del Job si falla por timeouts del servidor fiscal.
     */
    public $tries = 3;

    public function __construct(protected int $ecfId) {}

    public function handle(DgiiEmissionService $emissionService): void
    {
        try {
            $result = $emissionService->emit($this->ecfId);
            Log::info("e-CF ID {$this->ecfId} emitido exitosamente. TrackID: " . $result['trackId']);
        } catch (Exception $e) {
            Log::error("Fallo crítico en Job de emisión e-CF ID {$this->ecfId}: " . $e->getMessage());
            $this->fail($e);
        }
    }
}
