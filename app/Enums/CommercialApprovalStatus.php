<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum CommercialApprovalStatus: string implements HasLabel, HasIcon, HasColor
{
    case PENDIENTE = 'Pending';
    case APROBADO = 'Approved';
    case RECHAZADO = 'Rejected';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::PENDIENTE => 'Pendiente',
            self::APROBADO => 'Aprobado',
            self::RECHAZADO => 'Rechazado',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::PENDIENTE => 'heroicon-m-clock',
            self::APROBADO => 'heroicon-m-hand-thumb-up',
            self::RECHAZADO => 'heroicon-m-hand-thumb-down',
        };
    }

    public function getColor(): string | array | null
    {
        return match ($this) {
            self::PENDIENTE => 'gray',
            self::APROBADO => 'success',
            self::RECHAZADO => 'danger',
        };
    }
}
