<?php

namespace App\Filament\Resources\Contacts\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ContactForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(3)
                    ->columnSpanFull()
                    ->components([
                        // COLUMNA PRINCIPAL (2/3 del ancho)
                        Section::make('Información Principal')
                            ->description('Identificación y contacto del cliente o suplidor.')
                            ->columnSpan(2)
                            ->components([
                                TextInput::make('name')
                                    ->label('Nombre / Razón Social')
                                    ->required()
                                    ->maxLength(255)
                                    ->prefixIcon('heroicon-m-user')
                                    ->columnSpanFull(),

                                Grid::make(2)
                                    ->components([
                                        Select::make('document_type')
                                            ->label('Tipo de Documento')
                                            ->options([
                                                'rnc' => 'RNC',
                                                'cedula' => 'Cédula',
                                            ])
                                            ->default('rnc')
                                            ->required()
                                            ->prefixIcon('heroicon-m-identification')
                                            ->live(),

                                        TextInput::make('tax_id')
                                            ->label(fn ($get) => $get('document_type') === 'cedula' ? 'Cédula' : 'RNC')
                                            ->placeholder(fn ($get) => $get('document_type') === 'cedula' ? '001-0000000-0' : '101-00000-0')
                                            ->required()
                                            ->maxLength(20)
                                            ->prefixIcon('heroicon-m-hashtag'),
                                    ]),

                                Grid::make(2)
                                    ->components([
                                        TextInput::make('email')
                                            ->label('Correo Electrónico')
                                            ->email()
                                            ->prefixIcon('heroicon-m-envelope'),
                                        
                                        TextInput::make('phone')
                                            ->label('Teléfono')
                                            ->tel()
                                            ->prefixIcon('heroicon-m-phone'),
                                    ]),

                                TextInput::make('address')
                                    ->label('Dirección')
                                    ->prefixIcon('heroicon-m-map-pin')
                                    ->columnSpanFull(),
                            ]),

                        // COLUMNA LATERAL (1/3 del ancho)
                        Section::make('Configuración')
                            ->description('Ajustes específicos del contacto.')
                            ->columnSpan(1)
                            ->components([
                                Toggle::make('is_electronic_receiver')
                                    ->label('Receptor Electrónico')
                                    ->helperText('Activa si el contacto puede recibir e-CF directamente.')
                                    ->default(false)
                                    ->onIcon('heroicon-m-bolt')
                                    ->offIcon('heroicon-m-x-mark')
                                    ->columnSpanFull(),
                                
                                Toggle::make('is_active')
                                    ->label('Contacto Activo')
                                    ->helperText('Un contacto inactivo no se mostrará en los selectores.')
                                    ->default(true)
                                    ->onIcon('heroicon-m-check')
                                    ->offIcon('heroicon-m-no-symbol')
                                    ->columnSpanFull(),
                            ]),
                    ]),
            ]);
    }
}
