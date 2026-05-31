<?php

namespace App\Filament\Provider\Resources\Users\RelationManagers;

use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Actions\AttachAction;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CompaniesRelationManager extends RelationManager
{
    protected static string $relationship = 'companies';

    protected static ?string $recordTitleAttribute = 'company_name';

    protected static ?string $title = 'Empresas Asignadas';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('company_name')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('company_name')
            ->columns([
                TextColumn::make('company_name')
                    ->label('Empresa')
                    ->searchable(),
                TextColumn::make('tax_id')
                    ->label('RNC')
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                AttachAction::make()
                    ->preloadRecordSelect()
                    ->label('Asignar a Empresa'),
            ])
            ->recordActions([
                DetachAction::make()
                    ->label('Quitar Acceso'),
            ])
            ->toolbarActions([
                DetachBulkAction::make(),
            ]);
    }
}
