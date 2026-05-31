<?php

namespace App\Filament\Resources\Ecfs\Tables;

use App\Models\Ecf;
use App\Enums\EcfStatus;
use App\Services\DgiiEmissionService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;

class EcfsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('issued_at')
                    ->label('Fecha')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                TextColumn::make('contact.name')
                    ->label('Cliente')
                    ->searchable(),
                TextColumn::make('encf')
                    ->label('e-NCF')
                    ->searchable()
                    ->copyable(),
                TextColumn::make('type')
                    ->label('Tipo')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        '31' => 'Crédito Fiscal',
                        '32' => 'Consumo',
                        '33' => 'Nota de Débito',
                        '34' => 'Nota de Crédito',
                        default => $state,
                    })
                    ->searchable(),
                TextColumn::make('total_amount')
                    ->label('Total')
                    ->money('DOP')
                    ->sortable(),
                TextColumn::make('dgii_status')
                    ->label('Estatus DGII')
                    ->badge()
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                Action::make('downloadPdf')
                    ->label('Descargar PDF')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('info')
                    ->visible(fn (Ecf $record) => filled($record->pdf_path))
                    ->action(fn (Ecf $record) => Storage::disk('public')->download($record->pdf_path)),
                
                Action::make('viewErrors')
                    ->label('Ver Errores DGII')
                    ->icon('heroicon-o-exclamation-circle')
                    ->color('danger')
                    ->visible(fn (Ecf $record) => $record->dgii_status === EcfStatus::RECHAZADO && filled($record->dgii_messages))
                    ->modalHeading('Mensajes de la DGII')
                    ->infolist(fn (Ecf $record) => \Filament\Infolists\Infolist::make()
                        ->schema([
                            \Filament\Infolists\Components\TextEntry::make('dgii_messages')
                                ->label('Detalle del Error')
                                ->prose()
                                ->markdown(),
                        ])
                    )
                    ->modalSubmitAction(false),

                Action::make('emit')
                    ->label('Emitir e-CF')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('success')
                    ->requiresConfirmation()
                    ->hidden(fn (Ecf $record) => $record->dgii_status === EcfStatus::ACEPTADO)
                    ->action(function (Ecf $record, DgiiEmissionService $service) {
                        try {
                            $service->emit($record);
                            Notification::make()
                                ->title('Emisión Iniciada')
                                ->body('La factura se está firmando y enviando a la DGII.')
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Error de Emisión')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
                ViewAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
