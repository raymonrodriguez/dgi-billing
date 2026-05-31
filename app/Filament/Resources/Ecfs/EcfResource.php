<?php

namespace App\Filament\Resources\Ecfs;

use App\Filament\Resources\Ecfs\Pages\CreateEcf;
use App\Filament\Resources\Ecfs\Pages\ViewEcf;
use App\Filament\Resources\Ecfs\Pages\ListEcfs;
use App\Filament\Resources\Ecfs\Schemas\EcfForm;
use App\Filament\Resources\Ecfs\Tables\EcfsTable;
use App\Models\Ecf;
use BackedEnum;
use UnitEnum;
use Illuminate\Database\Eloquent\Model;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\RepeatableEntry;
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
            'Estatus' => $record->dgii_status,
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
                Section::make('Datos del Comprobante')
                    ->components([
                        Grid::make(3)
                            ->components([
                                TextEntry::make('encf')
                                    ->label('e-NCF')
                                    ->weight('bold')
                                    ->copyable(),
                                TextEntry::make('issued_at')
                                    ->label('Fecha de Emisión')
                                    ->dateTime('d/m/Y H:i'),
                                TextEntry::make('dgii_status')
                                    ->label('Estatus DGII')
                                    ->badge(),
                            ]),
                    ]),

                Section::make('Cliente')
                    ->components([
                        Grid::make(2)
                            ->components([
                                TextEntry::make('contact.name')
                                    ->label('Razón Social'),
                                TextEntry::make('contact.tax_id')
                                    ->label('RNC / Cédula'),
                            ]),
                    ]),

                Section::make('Detalle de la Factura')
                    ->components([
                        RepeatableEntry::make('items')
                            ->label('')
                            ->schema(
                                fn (Schema $schema): Schema => $schema
                                ->components([
                                    Grid::make(5)
                                        ->components([
                                            TextEntry::make('description')
                                                ->label('Descripción'),
                                            TextEntry::make('quantity')
                                                ->label('Cantidad')
                                                ->numeric(),
                                            TextEntry::make('price')
                                                ->label('Precio Unitario')
                                                ->money('DOP'),
                                            TextEntry::make('itbis_calc')
                                                ->label('ITBIS (18%)')
                                                ->money('DOP')
                                                ->state(fn ($record) => (float) ($record->subtotal * 0.18)),
                                            TextEntry::make('subtotal')
                                                ->label('Total Ítem')
                                                ->money('DOP'),
                                        ]),
                                ])
                            )
                            ->columns(1),
                    ]),

                Section::make('Totales')
                    ->components([
                        Grid::make(3)
                            ->components([
                                TextEntry::make('subtotal_calc')
                                    ->label('Subtotal')
                                    ->money('DOP')
                                    ->state(fn (Ecf $record): float => (float) ($record->total_amount - $record->total_tax)),
                                TextEntry::make('total_tax')
                                    ->label('Total ITBIS (18%)')
                                    ->money('DOP'),
                                TextEntry::make('total_amount')
                                    ->label('TOTAL')
                                    ->weight('bold')
                                    ->money('DOP'),
                            ]),
                    ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            // Podemos dejar las relaciones vacías si el infolist ya lo cubre todo
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEcfs::route('/'),
            'create' => CreateEcf::route('/create'),
            'view' => ViewEcf::route('/{record}'),
        ];
    }
}
