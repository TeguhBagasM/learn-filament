<?php

namespace App\Filament\Resources\Fakturs\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class FaktursTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('kode_faktur')
                    ->searchable(),
                TextColumn::make('tanggal_faktur')
                    ->date()
                    ->sortable(),
                TextColumn::make('kode_customer')
                    ->searchable(),
                TextColumn::make('customer.nama_customer')
                    ->label('Nama Customer')
                    ->searchable(),
                TextColumn::make('total')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('nominal_charge')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('charge')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('total_final')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                DeleteAction::make(),
                EditAction::make(),

            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
