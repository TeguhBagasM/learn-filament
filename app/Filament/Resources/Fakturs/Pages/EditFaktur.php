<?php

namespace App\Filament\Resources\Fakturs\Pages;

use App\Filament\Resources\Fakturs\FakturResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditFaktur extends EditRecord
{
    protected static string $resource = FakturResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
