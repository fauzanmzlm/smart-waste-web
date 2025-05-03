<?php

namespace App\Filament\Resources\WasteTypeResource\Pages;

use App\Filament\Resources\WasteTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditWasteType extends EditRecord
{
    protected static string $resource = WasteTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
