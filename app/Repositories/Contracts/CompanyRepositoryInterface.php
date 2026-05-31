<?php

namespace App\Repositories\Contracts;

use App\Models\Company;

interface CompanyRepositoryInterface
{
    public function getActiveCompany(): ?Company;
    public function getCertificateData(Company $company): array;
}
