<?php

namespace App\Repositories\Contracts;

use App\Models\DgiiToken;

interface DgiiTokenRepositoryInterface
{
    public function getValidToken(string $taxId): ?string;
    public function saveToken(string $taxId, string $token, string $expiresAt): DgiiToken;
}
