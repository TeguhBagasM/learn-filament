<?php

namespace App\Filament\Resources\Fakturs\Pages;

use App\Filament\Resources\Fakturs\FakturResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListFakturs extends ListRecords
{
    protected static string $resource = FakturResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
