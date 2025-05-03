<?php

namespace App\Filament\Resources\PointsTransactionResource\Pages;

use App\Filament\Resources\PointsTransactionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPointsTransaction extends EditRecord
{
    protected static string $resource = PointsTransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
