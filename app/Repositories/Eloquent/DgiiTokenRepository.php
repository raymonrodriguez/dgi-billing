<?php

namespace App\Repositories\Eloquent;

use App\Models\DgiiToken;
use App\Repositories\Contracts\DgiiTokenRepositoryInterface;
use Carbon\Carbon;
use Filament\Facades\Filament;

class DgiiTokenRepository implements DgiiTokenRepositoryInterface
{
    public function getValidToken(string $taxId): ?string
    {
        $token = DgiiToken::whereHas('company', function ($query) use ($taxId) {
            $query->where('tax_id', $taxId);
        })
            ->where('expires_at', '>', Carbon::now()->addMinutes(2))
            ->orderBy('created_at', 'desc')
            ->first();

        return $token ? $token->token : null;
    }

    public function saveToken(string $taxId, string $token, string $expiresAt): DgiiToken
    {
        // En un entorno multi-tenant, necesitamos asociar el token a la empresa actual
        $company = Filament::getTenant();

        return DgiiToken::create([
            'company_id' => $company->id,
            'token' => $token,
            'expires_at' => Carbon::parse($expiresAt),
        ]);
    }
}
