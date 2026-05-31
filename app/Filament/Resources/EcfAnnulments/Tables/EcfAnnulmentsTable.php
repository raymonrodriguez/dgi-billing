<?php

namespace App\Filament\Resources\EcfAnnulments\Tables;

use App\Models\EcfAnnulment;
use App\Enums\AnnulmentStatus;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class EcfAnnulmentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->label('Fecha')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                TextColumn::make('type')
                    ->label('Tipo e-CF')
                    ->formatStateUsing(fn (string $state): string => "Tipo {$state}")
                    ->searchable(),
                TextColumn::make('start_sequence')
                    ->label('Desde')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('end_sequence')
                    ->label('Hasta')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('quantity')
                    ->label('Cant.')
                    ->numeric(),
                TextColumn::make('status')
                    ->label('Estatus')
                    ->badge(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
