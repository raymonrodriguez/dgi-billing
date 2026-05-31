<?php

namespace App\Filament\Provider\Resources\Logs;

use App\Filament\Provider\Resources\Logs\Pages\ListLogs;
use App\Models\Log;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;

class LogResource extends Resource
{
    protected static ?string $model = Log::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-cpu-chip';

    protected static string|UnitEnum|null $navigationGroup = 'Soporte Técnico';

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->label('Fecha')
                    ->dateTime('d/m/Y H:i:s')
                    ->sortable(),
                TextColumn::make('model')
                    ->label('Modelo')
                    ->searchable(),
                TextColumn::make('action')
                    ->label('Acción')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'create' => 'success',
                        'update' => 'warning',
                        'delete' => 'danger',
                        default => 'info',
                    }),
                TextColumn::make('user_id')
                    ->label('Usuario ID'),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Detalle del Log')
                    ->components([
                        Grid::make(3)
                            ->components([
                                TextEntry::make('model')->label('Modelo'),
                                TextEntry::make('record_id')->label('ID Registro'),
                                TextEntry::make('action')->label('Acción')->badge(),
                            ]),
                        TextEntry::make('changes')
                            ->label('Detalle de Cambios')
                            ->json()
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListLogs::route('/'),
        ];
    }
}
