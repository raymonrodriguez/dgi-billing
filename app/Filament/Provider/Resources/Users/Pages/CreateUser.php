<?php

namespace App\Filament\Provider\Resources\Users\Pages;

use App\Filament\Provider\Resources\Users\UserResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Enums\Width;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    public function getMaxContentWidth(): Width | string | null
    {
        return Width::Full;
    }
}
