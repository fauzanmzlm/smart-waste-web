<?php

namespace App\Filament\Resources\RecyclingCenterResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PointsTransactionsRelationManager extends RelationManager
{
    protected static string $relationship = 'pointsTransactions';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),
                Forms\Components\TextInput::make('points')
                    ->required()
                    ->numeric(),
                Forms\Components\Select::make('type')
                    ->options([
                        'earned' => 'Earned',
                        'spent' => 'Spent',
                    ])
                    ->required(),
                Forms\Components\Select::make('category')
                    ->options([
                        'recycling' => 'Recycling',
                        'reward_redemption' => 'Reward Redemption',
                        'bonus' => 'Bonus',
                        'refund' => 'Refund',
                        'transfer' => 'Transfer',
                        'other' => 'Other',
                    ])
                    ->required(),
                Forms\Components\Textarea::make('description')
                    ->required()
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->searchable()
                    ->sortable(),
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
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->mutateFormDataUsing(function (array $data) {
                        $data['center_id'] = $this->getOwnerRecord()->id;
                        return $data;
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->button()->color('warning'),
                Tables\Actions\DeleteAction::make()->button()->color('danger'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}