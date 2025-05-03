<?php

namespace App\Filament\Resources\CleanupEventResource\Pages;

use App\Filament\Resources\CleanupEventResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCleanupEvents extends ListRecords
{
    protected static string $resource = CleanupEventResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
