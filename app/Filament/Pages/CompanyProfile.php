<?php

namespace App\Filament\Pages;

use App\Filament\Resources\Companies\Schemas\CompanyForm;
use Filament\Schemas\Schema;
use Filament\Pages\Tenancy\EditTenantProfile;
use Illuminate\Contracts\Support\Htmlable;

class CompanyProfile extends EditTenantProfile
{
    public static function getLabel(): string
    {
        return 'Perfil de la Empresa';
    }

    public function getTitle(): string | Htmlable
    {
        return 'Perfil de la Empresa';
    }

    public function form(Schema $schema): Schema
    {
        // Reutilizamos el mismo formulario que ya habíamos diseñado y validado
        return CompanyForm::configure($schema);
    }
}
