<?php

namespace App\Filament\Resources\Customers\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class CustomerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make("nama_customer")
                    ->required(),
                TextInput::make("kode_customer")
                    ->required(),
                TextInput::make("alamat_customer")
                    ->required(),
                TextInput::make("telepon_customer")
                    ->required()
            ]);
    }
}
