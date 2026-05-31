<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum EcfStatus: string implements HasLabel, HasIcon, HasColor
{
    case ACEPTADO = 'Aceptado';
    case RECHAZADO = 'Rechazado';
    case ACEPTADO_CONDICIONAL = 'Aceptado Condicional';
    case EN_PROCESO = 'En Proceso';
    case PENDIENTE = 'Pendiente';

    public function getLabel(): ?string
    {
        return $this->value;
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::ACEPTADO => 'heroicon-m-check-badge',
            self::RECHAZADO => 'heroicon-m-x-circle',
            self::ACEPTADO_CONDICIONAL => 'heroicon-m-exclamation-circle',
            self::EN_PROCESO => 'heroicon-m-arrow-path',
            self::PENDIENTE => 'heroicon-m-clock',
        };
    }

    public function getColor(): string | array | null
    {
        return match ($this) {
            self::ACEPTADO => 'success',
            self::RECHAZADO => 'danger',
            self::ACEPTADO_CONDICIONAL, self::EN_PROCESO => 'warning',
            self::PENDIENTE => 'gray',
        };
    }
}
