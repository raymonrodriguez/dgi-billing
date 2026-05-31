<?php

namespace App\Filament\Resources\EcfAnnulments\Schemas;

use App\Enums\AnnulmentStatus;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class EcfAnnulmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(3)
                    ->columnSpanFull()
                    ->components([
                        Section::make('Detalle de la Anulación')
                            ->description('Especifica el rango de e-NCF que deseas invalidar ante la DGII.')
                            ->columnSpan(2)
                            ->schema([
                                Select::make('type')
                                    ->label('Tipo de e-CF')
                                    ->options([
                                        '31' => '31 - Crédito Fiscal',
                                        '32' => '32 - Consumo',
                                        '33' => '33 - Nota de Débito',
                                        '34' => '34 - Nota de Crédito',
                                    ])
                                    ->required()
                                    ->prefixIcon('heroicon-m-tag'),

                                Grid::make(3)->schema([
                                    TextInput::make('start_sequence')
                                        ->label('e-NCF Desde')
                                        ->required()
                                        ->numeric()
                                        ->prefixIcon('heroicon-m-arrow-right-start-on-rectangle'),

                                    TextInput::make('end_sequence')
                                        ->label('e-NCF Hasta')
                                        ->required()
                                        ->numeric()
                                        ->prefixIcon('heroicon-m-arrow-left-end-on-rectangle'),

                                    TextInput::make('quantity')
                                        ->label('Cantidad')
                                        ->required()
                                        ->numeric()
                                        ->prefixIcon('heroicon-m-calculator'),
                                ]),

                                Textarea::make('reason')
                                    ->label('Motivo de Anulación')
                                    ->required()
                                    ->columnSpanFull()
                                    ->rows(3),
                            ]),

                        Section::make('Estado del Proceso')
                            ->columnSpan(1)
                            ->schema([
                                ToggleButtons::make('status')
                                    ->label('Estatus Envío')
                                    ->options(AnnulmentStatus::class)
                                    ->default(AnnulmentStatus::PENDIENTE)
                                    ->inline()
                                    ->grouped()
                                    ->disabled(),

                                TextInput::make('xml_path')
                                    ->label('Ruta XML')
                                    ->disabled()
                                    ->prefixIcon('heroicon-m-document-text'),
                            ]),
                    ]),
            ]);
    }
}
