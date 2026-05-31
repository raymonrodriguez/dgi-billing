<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum AdditionalTaxCode: string implements HasLabel
{
    case PROPINA_LEGAL = '001';
    case CDT = '002';
    case SEGUROS = '003';
    case ISC_CERVEZA = '006';
    case ISC_ALCOHOL = '007';
    case ISC_TABACO = '008';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::PROPINA_LEGAL => '001 - Propina Legal (10%)',
            self::CDT => '002 - CDT (2%)',
            self::SEGUROS => '003 - Impuesto sobre Seguros (16%)',
            self::ISC_CERVEZA => '006 - ISC Cervezas',
            self::ISC_ALCOHOL => '007 - ISC Bebidas Alcohólicas',
            self::ISC_TABACO => '008 - ISC Tabaco',
        };
    }
}
