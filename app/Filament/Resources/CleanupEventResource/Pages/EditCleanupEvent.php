<?php

namespace App\Filament\Resources\CleanupEventResource\Pages;

use App\Filament\Resources\CleanupEventResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCleanupEvent extends EditRecord
{
    protected static string $resource = CleanupEventResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
