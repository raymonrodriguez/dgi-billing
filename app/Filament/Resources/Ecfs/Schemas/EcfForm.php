<?php

namespace App\Filament\Resources\Ecfs\Schemas;

use App\Enums\EcfStatus;
use App\Enums\ItbisIndicator;
use App\Enums\AdditionalTaxCode;
use App\Enums\Currency;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Components\Repeater;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class EcfForm
{
    public static function configure(Schema $schema): Schema
    {
        // Lógica de bloqueo: Solo se bloquea si hay un estatus y NO es PENDIENTE o ERROR.
        // En creación, el estatus es null o PENDIENTE, por lo que NO se bloquea.
        $isLocked = function ($get) {
            $status = $get('dgii_status');

            if (!$status) {
                return false;
            }

            // Manejar tanto el objeto Enum como el valor string por seguridad
            $statusValue = $status instanceof EcfStatus ? $status : EcfStatus::tryFrom($status);

            return !in_array($statusValue, [EcfStatus::PENDIENTE, EcfStatus::ERROR]);
        };

        return $schema
            ->components([
                Section::make('Información del Comprobante')
                    ->description('Selecciona el cliente y define los datos fiscales básicos.')
                    ->columnSpanFull()
                    ->components([
                        Grid::make(3)->components([
                            Select::make('contact_id')
                                ->label('Cliente / Receptor')
                                ->relationship('contact', 'name')
                                ->required()
                                ->searchable()
                                ->preload()
                                ->prefixIcon('heroicon-m-user')
                                ->columnSpan(2)
                                ->live()
                                ->afterStateUpdated(fn ($state, $set) => $set('type', null))
                                ->disabled($isLocked),

                            TextInput::make('encf')
                                ->label('e-NCF')
                                ->unique(ignoreRecord: true)
                                ->prefixIcon('heroicon-m-hashtag')
                                ->placeholder('Generado automáticamente')
                                ->readOnly()
                                ->dehydrated(false),
                        ]),

                        Grid::make(2)->components([
                            Select::make('type')
                                ->label('Tipo de e-CF')
                                ->options(function (callable $get) {
                                    $contactId = $get('contact_id');
                                    if (!$contactId) {
                                        return [];
                                    }
                                    $contact = \App\Models\Contact::find($contactId);
                                    if (!$contact) {
                                        return [];
                                    }

                                    if ($contact->document_type === 'cedula') {
                                        return ['32' => 'Factura de Consumo Electrónica (B2C)'];
                                    }

                                    return [
                                        '31' => 'Factura de Crédito Fiscal Electrónica (B2B)',
                                        '32' => 'Factura de Consumo Electrónica',
                                        '33' => 'Nota de Débito Electrónica',
                                        '34' => 'Nota de Crédito Electrónica',
                                        '41' => 'Compras Electrónica',
                                        '44' => 'Regímenes Especiales Electrónica',
                                        '45' => 'Gubernamental Electrónica',
                                    ];
                                })
                                ->required()
                                ->prefixIcon('heroicon-m-document-text')
                                ->live()
                                ->afterStateUpdated(function ($state, callable $set) {
                                    if (!$state) {
                                        $set('encf', null);
                                        return;
                                    }
                                    $tenant = \Filament\Facades\Filament::getTenant();
                                    $sequence = \App\Models\EcfSequence::where('company_id', $tenant?->id)
                                        ->where('type', $state)
                                        ->where('is_active', true)
                                        ->first();

                                    if ($sequence) {
                                        $formattedSequence = str_pad((string) $sequence->current_sequence, 10, '0', STR_PAD_LEFT);
                                        $set('encf', "E{$state}{$formattedSequence} (Preview)");
                                    } else {
                                        $set('encf', 'Secuencia no disponible');
                                    }
                                })
                                ->disabled($isLocked),

                            Select::make('income_type')
                                ->label('Tipo de Ingresos')
                                ->options([
                                    '01' => '01 - Ingresos por Operaciones',
                                    '02' => '02 - Ingresos Financieros',
                                    '03' => '03 - Ingresos Extraordinarios',
                                    '04' => '04 - Ingresos por Arrendamientos',
                                    '05' => '05 - Ingresos por Venta de Activo Fijo',
                                    '06' => '06 - Otros Ingresos',
                                ])
                                ->required()
                                ->default('01')
                                ->prefixIcon('heroicon-m-currency-dollar')
                                ->visible(fn ($get) => in_array($get('type'), ['31', '44', '45']))
                                ->disabled($isLocked),
                        ]),
                    ]),

                Section::make('Moneda y Tasa de Cambio')
                    ->description('Configura la moneda de facturación.')
                    ->columnSpanFull()
                    ->components([
                        Grid::make(2)->components([
                            Select::make('currency')
                                ->label('Moneda')
                                ->options(Currency::class)
                                ->required()
                                ->default(Currency::DOP->value)
                                ->prefixIcon('heroicon-m-currency-dollar')
                                ->live()
                                ->afterStateUpdated(fn ($set, $get) => self::updateInvoiceTotals($set, $get))
                                ->disabled($isLocked),

                            TextInput::make('exchange_rate')
                                ->label('Tasa de Cambio')
                                ->required()
                                ->numeric()
                                ->default(1.0000)
                                ->prefixIcon('heroicon-m-arrows-right-left')
                                ->visible(fn ($get) => $get('currency') !== Currency::DOP->value)
                                ->helperText('Indica el valor de 1 moneda extranjera en DOP.')
                                ->live()
                                ->afterStateUpdated(fn ($set, $get) => self::updateInvoiceTotals($set, $get))
                                ->disabled($isLocked),
                        ]),
                    ]),

                Section::make('Información de Referencia y Exenciones')
                    ->description('Requerido para notas de crédito/débito y regímenes especiales.')
                    ->columnSpanFull()
                    ->visible(fn ($get) => in_array($get('type'), ['33', '34', '44']))
                    ->components([
                        Grid::make(2)->components([
                            TextInput::make('modified_ncf')
                                ->label('e-NCF Modificado')
                                ->placeholder('E310000000001')
                                ->required(fn ($get) => in_array($get('type'), ['33', '34']))
                                ->prefixIcon('heroicon-m-arrow-path-rounded-square')
                                ->visible(fn ($get) => in_array($get('type'), ['33', '34']))
                                ->disabled($isLocked),

                            TextInput::make('exemption_id')
                                ->label('Registro Exención / Carnet')
                                ->placeholder('Ej: 123456')
                                ->required(fn ($get) => $get('type') === '44')
                                ->prefixIcon('heroicon-m-shield-check')
                                ->visible(fn ($get) => $get('type') === '44')
                                ->disabled($isLocked),
                        ]),
                    ]),

                Section::make('Detalle de Bienes y Servicios')
                    ->description('Añade los productos o servicios que componen la factura.')
                    ->columnSpanFull()
                    ->components([
                        Repeater::make('items')
                            ->relationship('items')
                            ->label('Ítems de la Factura')
                            ->schema([
                                TextInput::make('description')
                                    ->label('Descripción')
                                    ->required()
                                    ->maxLength(255)
                                    ->columnSpanFull()
                                    ->disabled($isLocked),

                                Grid::make(3)->schema([
                                    TextInput::make('quantity')
                                        ->label('Cantidad')
                                        ->required()
                                        ->numeric()
                                        ->default(1)
                                        ->live(onBlur: true)
                                        ->afterStateUpdated(fn ($set, $get) => self::calculateItemSubtotal($set, $get))
                                        ->disabled($isLocked),

                                    TextInput::make('price')
                                        ->label('Precio Unitario')
                                        ->required()
                                        ->numeric()
                                        ->prefix('RD$')
                                        ->live(onBlur: true)
                                        ->afterStateUpdated(fn ($set, $get) => self::calculateItemSubtotal($set, $get))
                                        ->disabled($isLocked),

                                    TextInput::make('subtotal')
                                        ->label('Total Línea (Inc. Impuestos)')
                                        ->required()
                                        ->numeric()
                                        ->prefix('RD$')
                                        ->readOnly(),
                                ]),

                                Grid::make(2)->schema([
                                    Select::make('billing_indicator')
                                        ->label('Indicador ITBIS')
                                        ->options(ItbisIndicator::class)
                                        ->required()
                                        ->default(ItbisIndicator::ITBIS_18->value)
                                        ->live()
                                        ->afterStateUpdated(fn ($set, $get) => self::calculateItemSubtotal($set, $get))
                                        ->disabled($isLocked),

                                    Select::make('additional_taxes')
                                        ->label('Impuestos Adicionales')
                                        ->multiple()
                                        ->options(AdditionalTaxCode::class)
                                        ->searchable()
                                        ->preload()
                                        ->live()
                                        ->afterStateUpdated(fn ($set, $get) => self::calculateItemSubtotal($set, $get))
                                        ->disabled($isLocked),
                                ]),
                            ])
                            ->itemLabel(fn (array $state): ?string => $state['description'] ?? null)
                            ->reorderableWithButtons()
                            ->collapsible()
                            ->cloneable()
                            ->afterStateUpdated(fn ($set, $get) => self::updateInvoiceTotals($set, $get))
                            ->addable(fn ($get) => !$isLocked($get))
                            ->deletable(fn ($get) => !$isLocked($get)),
                    ]),

                Section::make('Totales y Estatus')
                    ->description('Resumen económico y control de tiempos.')
                    ->columnSpanFull()
                    ->components([
                        Grid::make(3)->components([
                            TextInput::make('total_tax')
                                ->label('Total ITBIS (18%/16%)')
                                ->required()
                                ->numeric()
                                ->prefix('RD$')
                                ->prefixIcon('heroicon-m-calculator')
                                ->readOnly(),

                            TextInput::make('total_additional_taxes')
                                ->label('Total Otros Impuestos')
                                ->required()
                                ->numeric()
                                ->prefix('RD$')
                                ->prefixIcon('heroicon-m-plus-circle')
                                ->readOnly(),

                            TextInput::make('total_amount')
                                ->label('TOTAL NETO')
                                ->required()
                                ->numeric()
                                ->prefix('RD$')
                                ->prefixIcon('heroicon-m-banknotes')
                                ->readOnly(),

                            ToggleButtons::make('dgii_status')
                                ->label('Estado DGII')
                                ->options(EcfStatus::class)
                                ->default(EcfStatus::PENDIENTE)
                                ->inline()
                                ->grouped()
                                ->disabled()
                                ->columnSpanFull(),

                            DateTimePicker::make('issued_at')
                                ->label('Fecha de Emisión')
                                ->required()
                                ->default(now())
                                ->prefixIcon('heroicon-m-calendar-days')
                                ->native(false)
                                ->displayFormat('d/m/Y H:i')
                                ->disabled($isLocked),

                            TextInput::make('track_id')
                                ->label('Track ID')
                                ->placeholder('Generado al enviar')
                                ->disabled()
                                ->prefixIcon('heroicon-m-signal'),
                        ]),
                    ]),
            ]);
    }

    public static function calculateItemSubtotal($set, $get): void
    {
        $quantity = (float) ($get('quantity') ?? 0);
        $price = (float) ($get('price') ?? 0);

        $baseAmount = $quantity * $price;

        // 1. Calcular ITBIS del ítem
        $itbisRate = match ($get('billing_indicator') ?? '1') {
            '1' => 0.18,
            '2' => 0.16,
            default => 0.00,
        };
        $itbisAmount = round($baseAmount * $itbisRate, 2);

        // 2. Calcular Impuestos Adicionales del ítem
        $additionalAmount = 0;
        if (!empty($get('additional_taxes'))) {
            foreach ($get('additional_taxes') as $taxCode) {
                $taxRate = match ($taxCode) {
                    '001' => 0.10, // Propina Legal
                    '002' => 0.02, // CDT
                    '003' => 0.16, // Seguros
                    default => 0.00,
                };
                $additionalAmount += round($baseAmount * $taxRate, 2);
            }
        }

        // El Subtotal que ve el usuario es el Total de la Línea
        $set('subtotal', round($baseAmount + $itbisAmount + $additionalAmount, 2));

        self::updateInvoiceTotals($set, $get);
    }

    public static function updateInvoiceTotals($set, $get): void
    {
        $items = $get('items') ?? [];
        $grandTotal = 0;
        $totalItbis = 0;
        $totalAdditional = 0;

        foreach ($items as $item) {
            $lineTotal = (float) ($item['subtotal'] ?? 0);
            $grandTotal += $lineTotal;

            $price = (float) ($item['price'] ?? 0);
            $qty = (float) ($item['quantity'] ?? 0);
            $base = $price * $qty;

            // Recalcular ITBIS para el pie
            $itbisRate = match ($item['billing_indicator'] ?? '1') {
                '1' => 0.18,
                '2' => 0.16,
                default => 0.00,
            };
            $totalItbis += round($base * $itbisRate, 2);

            // Recalcular Adicionales para el pie
            if (!empty($item['additional_taxes'])) {
                foreach ($item['additional_taxes'] as $taxCode) {
                    $taxRate = match ($taxCode) {
                        '001' => 0.10,
                        '002' => 0.02,
                        '003' => 0.16,
                        default => 0.00,
                    };
                    $totalAdditional += round($base * $taxRate, 2);
                }
            }
        }

        $set('total_amount', round($grandTotal, 2));
        $set('total_tax', round($totalItbis, 2));
        $set('total_additional_taxes', round($totalAdditional, 2));
    }
}
