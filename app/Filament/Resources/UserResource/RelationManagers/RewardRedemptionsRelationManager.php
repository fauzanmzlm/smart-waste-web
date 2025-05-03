<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class RewardRedemptionsRelationManager extends RelationManager
{
    protected static string $relationship = 'rewardRedemptions';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('reward_id')
                    ->relationship('reward', 'title')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set) {
                        $reward = \App\Models\Reward::find($state);
                        if ($reward) {
                            $set('points_cost', $reward->points_cost);
                        }
                    }),
                Forms\Components\TextInput::make('code')
                    ->maxLength(255)
                    ->helperText('Leave blank to auto-generate'),
                Forms\Components\TextInput::make('points_cost')
                    ->numeric()
                    ->required()
                    ->disabled(),
                Forms\Components\Select::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ])
                    ->required()
                    ->default('pending'),
                Forms\Components\Textarea::make('notes')
                    ->maxLength(255)
                    ->rows(2),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('code')
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->searchable()
                    ->copyable(),
                Tables\Columns\TextColumn::make('reward.title')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('reward.center.name')
                    ->label('Recycling Center')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'approved' => 'success',
                        'pending' => 'warning',
                        'rejected' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('points_cost')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('processed_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ]),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->button()->color('warning'),
                Tables\Actions\ViewAction::make()->button(),
                Tables\Actions\DeleteAction::make()
                    ->button()->color('danger')
                    ->visible(fn($record) => $record->status === 'pending'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn() => auth()->user()->account_type === 'Admin'),
                ]),
            ]);
    }
}
