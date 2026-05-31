<?php

namespace App\Repositories\Eloquent;

use App\Models\Company;
use App\Repositories\Contracts\CompanyRepositoryInterface;
use Illuminate\Support\Facades\Storage;

class CompanyRepository implements CompanyRepositoryInterface
{
    public function getActiveCompany(): ?Company
    {
        // Retorna la empresa emisora activa (configurable según multi-tenant o único emisor)
        return Company::where('is_active', true)->first();
    }

    public function getCertificateData(Company $company): array
    {
        return [
            'path' => Storage::path($company->certificate),
            'password' => $company->cert_password, // Ya está encriptado vía cast en el modelo
            'rnc' => $company->tax_id
        ];
    }
}
