<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum ItbisIndicator: string implements HasLabel
{
    case ITBIS_18 = '1';
    case ITBIS_16 = '2';
    case ITBIS_0 = '3';
    case EXENTO = '4';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::ITBIS_18 => 'ITBIS (18%)',
            self::ITBIS_16 => 'ITBIS (16%)',
            self::ITBIS_0 => 'ITBIS (0%)',
            self::EXENTO => 'Exento',
        };
    }
}
