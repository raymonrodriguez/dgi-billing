<?php

namespace App\Repositories\Eloquent;

use App\Models\Ecf;
use App\Enums\EcfStatus;
use App\Repositories\Contracts\EcfRepositoryInterface;

class EcfRepository implements EcfRepositoryInterface
{
    public function findById(int $id): ?Ecf
    {
        return Ecf::with(['items', 'company', 'contact'])->find($id);
    }

    public function updateStatus(Ecf $ecf, EcfStatus $status, ?string $trackId = null, ?string $xmlPath = null): bool
    {
        $data = ['dgii_status' => $status];

        if ($trackId) {
            $data['track_id'] = $trackId;
        }

        if ($xmlPath) {
            $data['signed_xml_path'] = $xmlPath;
        }

        return $ecf->update($data);
    }

    public function saveDgiiResponse(Ecf $ecf, array $responseLog): void
    {
        $ecf->update([
            'dgii_response' => array_merge($ecf->dgii_response ?? [], [
                'timestamp' => now()->toIso8601String(),
                'log' => $responseLog
            ])
        ]);
    }

    public function getPendingEmission(): \Illuminate\Support\Collection
    {
        return Ecf::where('dgii_status', EcfStatus::PENDIENTE)->get();
    }
}
