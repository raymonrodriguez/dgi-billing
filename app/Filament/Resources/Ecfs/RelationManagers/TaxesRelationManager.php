<?php

namespace App\Filament\Resources\Ecfs\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TaxesRelationManager extends RelationManager
{
    protected static string $relationship = 'taxes';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('type')
                    ->label('Tipo de Impuesto')
                    ->options([
                        'ITBIS' => 'ITBIS',
                        'ISC' => 'ISC',
                        'Propina' => 'Propina Legal',
                    ])
                    ->required(),
                TextInput::make('rate')
                    ->label('Tasa (%)')
                    ->required()
                    ->numeric()
                    ->default(18),
                TextInput::make('amount')
                    ->label('Monto')
                    ->required()
                    ->numeric()
                    ->prefix('RD$'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('type')
            ->columns([
                TextColumn::make('type')
                    ->label('Tipo'),
                TextColumn::make('rate')
                    ->label('Tasa (%)')
                    ->suffix('%'),
                TextColumn::make('amount')
                    ->label('Monto')
                    ->money('DOP'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
