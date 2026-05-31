<?php

namespace App\Console\Commands;

use App\Models\Ecf;
use App\Jobs\VerifyTrackIdJob;
use Illuminate\Console\Command;

class CheckEcfStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dgii:check-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Busca facturas "En Proceso" y despacha un Job para verificar su estatus en la DGII';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $pendingEcfs = Ecf::where('dgii_status', 'En Proceso')
            ->whereNotNull('track_id')
            ->get();

        if ($pendingEcfs->isEmpty()) {
            $this->info('No se encontraron facturas con estatus "En Proceso".');
            return;
        }

        $this->info("Procesando {$pendingEcfs->count()} facturas...");

        foreach ($pendingEcfs as $ecf) {
            VerifyTrackIdJob::dispatch($ecf);
            $this->line("Job despachado para e-NCF: {$ecf->encf} (TrackID: {$ecf->track_id})");
        }

        $this->success('Todos los Jobs han sido despachados correctamente.');
    }
}
