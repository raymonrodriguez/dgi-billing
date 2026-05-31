<?php

namespace App\Filament\Resources\EcfAnnulments;

use App\Filament\Resources\EcfAnnulments\Pages\CreateEcfAnnulment;
use App\Filament\Resources\EcfAnnulments\Pages\EditEcfAnnulment;
use App\Filament\Resources\EcfAnnulments\Pages\ListEcfAnnulments;
use App\Filament\Resources\EcfAnnulments\Schemas\EcfAnnulmentForm;
use App\Filament\Resources\EcfAnnulments\Tables\EcfAnnulmentsTable;
use App\Models\EcfAnnulment;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class EcfAnnulmentResource extends Resource
{
    protected static ?string $model = EcfAnnulment::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-document-minus';

    protected static string|UnitEnum|null $navigationGroup = 'Facturación';


    public static function form(Schema $schema): Schema
    {
        return EcfAnnulmentForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EcfAnnulmentsTable::configure($table);
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
            'index' => ListEcfAnnulments::route('/'),
            'create' => CreateEcfAnnulment::route('/create'),
            'edit' => EditEcfAnnulment::route('/{record}/edit'),
        ];
    }
}
