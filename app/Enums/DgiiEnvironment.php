<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum DgiiEnvironment: string implements HasLabel, HasIcon, HasColor
{
    case TEST = 'testecf';
    case CERTIFICATION = 'certecf';
    case PRODUCTION = 'ecf';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::TEST => 'Pruebas',
            self::CERTIFICATION => 'Certificación',
            self::PRODUCTION => 'Producción',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::TEST => 'heroicon-m-beaker',
            self::CERTIFICATION => 'heroicon-m-clipboard-document-check',
            self::PRODUCTION => 'heroicon-m-server',
        };
    }

    public function getColor(): string | array | null
    {
        return match ($this) {
            self::TEST => 'gray',
            self::CERTIFICATION => 'warning',
            self::PRODUCTION => 'success',
        };
    }
}
