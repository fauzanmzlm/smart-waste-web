<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RewardRedemptionResource\Pages;
use App\Filament\Resources\RewardRedemptionResource\RelationManagers;
use App\Models\RewardRedemption;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class RewardRedemptionResource extends Resource
{
    protected static ?string $model = RewardRedemption::class;

    protected static ?string $navigationIcon = 'heroicon-o-ticket';
    
    protected static ?string $navigationGroup = 'Points & Rewards';
    
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Redemption Details')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->relationship('user', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),
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
                    ])->columns(2),
                
                Forms\Components\Section::make('Status')
                    ->schema([
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
                        Forms\Components\DateTimePicker::make('processed_at')
                            ->nullable(),
                        Forms\Components\Select::make('processed_by')
                            ->label('Processed By')
                            ->relationship('processor', 'name')
                            ->searchable()
                            ->preload()
                            ->nullable(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->searchable()
                    ->copyable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('reward.title')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('reward.center.name')
                    ->label('Recycling Center')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
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
                Tables\Columns\TextColumn::make('processor.name')
                    ->label('Processed By')
                    ->searchable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ]),
                Tables\Filters\SelectFilter::make('center')
                    ->relationship('reward.center', 'name')
                    ->label('Recycling Center'),
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from'),
                        Forms\Components\DatePicker::make('created_until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()->button(),
                Tables\Actions\EditAction::make()->button()->color('warning'),
                Tables\Actions\Action::make('approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (RewardRedemption $record) => $record->status === 'pending')
                    ->action(function (RewardRedemption $record) {
                        $record->approve(auth()->id());
                    }),
                Tables\Actions\Action::make('reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (RewardRedemption $record) => $record->status === 'pending')
                    ->form([
                        Forms\Components\Textarea::make('notes')
                            ->label('Reason for Rejection')
                            ->required(),
                    ])
                    ->action(function (RewardRedemption $record, array $data) {
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

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRewardRedemptions::route('/'),
            'create' => Pages\CreateRewardRedemption::route('/create'),
            'view' => Pages\ViewRewardRedemption::route('/{record}'),
            'edit' => Pages\EditRewardRedemption::route('/{record}/edit'),
        ];
    }
    
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'pending')->count() ?: null;
    }
    
    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }
}