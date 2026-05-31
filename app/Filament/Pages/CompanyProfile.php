<?php

namespace App\Filament\Pages;

use App\Filament\Provider\Resources\Companies\Schemas\CompanyForm;
use Filament\Schemas\Schema;
use Filament\Pages\Tenancy\EditTenantProfile;
use Illuminate\Contracts\Support\Htmlable;
use Filament\Support\Enums\Width;

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

    public function getMaxContentWidth(): Width | string | null
    {
        return Width::Full;
    }

    public function form(Schema $schema): Schema
    {
        // Reutilizamos el mismo formulario premium del panel de proveedor
        return CompanyForm::configure($schema);
    }
}
