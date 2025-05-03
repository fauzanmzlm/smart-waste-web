<?php

namespace App\Filament\Resources\RewardResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Models\User;

class RedemptionsRelationManager extends RelationManager
{
    protected static string $relationship = 'redemptions';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->label('User')
                    ->relationship('user', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),
                Forms\Components\TextInput::make('code')
                    ->maxLength(255)
                    ->helperText('Leave blank to auto-generate'),
                Forms\Components\TextInput::make('points_cost')
                    ->required()
                    ->numeric()
                    ->default(function (RelationManager $livewire) {
                        return $livewire->getOwnerRecord()->points_cost;
                    })
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
                    ->rows(3),
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
                Tables\Columns\TextColumn::make('user.name')
                    ->label('User')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'approved' => 'success',
                        'pending' => 'warning',
                        'rejected' => 'danger',
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
                Tables\Columns\TextColumn::make('processor.name')
                    ->label('Processed By')
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ])
                    ->default('pending'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->mutateFormDataUsing(function (array $data) {
                        $data['reward_id'] = $this->getOwnerRecord()->id;
                        return $data;
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->button()->color('warning'),
                Tables\Actions\DeleteAction::make()->button()->color('danger')
                    ->visible(fn ($record) => $record->status === 'pending'),
                Tables\Actions\Action::make('approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn ($record) => $record->status === 'pending')
                    ->action(function ($record) {
                        $record->approve(auth()->id());
                    }),
                Tables\Actions\Action::make('reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn ($record) => $record->status === 'pending')
                    ->form([
                        Forms\Components\Textarea::make('notes')
                            ->label('Reason for Rejection')
                            ->required(),
                    ])
                    ->action(function ($record, array $data) {
                        $record->reject(auth()->id(), $data['notes']);
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn () => auth()->user()->account_type === 'Admin'),
                    Tables\Actions\BulkAction::make('approve_selected')
                        ->label('Approve Selected')
                        ->icon('heroicon-o-check')
                        ->color('success')
                        ->action(function ($records) {
                            foreach ($records as $record) {
                                if ($record->status === 'pending') {
                                    $record->approve(auth()->id());
                                }
                            }
                        }),
                    Tables\Actions\BulkAction::make('reject_selected')
                        ->label('Reject Selected')
                        ->icon('heroicon-o-x-mark')
                        ->color('danger')
                        ->form([
                            Forms\Components\Textarea::make('notes')
                                ->label('Reason for Rejection')
                                ->required(),
                        ])
                        ->action(function ($records, array $data) {
                            foreach ($records as $record) {
                                if ($record->status === 'pending') {
                                    $record->reject(auth()->id(), $data['notes']);
                                }
                            }
                        }),
                ]),
            ]);
    }
}