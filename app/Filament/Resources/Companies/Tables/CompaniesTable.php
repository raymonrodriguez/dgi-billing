<?php

namespace App\Filament\Resources\Companies\Tables;

use App\Models\Company;
use App\Services\DgiiService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
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
                    ->label('Empresa')
                    ->searchable(),
                TextColumn::make('tax_id')
                    ->label('RNC')
                    ->searchable(),
                TextColumn::make('trade_name')
                    ->label('Nombre Comercial')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('environment')
                    ->label('Ambiente')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'testecf' => 'gray',
                        'certecf' => 'warning',
                        'ecf' => 'success',
                        default => 'gray',
                    }),
                IconColumn::make('is_active')
                    ->label('Activa')
                    ->boolean(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                Action::make('testConnection')
                    ->label('Probar Conexión')
                    ->icon('heroicon-o-signal')
                    ->color('success')
                    ->action(function (Company $record, DgiiService $dgiiService) {
                        try {
                            $token = $dgiiService->getAccessToken($record);
                            Notification::make()
                                ->title('Conexión Exitosa')
                                ->body('Se obtuvo el token de la DGII correctamente.')
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
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
