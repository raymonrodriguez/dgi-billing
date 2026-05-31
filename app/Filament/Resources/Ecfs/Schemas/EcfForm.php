<?php

namespace App\Filament\Resources\Ecfs\Schemas;

use App\Enums\EcfStatus;
use App\Enums\ItbisIndicator;
use App\Enums\AdditionalTaxCode;
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
                                ->columnSpan(2),
                            
                            TextInput::make('encf')
                                ->label('e-NCF')
                                ->required()
                                ->unique(ignoreRecord: true)
                                ->prefixIcon('heroicon-m-hashtag')
                                ->placeholder('E310000000001'),
                        ]),

                        Select::make('type')
                            ->label('Tipo de e-CF')
                            ->options([
                                '31' => 'Factura de Crédito Fiscal Electrónica',
                                '32' => 'Factura de Consumo Electrónica',
                                '33' => 'Nota de Débito Electrónica',
                                '34' => 'Nota de Crédito Electrónica',
                                '41' => 'Compras Electrónica',
                                '43' => 'Gastos Menores Electrónicos',
                                '44' => 'Regímenes Especiales Electrónica',
                                '45' => 'Gubernamental Electrónica',
                            ])
                            ->required()
                            ->prefixIcon('heroicon-m-document-text'),
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
                                    ->columnSpanFull(),
                                
                                Grid::make(3)->schema([
                                    TextInput::make('quantity')
                                        ->label('Cantidad')
                                        ->required()
                                        ->numeric()
                                        ->default(1)
                                        ->live(onBlur: true)
                                        ->afterStateUpdated(fn ($state, $set, $get) => self::calculateItemSubtotal($set, $get)),
                                    
                                    TextInput::make('price')
                                        ->label('Precio Unitario')
                                        ->required()
                                        ->numeric()
                                        ->prefix('RD$')
                                        ->live(onBlur: true)
                                        ->afterStateUpdated(fn ($state, $set, $get) => self::calculateItemSubtotal($set, $get)),
                                    
                                    TextInput::make('subtotal')
                                        ->label('Subtotal')
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
                                        ->default(ItbisIndicator::ITBIS_18->value),

                                    Select::make('additional_taxes')
                                        ->label('Impuestos Adicionales')
                                        ->multiple()
                                        ->options(AdditionalTaxCode::class)
                                        ->searchable()
                                        ->preload(),
                                ]),
                            ])
                            ->itemLabel(fn (array $state): ?string => $state['description'] ?? null)
                            ->reorderableWithButtons()
                            ->collapsible()
                            ->cloneable()
                            ->afterStateUpdated(fn ($set, $get) => self::updateInvoiceTotals($set, $get)),
                    ]),

                Section::make('Totales y Estatus')
                    ->description('Resumen económico y control de tiempos.')
                    ->columnSpanFull()
                    ->components([
                        Grid::make(2)->components([
                            TextInput::make('total_amount')
                                ->label('Monto Total Bruto')
                                ->required()
                                ->numeric()
                                ->prefix('RD$')
                                ->prefixIcon('heroicon-m-banknotes'),
                            
                            TextInput::make('total_tax')
                                ->label('Total ITBIS')
                                ->required()
                                ->numeric()
                                ->prefix('RD$')
                                ->prefixIcon('heroicon-m-calculator'),

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
                                ->displayFormat('d/m/Y H:i'),
                            
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
        $set('subtotal', round($quantity * $price, 2));
    }

    public static function updateInvoiceTotals($set, $get): void
    {
        $items = $get('items') ?? [];
        $totalAmount = 0;
        $totalTax = 0;

        foreach ($items as $item) {
            $subtotal = (float) ($item['subtotal'] ?? 0);
            $totalAmount += $subtotal;
            
            if (($item['billing_indicator'] ?? '1') === '1') {
                $totalTax += round($subtotal * 0.18 / 1.18, 2);
            }
        }

        $set('total_amount', round($totalAmount, 2));
        $set('total_tax', round($totalTax, 2));
    }
}
