<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class LatestUsers extends BaseWidget
{
    protected static ?int $sort = 2;
    protected static ?string $heading = 'Recently Registered Users';
    protected static ?string $pollingInterval = '60s';
    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                User::query()
                    ->orderBy('created_at', 'desc')
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\ImageColumn::make('photoURL')
                    ->label('Avatar')
                    ->circular(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('loyalty_id')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('account_type')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'Admin' => 'danger',
                        'CenterOwner' => 'warning',
                        default => 'success',
                    }),
                Tables\Columns\TextColumn::make('items_recycled')
                    ->label('Recycled Items')
                    ->getStateUsing(fn(User $record): int => $record->recyclingHistories()->count())
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query->withCount('recyclingHistories')
                            ->orderBy('recycling_histories_count', $direction);
                    }),
                Tables\Columns\TextColumn::make('points_balance')
                    ->label('Points')
                    ->getStateUsing(fn(User $record): int => $record->points_balance)
                    ->badge()
                    ->color('success'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Registered')
                    ->dateTime()
                    ->sortable()
                    ->formatStateUsing(fn($state) => Carbon::parse($state)->diffForHumans()),
                Tables\Columns\TextColumn::make('last_login_at')
                    ->label('Last Login')
                    ->dateTime()
                    ->sortable()
                    ->formatStateUsing(fn($state) => $state ? Carbon::parse($state)->diffForHumans() : 'Never'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('account_type')
                    ->options([
                        'Standard' => 'Standard',
                        'CenterOwner' => 'Center Owner',
                        'Admin' => 'Admin',
                    ]),
                Tables\Filters\Filter::make('active_users')
                    ->label('Recently Active')
                    ->query(fn(Builder $query): Builder => $query->where('last_login_at', '>=', now()->subDays(7))),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->url(fn(User $record): string => route('filament.admin.resources.users.edit', ['record' => $record]))
                    ->icon('heroicon-m-eye'),
                Tables\Actions\Action::make('view_history')
                    ->label('Recycling History')
                    ->icon('heroicon-m-clipboard-document-list')
                    ->url(fn(User $record): string => url('/admin/recycling-histories?user_id=' . $record->id)),
            ])
            ->paginated([5, 10, 25, 50])
            ->defaultPaginationPageOption(10);
    }
}
