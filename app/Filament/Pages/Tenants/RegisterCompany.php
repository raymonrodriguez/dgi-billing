<?php

namespace App\Filament\Pages\Tenants;

use App\Models\Company;
use App\Filament\Provider\Resources\Companies\Schemas\CompanyForm;
use Filament\Schemas\Schema;
use Filament\Pages\Tenancy\RegisterTenant;
use Filament\Support\Enums\Width;

class RegisterCompany extends RegisterTenant
{
    public static function getLabel(): string
    {
        return 'Registrar Empresa';
    }

    public function getMaxContentWidth(): Width | string | null
    {
        return Width::Full;
    }

    public function form(Schema $schema): Schema
    {
        return CompanyForm::configure($schema);
    }

    protected function handleRegistration(array $data): Company
    {
        $company = Company::create($data);

        $company->users()->attach(auth()->user());

        return $company;
    }
}
