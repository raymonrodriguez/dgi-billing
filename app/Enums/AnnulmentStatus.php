<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum AnnulmentStatus: string implements HasLabel, HasIcon, HasColor
{
    case PENDIENTE = 'Pending';
    case ENVIADO = 'Sent';
    case ERROR = 'Error';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::PENDIENTE => 'Pendiente',
            self::ENVIADO => 'Enviado',
            self::ERROR => 'Error',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::PENDIENTE => 'heroicon-m-clock',
            self::ENVIADO => 'heroicon-m-paper-airplane',
            self::ERROR => 'heroicon-m-exclamation-triangle',
        };
    }

    public function getColor(): string | array | null
    {
        return match ($this) {
            self::PENDIENTE => 'gray',
            self::ENVIADO => 'success',
            self::ERROR => 'danger',
        };
    }
}
