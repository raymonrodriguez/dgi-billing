<?php

namespace App\Filament\Resources\ReceivedEcfs\Tables;

use App\Models\ReceivedEcf;
use App\Enums\CommercialApprovalStatus;
use App\Jobs\SendCommercialApprovalJob;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;

class ReceivedEcfsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->label('Fecha Recibo')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                TextColumn::make('rnc_emisor')
                    ->label('RNC Suplidor')
                    ->searchable(),
                TextColumn::make('encf')
                    ->label('e-NCF')
                    ->searchable()
                    ->copyable(),
                TextColumn::make('total_amount')
                    ->label('Monto')
                    ->money('DOP')
                    ->sortable(),
                TextColumn::make('commercial_approval_status')
                    ->label('Aprobación')
                    ->badge(),
                IconColumn::make('arecf_sent')
                    ->label('ARECF')
                    ->boolean(),
                IconColumn::make('acecf_sent')
                    ->label('ACECF')
                    ->boolean(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                Action::make('downloadOriginalXml')
                    ->label('Descargar XML')
                    ->icon('heroicon-o-code-bracket')
                    ->color('info')
                    ->action(fn (ReceivedEcf $record) => Storage::download($record->received_xml_path)),

                Action::make('approve')
                    ->label('Aprobar Comercial')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (ReceivedEcf $record) => $record->commercial_approval_status === CommercialApprovalStatus::PENDIENTE)
                    ->action(function (ReceivedEcf $record) {
                        SendCommercialApprovalJob::dispatch($record->id, '1');
                        Notification::make()
                            ->title('Aprobación Enviada')
                            ->body('Se ha programado el envío de la aprobación comercial.')
                            ->success()
                            ->send();
                    }),
                
                Action::make('reject')
                    ->label('Rechazar Comercial')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (ReceivedEcf $record) => $record->commercial_approval_status === CommercialApprovalStatus::PENDIENTE)
                    ->action(function (ReceivedEcf $record) {
                        SendCommercialApprovalJob::dispatch($record->id, '2');
                        Notification::make()
                            ->title('Rechazo Enviado')
                            ->body('Se ha programado el envío del rechazo comercial.')
                            ->warning()
                            ->send();
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
