<?php

namespace App\Filament\Resources\EcfSequences\Tables;

use App\Filament\Resources\Ecfs\EcfResource;
use App\Models\EcfSequence;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class EcfSequencesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('type')
                    ->label('Tipo e-CF')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        '31' => '31 - Crédito Fiscal',
                        '32' => '32 - Consumo',
                        '33' => '33 - Nota de Débito',
                        '34' => '34 - Nota de Crédito',
                        '41' => '41 - Compras',
                        '43' => '43 - Gastos Menores',
                        '44' => '44 - Regímenes Especiales',
                        '45' => '45 - Gubernamental',
                        '46' => '46 - Exportaciones',
                        '47' => '47 - Pagos al Exterior',
                        default => $state,
                    })
                    ->searchable()
                    ->sortable(),
                TextColumn::make('description')
                    ->label('Descripción')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('start_range')
                    ->label('Desde')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('end_range')
                    ->label('Hasta')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('current_sequence')
                    ->label('Secuencia Actual')
                    ->numeric()
                    ->sortable()
                    ->weight('bold')
                    ->color('primary'),
                TextColumn::make('expiration_date')
                    ->label('Vencimiento')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->color(fn ($record) => $record->expiration_date->isPast() ? 'danger' : 'gray'),
                IconColumn::make('is_active')
                    ->label('Activo')
                    ->boolean(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ActionGroup::make([
                    EditAction::make(),

                    Action::make('viewInvoices')
                        ->label('Ver Facturas')
                        ->icon('heroicon-o-document-text')
                        ->color('info')
                        ->url(fn (EcfSequence $record): string => EcfResource::getUrl('index', [
                            'tableFilters[type][value]' => $record->type,
                        ])),
                ])
                ->icon('heroicon-m-ellipsis-vertical')
                ->tooltip('Acciones'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
