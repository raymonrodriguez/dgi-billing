<?php

namespace App\Filament\Resources\EcfAnnulments\Pages;

use App\Filament\Resources\EcfAnnulments\EcfAnnulmentResource;
use App\Jobs\SendCancellationJob;
use Filament\Resources\Pages\CreateRecord;

class CreateEcfAnnulment extends CreateRecord
{
    protected static string $resource = EcfAnnulmentResource::class;

    protected function afterCreate(): void
    {
        SendCancellationJob::dispatch($this->getRecord());
    }
}
