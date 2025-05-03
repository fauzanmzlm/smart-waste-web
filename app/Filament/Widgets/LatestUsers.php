<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class LatestUsers extends BaseWidget
{
    protected static ?int $sort = 2;
    
    protected int|string|array $columnSpan = 'full';
    
    protected static ?string $heading = 'Recently Registered Users';
    
    protected static ?string $pollingInterval = '60s';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                User::query()
                    ->orderBy('created_at', 'desc')
                    ->limit(5)
            )
            ->columns([
                Tables\Columns\ImageColumn::make('photoURL')
                    ->label('Avatar')
                    ->circular(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('account_type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Admin' => 'danger',
                        'CenterOwner' => 'warning',
                        default => 'success',
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->url(fn (User $record): string => route('filament.admin.resources.users.edit', ['record' => $record]))
                    ->icon('heroicon-m-eye'),
            ]);
    }
}