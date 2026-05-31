<?php

namespace App\Repositories\Contracts;

use App\Models\Company;

interface CompanyRepositoryInterface
{
    /**
     * Get the currently active tenant/company.
     */
    public function getCurrentTenant(): ?Company;

    /**
     * Get security and certificate data for the given company.
     */
    public function getCertificateData(Company $company): array;
}
