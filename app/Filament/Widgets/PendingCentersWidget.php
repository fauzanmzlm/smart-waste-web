<?php

namespace App\Filament\Widgets;

use App\Models\RecyclingCenter;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class PendingCentersWidget extends BaseWidget
{
    protected static ?string $heading = 'Pending Recycling Centers';
    protected static ?string $pollingInterval = '60s';
    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                RecyclingCenter::query()
                    ->where('status', 'pending')
                    ->orderBy('created_at', 'desc')
            )
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    ->label('Image')
                    ->circular(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('address')
                    ->searchable()
                    ->limit(30),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Owner')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Submitted')
                    ->dateTime()
                    ->sortable()
                    ->formatStateUsing(fn($state) => Carbon::parse($state)->diffForHumans()),
            ])
            ->actions([
                Tables\Actions\Action::make('approve')
                    ->label('Approve')
                    ->color('success')
                    ->icon('heroicon-m-check')
                    ->requiresConfirmation()
                    ->action(function (RecyclingCenter $record) {
                        $record->update([
                            'status' => 'approved',
                            'is_active' => true,
                        ]);
                    }),
                Tables\Actions\Action::make('reject')
                    ->label('Reject')
                    ->color('danger')
                    ->icon('heroicon-m-x-mark')
                    ->requiresConfirmation()
                    ->form([
                        \Filament\Forms\Components\Textarea::make('rejection_reason')
                            ->label('Reason for Rejection')
                            ->required(),
                    ])
                    ->action(function (RecyclingCenter $record, array $data) {
                        $record->update([
                            'status' => 'rejected',
                            'rejection_reason' => $data['rejection_reason'],
                        ]);
                    }),
                Tables\Actions\Action::make('view')
                    ->url(fn(RecyclingCenter $record): string => url('/admin/recycling-centers/' . $record->id . '/edit'))
                    ->icon('heroicon-m-eye'),
            ])
            ->emptyStateHeading('No pending centers')
            ->emptyStateDescription('All recycling centers have been reviewed');
    }
}
