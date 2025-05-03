<?php

namespace App\Filament\Resources\PointsTransactionResource\Pages;

use App\Filament\Resources\PointsTransactionResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewPointsTransaction extends ViewRecord
{
    protected static string $resource = PointsTransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->visible(fn() => auth()->user()->account_type === 'Admin'),
            Actions\DeleteAction::make()
                ->visible(fn() => auth()->user()->account_type === 'Admin'),
            Actions\Action::make('back')
                ->label('Back to Transactions')
                ->url(fn() => PointsTransactionResource::getUrl())
                ->color('gray'),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            // Add any widgets you want to display above the transaction details
        ];
    }

    // protected function getRelationManagers(): array
    // {
    //     return [
    //         // Add any relation managers here if needed
    //     ];
    // }
}
