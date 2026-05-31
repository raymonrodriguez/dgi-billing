<?php

namespace App\Filament\Resources\Companies\Schemas;

use App\Enums\DgiiEnvironment;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CompanyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Identificación de la Empresa')
                    ->description('Información fiscal y comercial de tu negocio.')
                    ->components([
                        Grid::make(2)->components([
                            TextInput::make('company_name')
                                ->label('Razón Social')
                                ->required()
                                ->prefixIcon('heroicon-m-building-library'),

                            TextInput::make('trade_name')
                                ->label('Nombre Comercial')
                                ->prefixIcon('heroicon-m-building-storefront'),
                        ]),
                        TextInput::make('tax_id')
                            ->label('RNC')
                            ->required()
                            ->prefixIcon('heroicon-m-hashtag')
                            ->minLength(9)
                            ->maxLength(9)
                            ->helperText('El RNC debe contener 9 dígitos.'),
                    ]),

                Section::make('Configuración DGII')
                    ->description('Define el entorno de comunicación con la DGII.')
                    ->components([
                        ToggleButtons::make('environment')
                            ->label('Ambiente')
                            ->inline()
                            ->grouped()
                            ->options(DgiiEnvironment::class)
                            ->required()
                            ->default(DgiiEnvironment::TEST),
                    ]),

                Section::make('Certificado Digital')
                    ->description('Credenciales para la firma de documentos electrónicos.')
                    ->components([
                        Grid::make(2)->components([
                            FileUpload::make('certificate')
                                ->label('Archivo .p12')
                                ->directory('certificates')
                                ->acceptedFileTypes(['application/x-pkcs12'])
                                ->preserveFilenames()
                                ->helperText('Sube el archivo proporcionado por tu certificadora.'),

                            TextInput::make('cert_password')
                                ->label('Contraseña del Certificado')
                                ->password()
                                ->prefixIcon('heroicon-m-key')
                                ->dehydrated(fn ($state) => filled($state))
                                ->helperText('La clave para usar el certificado.'),
                        ]),
                    ]),

                Section::make('Estado')
                    ->components([
                        Toggle::make('is_active')
                            ->label('Empresa Activa')
                            ->helperText('Una empresa inactiva no podrá emitir facturas.')
                            ->default(true),
                    ]),
            ]);
    }
}
