<?php

namespace App\Filament\Resources\Contingencies;

use App\Filament\Resources\Contingencies\Pages\CreateContingency;
use App\Filament\Resources\Contingencies\Pages\EditContingency;
use App\Filament\Resources\Contingencies\Pages\ListContingencies;
use App\Filament\Resources\Contingencies\Schemas\ContingencyForm;
use App\Filament\Resources\Contingencies\Tables\ContingenciesTable;
use App\Models\Contingency;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class ContingencyResource extends Resource
{
    protected static ?string $model = Contingency::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-exclamation-triangle';

    protected static string|UnitEnum|null $navigationGroup = 'Facturación';


    public static function form(Schema $schema): Schema
    {
        return ContingencyForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ContingenciesTable::configure($table);
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
            'index' => ListContingencies::route('/'),
            'create' => CreateContingency::route('/create'),
            'edit' => EditContingency::route('/{record}/edit'),
        ];
    }
}
