<?php

namespace App\Filament\Resources\Ecfs\RelationManagers;

use App\Enums\AdditionalTaxCode;
use App\Enums\ItbisIndicator;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Grid;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('description')
                    ->label('Descripción')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),

                Grid::make(3)->components([
                    TextInput::make('quantity')
                        ->label('Cantidad')
                        ->required()
                        ->numeric()
                        ->default(1)
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn ($state, $set, $get) => $set('subtotal', ($state * $get('price')) - $get('discount'))),

                    TextInput::make('price')
                        ->label('Precio Unitario')
                        ->required()
                        ->numeric()
                        ->prefix('RD$')
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn ($state, $set, $get) => $set('subtotal', ($state * $get('quantity')) - $get('discount'))),

                    TextInput::make('discount')
                        ->label('Descuento')
                        ->numeric()
                        ->default(0)
                        ->prefix('RD$')
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn ($state, $set, $get) => $set('subtotal', ($get('quantity') * $get('price')) - $state)),
                ]),

                Grid::make(2)->components([
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

                TextInput::make('subtotal')
                    ->label('Subtotal')
                    ->required()
                    ->numeric()
                    ->prefix('RD$')
                    ->readOnly()
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('description')
            ->columns([
                TextColumn::make('description')
                    ->label('Descripción')
                    ->searchable(),
                TextColumn::make('quantity')
                    ->label('Cant.')
                    ->numeric(),
                TextColumn::make('price')
                    ->label('Precio')
                    ->money('DOP'),
                TextColumn::make('billing_indicator')
                    ->label('ITBIS')
                    ->badge(),
                TextColumn::make('subtotal')
                    ->label('Subtotal')
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
