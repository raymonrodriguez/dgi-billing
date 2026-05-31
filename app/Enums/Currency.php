<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum Currency: string implements HasLabel
{
    case DOP = 'DOP';
    case USD = 'USD';
    case EUR = 'EUR';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::DOP => 'Peso Dominicano (DOP)',
            self::USD => 'Dólar Estadounidense (USD)',
            self::EUR => 'Euro (EUR)',
        };
    }
}
