<?php

namespace App\Filament\Resources\EcfSequences;

use App\Filament\Resources\EcfSequences\Pages\CreateEcfSequence;
use App\Filament\Resources\EcfSequences\Pages\EditEcfSequence;
use App\Filament\Resources\EcfSequences\Pages\ListEcfSequences;
use App\Filament\Resources\EcfSequences\Schemas\EcfSequenceForm;
use App\Filament\Resources\EcfSequences\Tables\EcfSequencesTable;
use App\Models\EcfSequence;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class EcfSequenceResource extends Resource
{
    protected static ?string $model = EcfSequence::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-numbered-list';

    protected static string|UnitEnum|null $navigationGroup = 'Facturación';


    public static function form(Schema $schema): Schema
    {
        return EcfSequenceForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EcfSequencesTable::configure($table);
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
            'index' => ListEcfSequences::route('/'),
            'create' => CreateEcfSequence::route('/create'),
            'edit' => EditEcfSequence::route('/{record}/edit'),
        ];
    }
}
