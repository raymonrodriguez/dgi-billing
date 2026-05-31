<?php

namespace App\Filament\Resources\EcfSequences\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class EcfSequenceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(3)
                    ->columnSpanFull()
                    ->components([
                        Section::make('Rango de Secuencias')
                            ->description('Define los límites del talonario otorgado por la DGII.')
                            ->columnSpan(2)
                            ->schema([
                                Select::make('type')
                                    ->label('Tipo e-CF')
                                    ->options([
                                        '31' => '31 - Crédito Fiscal',
                                        '32' => '32 - Consumo',
                                        '33' => '33 - Nota de Débito',
                                        '34' => '34 - Nota de Crédito',
                                    ])
                                    ->required()
                                    ->prefixIcon('heroicon-m-tag'),

                                TextInput::make('description')
                                    ->label('Descripción Interna')
                                    ->prefixIcon('heroicon-m-pencil-square'),

                                Grid::make(3)->schema([
                                    TextInput::make('start_range')
                                        ->label('e-NCF Desde')
                                        ->required()
                                        ->numeric()
                                        ->prefixIcon('heroicon-m-arrow-right-start-on-rectangle'),

                                    TextInput::make('end_range')
                                        ->label('e-NCF Hasta')
                                        ->required()
                                        ->numeric()
                                        ->prefixIcon('heroicon-m-arrow-left-end-on-rectangle'),

                                    TextInput::make('current_sequence')
                                        ->label('Siguiente Correlativo')
                                        ->required()
                                        ->numeric()
                                        ->prefixIcon('heroicon-m-arrow-trending-up'),
                                ]),
                            ]),

                        Section::make('Validez y Estado')
                            ->columnSpan(1)
                            ->schema([
                                DateTimePicker::make('expiration_date')
                                    ->label('Vencimiento')
                                    ->prefixIcon('heroicon-m-calendar-days'),

                                Toggle::make('is_active')
                                    ->label('Talonario Activo')
                                    ->helperText('Solo un talonario activo por tipo.')
                                    ->default(true)
                                    ->onIcon('heroicon-m-check')
                                    ->offIcon('heroicon-m-x-mark'),
                            ]),
                    ]),
            ]);
    }
}
