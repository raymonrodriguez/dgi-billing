<?php

namespace App\Filament\Resources\Ecfs;

use App\Filament\Resources\Ecfs\Pages\CreateEcf;
use App\Filament\Resources\Ecfs\Pages\EditEcf;
use App\Filament\Resources\Ecfs\Pages\ViewEcf;
use App\Filament\Resources\Ecfs\Pages\ListEcfs;
use App\Filament\Resources\Ecfs\Schemas\EcfForm;
use App\Filament\Resources\Ecfs\Tables\EcfsTable;
use App\Models\Ecf;
use App\Enums\Currency;
use BackedEnum;
use UnitEnum;
use Illuminate\Database\Eloquent\Model;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\KeyValueEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;

class EcfResource extends Resource
{
    protected static ?string $model = Ecf::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-document-currency-dollar';

    protected static string|UnitEnum|null $navigationGroup = 'Facturación';

    protected static ?string $recordTitleAttribute = 'encf';

    public static function getGloballySearchableAttributes(): array
    {
        return ['encf', 'contact.tax_id'];
    }

    public static function getGlobalSearchResultTitle(Model $record): string
    {
        return $record->encf;
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Cliente' => $record->contact->name,
            'Estatus' => $record->dgii_status->value,
        ];
    }

    public static function form(Schema $schema): Schema
    {
        return EcfForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EcfsTable::configure($table);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                // 1. SECCIÓN: IDENTIDAD DE LA FACTURA
                Section::make('1. Identificación Fiscal')
                    ->icon('heroicon-m-identification')
                    ->columnSpanFull()
                    ->components([
                        Grid::make(4)
                            ->components([
                                TextEntry::make('encf')
                                    ->label('e-NCF')
                                    ->weight('black')
                                    ->size('xl')
                                    ->color('primary')
                                    ->fontFamily('mono')
                                    ->copyable(),

                                TextEntry::make('type')
                                    ->label('Tipo Comprobante')
                                    ->badge()
                                    ->formatStateUsing(fn (string $state): string => match ($state) {
                                        '31' => '31 - Crédito Fiscal',
                                        '32' => '32 - Consumo',
                                        '33' => '33 - Nota de Débito',
                                        '34' => '34 - Nota de Crédito',
                                        default => "Tipo {$state}",
                                    }),

                                TextEntry::make('dgii_status')
                                    ->label('Estado DGII')
                                    ->badge(),

                                TextEntry::make('issued_at')
                                    ->label('Fecha Emisión')
                                    ->dateTime('d/m/Y H:i'),
                            ]),
                    ]),

                // 2. SECCIÓN: EMISOR Y RECEPTOR
                Grid::make(2)
                    ->columnSpanFull()
                    ->components([
                        Section::make('2. Datos del Emisor (Tu Empresa)')
                            ->icon('heroicon-m-building-office')
                            ->components([
                                TextEntry::make('company.company_name')
                                    ->label('Razón Social')
                                    ->weight('bold'),
                                TextEntry::make('company.tax_id')
                                    ->label('RNC')
                                    ->fontFamily('mono'),
                            ]),

                        Section::make('3. Datos del Receptor (Cliente)')
                            ->icon('heroicon-m-user')
                            ->components([
                                TextEntry::make('contact.name')
                                    ->label('Nombre / Razón Social')
                                    ->weight('bold'),
                                TextEntry::make('contact.tax_id')
                                    ->label('RNC / Cédula')
                                    ->fontFamily('mono'),
                            ]),
                    ]),

                // 3. SECCIÓN: CONTEXTO FINANCIERO
                Section::make('4. Información Financiera')
                    ->icon('heroicon-m-currency-dollar')
                    ->columnSpanFull()
                    ->components([
                        Grid::make(3)
                            ->components([
                                TextEntry::make('currency')
                                    ->label('Moneda')
                                    ->badge(),
                                TextEntry::make('exchange_rate')
                                    ->label('Tasa de Cambio')
                                    ->numeric(4)
                                    ->visible(fn ($record) => $record->currency !== Currency::DOP),
                                TextEntry::make('income_type')
                                    ->label('Tipo de Ingreso')
                                    ->placeholder('Operaciones Gravadas'),
                            ]),
                    ]),

                // 4. SECCIÓN: DETALLE DE ÍTEMS (TABLA MEJORADA)
                Section::make('5. Detalle de Bienes y Servicios')
                    ->description('Desglose de productos y servicios facturados.')
                    ->icon('heroicon-m-list-bullet')
                    ->columnSpanFull()
                    ->components([
                        RepeatableEntry::make('items')
                            ->hiddenLabel()
                            ->schema(
                                fn (Schema $schema): Schema => $schema
                                ->components([
                                    Grid::make(12)
                                        ->components([
                                            TextEntry::make('description')
                                                ->label('Descripción del Producto/Servicio')
                                                ->weight('bold')
                                                ->columnSpan(6),

                                            TextEntry::make('billing_indicator')
                                                ->label('ITBIS')
                                                ->badge()
                                                ->columnSpan(1),

                                            TextEntry::make('quantity')
                                                ->label('Cant.')
                                                ->numeric()
                                                ->alignEnd()
                                                ->columnSpan(1),

                                            TextEntry::make('price')
                                                ->label('Precio')
                                                ->money('DOP')
                                                ->alignEnd()
                                                ->columnSpan(2),

                                            TextEntry::make('subtotal')
                                                ->label('Total Ítem')
                                                ->money('DOP')
                                                ->weight('black')
                                                ->alignEnd()
                                                ->color('primary')
                                                ->columnSpan(2),
                                        ]),
                                ])
                            )
                            ->columns(1),
                    ]),

                // 5. SECCIÓN: TOTALES
                Section::make('6. Resumen Económico')
                    ->icon('heroicon-m-banknotes')
                    ->columnSpanFull()
                    ->components([
                        Grid::make(3)
                            ->components([
                                TextEntry::make('subtotal_calc')
                                    ->label('Monto Subtotal')
                                    ->money('DOP')
                                    ->state(fn (Ecf $record): float => (float) ($record->total_amount - $record->total_tax)),

                                TextEntry::make('total_tax')
                                    ->label('ITBIS Total (18%)')
                                    ->money('DOP'),

                                TextEntry::make('total_amount')
                                    ->label('TOTAL NETO A PAGAR')
                                    ->money('DOP')
                                    ->weight('black')
                                    ->size('xl')
                                    ->color('primary'),
                            ]),
                    ]),

                // 6. SECCIÓN: HISTORIAL
                Section::make('7. Historial de Cambios y Eventos')
                    ->description('Bitácora completa de actividad.')
                    ->icon('heroicon-m-clock')
                    ->columnSpanFull()
                    ->collapsible()
                    ->components([
                        RepeatableEntry::make('all_activity_logs')
                            ->hiddenLabel()
                            ->schema(
                                fn (Schema $schema): Schema => $schema
                                ->components([
                                    Grid::make(4)
                                        ->components([
                                            TextEntry::make('created_at')
                                                ->label('Fecha/Hora')
                                                ->dateTime('d/m/Y H:i:s'),
                                            TextEntry::make('action')
                                                ->label('Evento')
                                                ->badge()
                                                ->formatStateUsing(fn ($state) => match ($state) {
                                                    'create' => 'Creado',
                                                    'update' => 'Actualizado',
                                                    'delete' => 'Eliminado',
                                                    default => $state,
                                                })
                                                ->color(fn ($state) => match ($state) {
                                                    'create' => 'success',
                                                    'update' => 'warning',
                                                    'delete' => 'danger',
                                                    default => 'info',
                                                }),
                                            TextEntry::make('description')
                                                ->label('Descripción')
                                                ->size('sm')
                                                ->color('gray'),
                                            TextEntry::make('user_id')
                                                ->label('Responsable')
                                                ->formatStateUsing(function ($state) {
                                                    if ($state === 'system') {
                                                        return 'Sistema';
                                                    }
                                                    if (is_numeric($state)) {
                                                        $user = \App\Models\User::find($state);
                                                        return $user ? $user->name : "Usuario #{$state}";
                                                    }
                                                    return $state;
                                                }),
                                            TextEntry::make('changes')
                                                ->label('Detalle de Cambios')
                                                ->formatStateUsing(function ($state, $record) {
                                                    if (!$state || !is_array($state)) {
                                                        return null;
                                                    }

                                                    $fieldMap = [
                                                        'total_amount' => 'Monto Total',
                                                        'total_tax' => 'ITBIS Total',
                                                        'contact_id' => 'Cliente',
                                                        'dgii_status' => 'Estado DGII',
                                                        'exchange_rate' => 'Tasa de Cambio',
                                                        'currency' => 'Moneda',
                                                        'description' => 'Descripción',
                                                        'quantity' => 'Cantidad',
                                                        'price' => 'Precio Unit.',
                                                        'discount' => 'Descuento',
                                                        'subtotal' => 'Monto Neto',
                                                        'billing_indicator' => 'Indicador ITBIS',
                                                        'additional_taxes' => 'Impuestos Adicionales',
                                                        'modified_ncf' => 'e-NCF Modificado',
                                                        'exemption_id' => 'Registro Exención',
                                                        'income_type' => 'Tipo de Ingreso',
                                                        'encf' => 'e-NCF',
                                                        'track_id' => 'Track ID',
                                                    ];

                                                    $formattedLines = [];
                                                    $isUpdate = $record->action === 'update';

                                                    foreach ($state as $fieldKey => $data) {
                                                        if (in_array($fieldKey, ['updated_at', 'created_at', 'deleted_at'])) {
                                                            continue;
                                                        }

                                                        $label = $fieldMap[$fieldKey] ?? ucfirst(str_replace('_', ' ', $fieldKey));

                                                        // Caso: Actualización (Antes -> Después)
                                                        if ($isUpdate && is_array($data) && (isset($data['old']) || isset($data['new']))) {
                                                            $old = $data['old'];
                                                            $new = $data['new'];

                                                            if (str_contains($fieldKey, 'amount') || str_contains($fieldKey, 'tax') || $fieldKey === 'price' || $fieldKey === 'subtotal') {
                                                                $old = is_numeric($old) ? 'RD$ ' . number_format((float)$old, 2) : $old;
                                                                $new = is_numeric($new) ? 'RD$ ' . number_format((float)$new, 2) : $new;
                                                            }

                                                            $oldStr = empty($old) && $old !== 0 ? '<Vacío>' : (is_array($old) ? json_encode($old) : $old);
                                                            $newStr = empty($new) && $new !== 0 ? '<Vacío>' : (is_array($new) ? json_encode($new) : $new);

                                                            $formattedLines[] = "• **{$label}**: {$oldStr} → **{$newStr}**";
                                                        }
                                                        // Caso: Creación o Valor Simple (Valor inicial)
                                                        else {
                                                            $val = $data;
                                                            if (str_contains($fieldKey, 'amount') || str_contains($fieldKey, 'tax') || $fieldKey === 'price' || $fieldKey === 'subtotal') {
                                                                $val = is_numeric($val) ? 'RD$ ' . number_format((float)$val, 2) : $val;
                                                            }
                                                            $valStr = is_array($val) ? json_encode($val) : $val;
                                                            if (empty($valStr) && $valStr !== 0 && $valStr !== '0') {
                                                                continue;
                                                            }

                                                            $formattedLines[] = "• **{$label}**: **{$valStr}**";
                                                        }
                                                    }

                                                    return implode("\n", $formattedLines);
                                                })
                                                ->markdown()
                                                ->prose()
                                                ->visible(fn ($state) => !empty($state)),
                                        ]),
                                ])
                            )
                            ->columns(1),
                    ]),

                // 7. SECCIÓN: DATOS DGII
                Section::make('8. Información Técnica DGII')
                    ->icon('heroicon-m-shield-check')
                    ->columnSpanFull()
                    ->collapsible()
                    ->collapsed()
                    ->components([
                        Grid::make(3)
                            ->components([
                                TextEntry::make('track_id')
                                    ->label('Tracking ID')
                                    ->fontFamily('mono')
                                    ->copyable(),

                                TextEntry::make('security_code')
                                    ->label('Código de Seguridad')
                                    ->fontFamily('mono'),

                                KeyValueEntry::make('dgii_response.log')
                                    ->label('Log de Respuesta'),
                            ]),
                    ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEcfs::route('/'),
            'create' => CreateEcf::route('/create'),
            'edit' => EditEcf::route('/{record}/edit'),
            'view' => ViewEcf::route('/{record}'),
        ];
    }
}
