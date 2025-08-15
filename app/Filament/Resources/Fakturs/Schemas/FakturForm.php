<?php

namespace App\Filament\Resources\Fakturs\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class FakturForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('kode_faktur')
                    ->required(),
                DatePicker::make('tanggal_faktur')
                    ->required(),
                TextInput::make('kode_customer')
                    ->required(),
                Select::make('customer_id')
                    ->relationship('customer', 'nama_customer')
                    ->required(),
                Repeater::make('detail')
                    ->relationship()
                    ->schema([
                        Select::make('barang_id')
                            ->relationship('barang', 'nama_barang')
                            ->required(),
                        
                        TextInput::make('kode_barang')
                            ->required(),
                        TextInput::make('jumlah_barang')
                            ->required()
                            ->numeric(),
                        TextInput::make('harga_barang')
                            ->required()
                            ->numeric(),
                    ])
                    ->columnSpanFull(),
                Textarea::make('ket_faktur')
                    ->columnSpanFull(),
                TextInput::make('total')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('nominal_charge')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('charge')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('total_final')
                    ->required()
                    ->numeric()
                    ->default(0),
            ]);
    }
}
