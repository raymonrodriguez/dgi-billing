<?php

namespace App\Filament\Resources\Ecfs\Pages;

use App\Filament\Resources\Ecfs\EcfResource;
use App\Models\EcfSequence;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Exceptions\Halt;
use Filament\Facades\Filament;
use Filament\Support\Enums\Width;

class CreateEcf extends CreateRecord
{
    protected static string $resource = EcfResource::class;

    public function getMaxContentWidth(): Width | string | null
    {
        return Width::Full;
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $tenant = Filament::getTenant();

        // 1. Buscar la secuencia activa para el tipo seleccionado
        $sequence = EcfSequence::where('company_id', $tenant->id)
            ->where('type', $data['type'])
            ->where('is_active', true)
            ->first();

        // 2. Validar que exista una secuencia
        if (!$sequence) {
            Notification::make()
                ->danger()
                ->title('Error de Secuencia')
                ->body("No existe un talonario activo configurado para el tipo de comprobante seleccionado ({$data['type']}). Ve a la sección de 'Talonarios e-NCF' para crearlo.")
                ->persistent()
                ->send();

            throw new Halt();
        }

        // 3. Validar que no se haya agotado el rango
        if ($sequence->current_sequence > $sequence->end_range) {
            Notification::make()
                ->danger()
                ->title('Talonario Agotado')
                ->body("La secuencia para el tipo {$data['type']} ha llegado a su límite final. Debes solicitar un nuevo talonario a la DGII y configurarlo.")
                ->persistent()
                ->send();

            throw new Halt();
        }

        // 4. Generar el e-NCF: Prefijo 'E' + Tipo (2 dígitos) + Secuencia (10 dígitos)
        $formattedSequence = str_pad((string) $sequence->current_sequence, 10, '0', STR_PAD_LEFT);
        $data['encf'] = "E{$data['type']}{$formattedSequence}";

        // 5. Incrementar el contador de la secuencia para la próxima factura
        $sequence->increment('current_sequence');

        return $data;
    }
}
