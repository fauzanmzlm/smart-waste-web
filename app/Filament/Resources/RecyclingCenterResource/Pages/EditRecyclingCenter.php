<?php

namespace App\Filament\Resources\RecyclingCenterResource\Pages;

use App\Filament\Resources\RecyclingCenterResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRecyclingCenter extends EditRecord
{
    protected static string $resource = RecyclingCenterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
