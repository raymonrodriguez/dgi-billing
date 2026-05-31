<?php

namespace App\Repositories\Eloquent;

use App\Models\Company;
use App\Repositories\Contracts\CompanyRepositoryInterface;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Storage;

class CompanyRepository implements CompanyRepositoryInterface
{
    public function getCurrentTenant(): ?Company
    {
        // En un SaaS, el "Emisor Activo" es el Tenant que tiene la sesión iniciada
        return Filament::getTenant();
    }

    public function getCertificateData(Company $company): array
    {
        return [
            'path' => Storage::path($company->certificate),
            'password' => $company->cert_password,
            'rnc' => $company->tax_id
        ];
    }
}
