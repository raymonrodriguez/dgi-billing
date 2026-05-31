<?php

namespace App\Filament\Resources\Contingencies\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ContingencyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Registro de Contingencia')
                    ->description('Informa sobre fallas técnicas que impiden la emisión normal.')
                    ->schema([
                        Textarea::make('reason')
                            ->label('Motivo de la Contingencia')
                            ->required()
                            ->rows(3)
                            ->columnSpanFull(),

                        Grid::make(2)->schema([
                            DateTimePicker::make('start_date')
                                ->label('Fecha de Inicio')
                                ->required()
                                ->prefixIcon('heroicon-m-play-circle'),
                            
                            DateTimePicker::make('end_date')
                                ->label('Fecha de Finalización')
                                ->prefixIcon('heroicon-m-stop-circle'),
                        ]),

                        TextInput::make('dgii_track_id')
                            ->label('Track ID de Contingencia')
                            ->helperText('ID devuelto por la DGII al reportar la falla.')
                            ->prefixIcon('heroicon-m-signal')
                            ->disabled(),
                    ]),
            ]);
    }
}
