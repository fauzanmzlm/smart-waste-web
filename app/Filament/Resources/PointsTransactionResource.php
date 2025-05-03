<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PointsTransactionResource\Pages;
use App\Filament\Resources\PointsTransactionResource\RelationManagers;
use App\Models\PointsTransaction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PointsTransactionResource extends Resource
{
    protected static ?string $model = PointsTransaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';
    
    protected static ?string $navigationGroup = 'Points & Rewards';
    
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Transaction Details')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->relationship('user', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),
                        Forms\Components\TextInput::make('points')
                            ->required()
                            ->numeric()
                            ->default(0),
                        Forms\Components\Select::make('type')
                            ->options([
                                'earned' => 'Earned',
                                'spent' => 'Spent',
                            ])
                            ->required()
                            ->default('earned'),
                        Forms\Components\Select::make('category')
                            ->options([
                                'recycling' => 'Recycling',
                                'reward_redemption' => 'Reward Redemption',
                                'bonus' => 'Bonus',
                                'refund' => 'Refund',
                                'transfer' => 'Transfer',
                                'other' => 'Other',
                            ])
                            ->required()
                            ->default('other'),
                        Forms\Components\Textarea::make('description')
                            ->required()
                            ->rows(3)
                            ->columnSpanFull(),
                    ])->columns(2),
                
                Forms\Components\Section::make('Additional Information')
                    ->schema([
                        Forms\Components\Select::make('center_id')
                            ->relationship('center', 'name')
                            ->searchable()
                            ->preload(),
                        Forms\Components\Select::make('transactionable_type')
                            ->label('Related To')
                            ->options([
                                'App\\Models\\RecyclingHistory' => 'Recycling Activity',
                                'App\\Models\\RewardRedemption' => 'Reward Redemption',
                                'App\\Models\\Badge' => 'Badge',
                                null => 'None',
                            ])
                            ->nullable(),
                        Forms\Components\TextInput::make('transactionable_id')
                            ->label('Related ID')
                            ->numeric()
                            ->nullable()
                            ->visible(fn (Forms\Get $get) => $get('transactionable_type') !== null),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->numeric()
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'earned' => 'success',
                        'spent' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('category')
                    ->badge(),
                Tables\Columns\TextColumn::make('points')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('description')
                    ->wrap()
                    ->limit(30),
                Tables\Columns\TextColumn::make('center.name')
                    ->label('Recycling Center')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'earned' => 'Earned',
                        'spent' => 'Spent',
                    ]),
                Tables\Filters\SelectFilter::make('category')
                    ->options([
                        'recycling' => 'Recycling',
                        'reward_redemption' => 'Reward Redemption',
                        'bonus' => 'Bonus',
                        'refund' => 'Refund',
                        'transfer' => 'Transfer',
                        'other' => 'Other',
                    ]),
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
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()->button()->color('warning'),
                Tables\Actions\DeleteAction::make()->button()->color('danger'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListPointsTransactions::route('/'),
            'create' => Pages\CreatePointsTransaction::route('/create'),
            'view' => Pages\ViewPointsTransaction::route('/{record}'),
            'edit' => Pages\EditPointsTransaction::route('/{record}/edit'),
        ];
    }
}