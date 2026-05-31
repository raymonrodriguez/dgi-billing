<?php

namespace App\Repositories\Contracts;

use App\Models\Ecf;
use App\Enums\EcfStatus;

interface EcfRepositoryInterface
{
    public function findById(int $id): ?Ecf;
    public function updateStatus(Ecf $ecf, EcfStatus $status, ?string $trackId = null, ?string $xmlPath = null): bool;
    public function saveDgiiResponse(Ecf $ecf, array $responseLog): void;
    public function getPendingEmission(): \Illuminate\Support\Collection;
}
