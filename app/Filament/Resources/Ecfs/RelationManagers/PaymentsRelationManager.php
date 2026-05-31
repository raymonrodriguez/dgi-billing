<?php

namespace App\Filament\Resources\Ecfs\RelationManagers;

use Filament\Actions\AssociateAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DissociateAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PaymentsRelationManager extends RelationManager
{
    protected static string $relationship = 'payments';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('method')
                    ->label('Método de Pago')
                    ->options([
                        '01' => 'Efectivo',
                        '02' => 'Cheque',
                        '03' => 'Tarjeta de Crédito/Débito',
                        '04' => 'Transferencia',
                        '07' => 'Crédito',
                    ])
                    ->required(),
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
            ->recordTitleAttribute('method')
            ->columns([
                TextColumn::make('method')
                    ->label('Método')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        '01' => 'Efectivo',
                        '02' => 'Cheque',
                        '03' => 'Tarjeta',
                        '04' => 'Transferencia',
                        '07' => 'Crédito',
                        default => $state,
                    }),
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
