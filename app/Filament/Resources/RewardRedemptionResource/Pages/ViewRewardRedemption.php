<?php

namespace App\Filament\Resources\RewardRedemptionResource\Pages;

use App\Filament\Resources\RewardRedemptionResource;
use App\Models\RewardRedemption;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Notifications\Notification;

class ViewRewardRedemption extends ViewRecord
{
    protected static string $resource = RewardRedemptionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->visible(fn () => auth()->user()->account_type === 'Admin'),
            Actions\Action::make('approve')
                ->label('Approve Redemption')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->visible(fn (RewardRedemption $record) => $record->status === 'pending' && 
                    (auth()->user()->account_type === 'Admin' || 
                    (auth()->user()->account_type === 'CenterOwner' && 
                     auth()->user()->recyclingCenter && 
                     auth()->user()->recyclingCenter->id === $record->reward->center_id)))
                ->action(function (RewardRedemption $record) {
                    $record->approve(auth()->id());
                    
                    Notification::make()
                        ->title('Redemption approved successfully')
                        ->success()
                        ->send();
                    
                    $this->redirect($this->getResource()::getUrl('view', ['record' => $record->id]));
                }),
            Actions\Action::make('reject')
                ->label('Reject Redemption')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->visible(fn (RewardRedemption $record) => $record->status === 'pending' && 
                    (auth()->user()->account_type === 'Admin' || 
                    (auth()->user()->account_type === 'CenterOwner' && 
                     auth()->user()->recyclingCenter && 
                     auth()->user()->recyclingCenter->id === $record->reward->center_id)))
                ->form([
                    \Filament\Forms\Components\Textarea::make('notes')
                        ->label('Reason for Rejection')
                        ->required(),
                ])
                ->action(function (array $data, RewardRedemption $record) {
                    $record->reject(auth()->id(), $data['notes']);
                    
                    Notification::make()
                        ->title('Redemption rejected successfully')
                        ->success()
                        ->send();
                    
                    $this->redirect($this->getResource()::getUrl('view', ['record' => $record->id]));
                }),
            Actions\Action::make('back')
                ->label('Back to Redemptions')
                ->url(fn () => RewardRedemptionResource::getUrl())
                ->color('gray'),
        ];
    }
}