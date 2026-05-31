<?php

namespace App\Filament\Provider\Resources\Users\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Illuminate\Support\Facades\Hash;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Datos del Usuario')
                    ->description('Información básica de identificación.')
                    ->icon('heroicon-m-user-circle')
                    ->components([
                        Grid::make(2)
                            ->components([
                                TextInput::make('name')
                                    ->label('Nombre Completo')
                                    ->required()
                                    ->placeholder('Ej: Juan Pérez')
                                    ->prefixIcon('heroicon-m-user'),

                                TextInput::make('email')
                                    ->label('Correo Electrónico')
                                    ->email()
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->placeholder('correo@ejemplo.com')
                                    ->prefixIcon('heroicon-m-envelope'),
                            ]),
                    ]),

                Section::make('Seguridad y Privilegios')
                    ->description('Configuración de contraseña y nivel de acceso.')
                    ->icon('heroicon-m-shield-check')
                    ->components([
                        Grid::make(2)
                            ->components([
                                TextInput::make('password')
                                    ->label('Contraseña de Acceso')
                                    ->password()
                                    ->prefixIcon('heroicon-m-key')
                                    ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                                    ->dehydrated(fn ($state) => filled($state))
                                    ->required(fn (string $operation): bool => $operation === 'create')
                                    ->helperText('En edición, dejar en blanco para mantener la actual.'),

                                Toggle::make('is_provider')
                                    ->label('Acceso Super Administrador')
                                    ->helperText('Permite gestionar todas las empresas del sistema.')
                                    ->default(false)
                                    ->onColor('danger')
                                    ->inline(false),
                            ]),
                    ]),

                Section::make('Información del Registro')
                    ->icon('heroicon-m-information-circle')
                    ->collapsible()
                    ->collapsed()
                    ->visible(fn ($record) => $record !== null)
                    ->components([
                        Grid::make(2)
                            ->components([
                                TextInput::make('id')
                                    ->label('ID de Usuario')
                                    ->disabled(),

                                TextInput::make('created_at')
                                    ->label('Registrado el')
                                    ->disabled(),
                            ]),
                    ]),
            ]);
    }
}
