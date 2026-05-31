<?php

namespace App\Filament\Pages\Tenants;

use App\Models\Company;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Pages\Tenancy\RegisterTenant;

class RegisterCompany extends RegisterTenant
{
    public static function getLabel(): string
    {
        return 'Registrar Empresa';
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('company_name')
                    ->label('Nombre de la Empresa')
                    ->required(),
                TextInput::make('tax_id')
                    ->label('RNC')
                    ->required()
                    ->unique('companies', 'tax_id'),
            ]);
    }

    protected function handleRegistration(array $data): Company
    {
        $company = Company::create($data);

        $company->users()->attach(auth()->user());

        return $company;
    }
}
