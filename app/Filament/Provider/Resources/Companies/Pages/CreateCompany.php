<?php

namespace App\Filament\Provider\Resources\Companies\Pages;

use App\Filament\Provider\Resources\Companies\CompanyResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Enums\Width;

class CreateCompany extends CreateRecord
{
    protected static string $resource = CompanyResource::class;

    public function getMaxContentWidth(): Width | string | null
    {
        return Width::Full;
    }
}
