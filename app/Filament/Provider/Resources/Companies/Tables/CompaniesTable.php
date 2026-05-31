<?php

namespace App\Filament\Provider\Resources\Companies\Tables;

use App\Models\Company;
use App\Services\DGII\DgiiAuthService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ActionGroup;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CompaniesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('company_name')
                    ->label('Empresa / Emisor')
                    ->description(fn (Company $record) => $record->trade_name)
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('tax_id')
                    ->label('RNC')
                    ->copyable()
                    ->fontFamily('mono')
                    ->searchable(),

                TextColumn::make('environment')
                    ->label('Ambiente')
                    ->badge(),

                TextColumn::make('ecfs_count')
                    ->label('Facturas')
                    ->counts('ecfs')
                    ->sortable()
                    ->badge()
                    ->color('info'),

                TextColumn::make('last_ecf_date')
                    ->label('Última Actividad')
                    ->state(fn (Company $record) => $record->ecfs()->latest()->first()?->created_at?->diffForHumans() ?? 'Sin actividad')
                    ->color('gray')
                    ->size('sm'),

                TextColumn::make('email')
                    ->label('Contacto')
                    ->icon('heroicon-m-envelope')
                    ->searchable(),

                IconColumn::make('is_active')
                    ->label('Estado')
                    ->boolean()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])
            ->recordActions([
                ActionGroup::make([
                    EditAction::make(),

                    Action::make('testConnection')
                        ->label('Probar Token DGII')
                        ->icon('heroicon-o-signal')
                        ->color('success')
                        ->requiresConfirmation()
                        ->modalHeading('Validar Credenciales DGII')
                        ->modalDescription('Se realizará una petición de autenticación real usando el certificado de esta empresa.')
                        ->modalSubmitActionLabel('Probar ahora')
                        ->action(function (Company $record, DgiiAuthService $authService) {
                            try {
                                \Filament\Facades\Filament::setTenant($record);
                                $token = $authService->getToken();
                                Notification::make()
                                    ->title('Conexión Exitosa')
                                    ->body("Token obtenido para {$record->company_name}.")
                                    ->success()
                                    ->send();
                            } catch (\Exception $e) {
                                Notification::make()
                                    ->title('Error de Conexión')
                                    ->body($e->getMessage())
                                    ->danger()
                                    ->send();
                            }
                        }),

                    Action::make('enterDashboard')
                        ->label('Entrar al Panel')
                        ->icon('heroicon-o-arrow-right-on-rectangle')
                        ->color('primary')
                        ->requiresConfirmation()
                        ->modalHeading('Acceso a Panel de Cliente')
                        ->modalDescription('Vas a entrar al entorno de facturación privado de esta empresa.')
                        ->modalSubmitActionLabel('Entrar al Panel')
                        ->url(fn (Company $record): string => route('filament.admin.pages.dashboard', ['tenant' => $record->id]))
                        ->openUrlInNewTab(),
                ])
                ->icon('heroicon-m-ellipsis-vertical')
                ->tooltip('Acciones de Gestión'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
