<?php

namespace App\Filament\Resources\Contacts\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ActionGroup;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;

class ContactsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nombre / Razón Social')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->description(fn ($record) => "ID: {$record->tax_id}"),

                TextColumn::make('document_type')
                    ->label('Tipo Doc.')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => strtoupper($state))
                    ->color(fn (string $state): string => match ($state) {
                        'rnc' => 'info',
                        'cedula' => 'gray',
                        default => 'gray',
                    }),

                TextColumn::make('email')
                    ->label('Contacto')
                    ->searchable()
                    ->description(fn ($record) => $record->phone),

                IconColumn::make('is_electronic_receiver')
                    ->label('E-Receptor')
                    ->boolean()
                    ->sortable(),

                IconColumn::make('is_active')
                    ->label('Activo')
                    ->boolean()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Registro')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('document_type')
                    ->label('Tipo de Documento')
                    ->options([
                        'rnc' => 'RNC',
                        'cedula' => 'Cédula',
                    ]),
                
                Filter::make('is_electronic_receiver')
                    ->label('Solo Receptores Electrónicos')
                    ->toggle()
                    ->query(fn ($query) => $query->where('is_electronic_receiver', true)),
            ])
            ->recordActions([
                ActionGroup::make([
                    EditAction::make(),
                ])
                ->icon('heroicon-m-ellipsis-vertical')
                ->tooltip('Acciones')
                ->color('gray'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
