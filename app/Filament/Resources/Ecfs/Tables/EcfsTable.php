<?php

namespace App\Filament\Resources\Ecfs\Tables;

use App\Models\Ecf;
use App\Models\EcfAnnulment;
use App\Enums\EcfStatus;
use App\Services\DGII\DgiiEmissionService;
use App\Jobs\SendCancellationJob;
use App\Jobs\VerifyTrackIdJob;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Actions\BulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Facades\Storage;
use Filament\Support\Enums\Width;

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
                        '41' => 'Compras',
                        '43' => 'Gastos Menores',
                        '44' => 'Regímenes Especiales',
                        '45' => 'Gubernamental',
                        '46' => 'Exportaciones',
                        '47' => 'Pagos al Exterior',
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
                SelectFilter::make('type')
                    ->label('Tipo de e-CF')
                    ->options([
                        '31' => '31 - Crédito Fiscal',
                        '32' => '32 - Consumo',
                        '33' => '33 - Nota de Débito',
                        '34' => '34 - Nota de Crédito',
                        '41' => '41 - Compras',
                        '43' => '43 - Gastos Menores',
                        '44' => '44 - Regímenes Especiales',
                        '45' => '45 - Gubernamental',
                        '46' => '46 - Exportaciones',
                        '47' => '47 - Pagos al Exterior',
                    ]),
            ])
            ->recordActions([
                ActionGroup::make([
                    Action::make('preview')
                        ->label('Vista Previa')
                        ->icon('heroicon-o-eye')
                        ->color('gray')
                        ->modalHeading('Vista Previa del Comprobante')
                        ->modalWidth(Width::Screen)
                        ->modalSubmitAction(false)
                        ->modalCancelActionLabel('Cerrar')
                        ->modalContent(fn (Ecf $record) => view('filament.resources.ecfs.preview', [
                            'ecf' => $record,
                            'company' => $record->company,
                            'contact' => $record->contact,
                            'items' => $record->items,
                            'qrCode' => base64_encode('DUMMY_QR_FOR_PREVIEW'), // Simulación para la vista previa
                            'sequenceExpiration' => \App\Models\EcfSequence::where('company_id', $record->company_id)
                                ->where('type', $record->type)
                                ->where('is_active', true)
                                ->first()?->expiration_date,
                        ])),

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
                        ->infolist(
                            fn (Ecf $record) => \Filament\Infolists\Infolist::make()
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
                                $service->emit($record->id);
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

                    Action::make('void')
                        ->label('Anular e-CF')
                        ->icon('heroicon-o-document-minus')
                        ->color('danger')
                        ->visible(fn (Ecf $record) => in_array($record->dgii_status, [EcfStatus::ACEPTADO, EcfStatus::ACEPTADO_CONDICIONAL]))
                        ->form([
                            Textarea::make('reason')
                                ->label('Motivo de la anulación')
                                ->required()
                                ->maxLength(255),
                        ])
                        ->action(function (Ecf $record, array $data) {
                            $sequenceNumber = (int) substr($record->encf, 3);

                            $annulment = EcfAnnulment::create([
                                'company_id' => $record->company_id,
                                'type' => $record->type,
                                'start_sequence' => $sequenceNumber,
                                'end_sequence' => $sequenceNumber,
                                'quantity' => 1,
                                'reason' => $data['reason'],
                            ]);

                            $record->logActivity('Anulación Solicitada', "Se ha solicitado la anulación de la factura por el motivo: {$data['reason']}");

                            SendCancellationJob::dispatch($annulment);
                            Notification::make()
                                ->title('Anulación Solicitada')
                                ->body('Se ha enviado la solicitud de anulación a la DGII.')
                                ->warning()
                                ->send();
                        }),

                    EditAction::make(),
                ])
                ->icon('heroicon-m-ellipsis-vertical')
                ->tooltip('Acciones')
                ->color('gray'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('emitBatch')
                        ->label('Emitir Lote')
                        ->icon('heroicon-o-paper-airplane')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function (EloquentCollection $records, DgiiEmissionService $service) {
                            $records->each(function (Ecf $record) use ($service) {
                                if ($record->dgii_status !== EcfStatus::ACEPTADO) {
                                    $service->emit($record->id);
                                }
                            });

                            Notification::make()
                                ->title('Emisión Masiva Iniciada')
                                ->body('Se ha programado el envío de ' . $records->count() . ' facturas.')
                                ->success()
                                ->send();
                        }),

                    BulkAction::make('verifyBatch')
                        ->label('Verificar Estatus Lote')
                        ->icon('heroicon-o-arrow-path')
                        ->color('warning')
                        ->action(function (EloquentCollection $records) {
                            $records->each(function (Ecf $record) {
                                if ($record->dgii_status === EcfStatus::EN_PROCESO || $record->dgii_status === EcfStatus::ENVIADO) {
                                    VerifyTrackIdJob::dispatch($record);
                                }
                            });

                            Notification::make()
                                ->title('Verificación Iniciada')
                                ->body('Se está consultando el estado de ' . $records->count() . ' facturas ante la DGII.')
                                ->info()
                                ->send();
                        }),

                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
