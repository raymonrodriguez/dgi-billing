<?php

namespace App\Filament\Resources\ReceivedEcfs\Schemas;

use App\Enums\CommercialApprovalStatus;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ReceivedEcfForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(3)
                    ->columnSpanFull()
                    ->components([
                        Section::make('Datos de la Factura Recibida')
                            ->description('Información del comprobante enviado por el suplidor.')
                            ->columnSpan(2)
                            ->schema([
                                Grid::make(2)->schema([
                                    TextInput::make('rnc_emisor')
                                        ->label('RNC Suplidor')
                                        ->prefixIcon('heroicon-m-building-office')
                                        ->disabled(),

                                    TextInput::make('encf')
                                        ->label('e-NCF')
                                        ->prefixIcon('heroicon-m-hashtag')
                                        ->disabled(),
                                ]),

                                TextInput::make('total_amount')
                                    ->label('Monto Total')
                                    ->numeric()
                                    ->prefix('RD$')
                                    ->prefixIcon('heroicon-m-banknotes')
                                    ->disabled(),

                                TextInput::make('received_xml_path')
                                    ->label('Ruta del XML Original')
                                    ->prefixIcon('heroicon-m-code-bracket')
                                    ->disabled(),
                            ]),

                        Section::make('Aprobación Comercial')
                            ->columnSpan(1)
                            ->schema([
                                ToggleButtons::make('commercial_approval_status')
                                    ->label('Estatus Aprobación')
                                    ->options(CommercialApprovalStatus::class)
                                    ->default(CommercialApprovalStatus::PENDIENTE)
                                    ->inline()
                                    ->grouped()
                                    ->disabled(),
                            ]),
                    ]),
            ]);
    }
}
