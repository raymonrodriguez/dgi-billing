<?php

namespace App\Filament\Provider\Resources\Companies\Schemas;

use Filament\Forms\Components\Select;
use App\Enums\DgiiEnvironment;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ToggleButtons;
use Filament\Actions\Action;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CompanyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Identificación Fiscal y Comercial')
                    ->description('Datos legales de la empresa emisora.')
                    ->icon('heroicon-m-building-office-2')
                    ->columnSpanFull()
                    ->components([
                        Grid::make(3)->components([
                            FileUpload::make('logo')
                                ->label('Logo de la Empresa')
                                ->image()
                                ->imageEditor()
                                ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/svg+xml', 'image/jpg'])
                                ->directory('company-logos')
                                ->disk('public')
                                ->visibility('public')
                                ->maxSize(1024)
                                ->columnSpan(1),

                            Grid::make(1)->components([
                                TextInput::make('company_name')
                                    ->label('Razón Social')
                                    ->required()
                                    ->placeholder('Ej: Mi Empresa S.R.L.')
                                    ->prefixIcon('heroicon-m-building-library'),

                                TextInput::make('tax_id')
                                    ->label('RNC')
                                    ->required()
                                    ->prefixIcon('heroicon-m-hashtag')
                                    ->placeholder('101000001')
                                    ->minLength(9)
                                    ->maxLength(9),
                            ])->columnSpan(2),
                        ]),

                        TextInput::make('trade_name')
                            ->label('Nombre Comercial (Opcional)')
                            ->placeholder('Ej: Mi Tienda Online')
                            ->prefixIcon('heroicon-m-building-storefront')
                            ->columnSpanFull(),
                    ]),

                Section::make('Configuración y Ambiente DGII')
                    ->description('Estado de la empresa y entorno de validación.')
                    ->icon('heroicon-m-cpu-chip')
                    ->columnSpanFull()
                    ->components([
                        Grid::make(2)->components([
                            ToggleButtons::make('environment')
                                ->label('Ambiente de Trabajo')
                                ->inline()
                                ->grouped()
                                ->options(DgiiEnvironment::class)
                                ->required()
                                ->default(DgiiEnvironment::TEST)
                                ->disabled() // Bloqueado por defecto
                                ->hintAction(
                                    Action::make('changeEnvironment')
                                        ->label('Modificar Ambiente')
                                        ->icon('heroicon-m-pencil-square')
                                        ->color('warning')
                                        ->requiresConfirmation()
                                        ->modalHeading('¿Cambiar Ambiente de Facturación?')
                                        ->modalDescription('ADVERTENCIA: Cambiar el ambiente afectará la validez legal de todas las facturas futuras. El paso a PRODUCCIÓN requiere que la empresa esté certificada ante la DGII.')
                                        ->modalSubmitActionLabel('Sí, Cambiar')
                                        ->form([
                                            Select::make('new_environment')
                                                ->label('Selecciona el nuevo ambiente')
                                                ->options(DgiiEnvironment::class)
                                                ->required()
                                        ])
                                        ->action(function (array $data, callable $set) {
                                            $set('environment', $data['new_environment']);
                                        })
                                ),

                            Toggle::make('is_active')
                                ->label('Empresa Habilitada')
                                ->helperText('Solo las empresas activas pueden emitir facturas.')
                                ->default(true)
                                ->onColor('success')
                                ->offColor('danger'),
                        ]),
                    ]),

                Section::make('Seguridad y Firma Electrónica')
                    ->description('Certificados necesarios para la firma de e-CF.')
                    ->icon('heroicon-m-shield-check')
                    ->columnSpanFull()
                    ->components([
                        Grid::make(2)->components([
                            FileUpload::make('certificate')
                                ->label('Certificado Digital (.p12)')
                                ->directory('certificates')
                                ->acceptedFileTypes(['application/x-pkcs12'])
                                ->preserveFilenames()
                                ->required(fn (string $operation): bool => $operation === 'create')
                                ->helperText('Sube el archivo proporcionado por tu certificadora de firma digital.'),

                            TextInput::make('cert_password')
                                ->label('Clave del Certificado')
                                ->password()
                                ->required(fn (string $operation): bool => $operation === 'create')
                                ->prefixIcon('heroicon-m-key')
                                ->dehydrated(fn ($state) => filled($state))
                                ->helperText('La contraseña necesaria para abrir el archivo .p12.'),
                        ]),
                    ]),

                Section::make('Ubicación y Contacto')
                    ->description('Datos informativos para la representación impresa (PDF).')
                    ->icon('heroicon-m-map-pin')
                    ->columnSpanFull()
                    ->components([
                        Grid::make(2)->components([
                            TextInput::make('email')
                                ->label('Correo de Notificaciones')
                                ->email()
                                ->prefixIcon('heroicon-m-envelope'),

                            TextInput::make('phone')
                                ->label('Teléfono de Contacto')
                                ->tel()
                                ->prefixIcon('heroicon-m-phone'),

                            TextInput::make('address')
                                ->label('Dirección Física')
                                ->placeholder('Av. Winston Churchill #123, Santo Domingo, RD')
                                ->prefixIcon('heroicon-m-map')
                                ->columnSpanFull(),
                        ]),
                    ]),
            ]);
    }
}
