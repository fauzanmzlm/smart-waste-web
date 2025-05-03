<?php

namespace App\Filament\Resources\RecyclingCenterResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class RewardsRelationManager extends RelationManager
{
    protected static string $relationship = 'rewards';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->required()
                    ->maxLength(255),
                Forms\Components\FileUpload::make('image')
                    ->image()
                    ->directory('rewards'),
                Forms\Components\Textarea::make('description')
                    ->required()
                    ->rows(3),
                Forms\Components\Select::make('category')
                    ->options([
                        'discount' => 'Discount',
                        'product' => 'Product',
                        'service' => 'Service',
                        'experience' => 'Experience',
                        'donation' => 'Donation',
                        'other' => 'Other',
                    ])
                    ->required(),
                Forms\Components\TextInput::make('points_cost')
                    ->required()
                    ->numeric()
                    ->minValue(1)
                    ->default(100),
                Forms\Components\TextInput::make('quantity')
                    ->helperText('Leave empty for unlimited')
                    ->numeric()
                    ->minValue(1),
                Forms\Components\DateTimePicker::make('expiry_date')
                    ->helperText('Leave empty for no expiry'),
                Forms\Components\Textarea::make('terms')
                    ->rows(3)
                    ->helperText('Terms and conditions for the reward'),
                Forms\Components\Textarea::make('redemption_instructions')
                    ->rows(3)
                    ->helperText('Instructions for redeeming the reward'),
                Forms\Components\Grid::make(2)
                    ->schema([
                        Forms\Components\Toggle::make('is_active')
                            ->label('Active')
                            ->default(true),
                        Forms\Components\Toggle::make('is_featured')
                            ->label('Featured')
                            ->default(false),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    ->circular(),
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('category')
                    ->badge(),
                Tables\Columns\TextColumn::make('points_cost')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('quantity')
                    ->numeric()
                    ->default('∞')
                    ->sortable(),
                Tables\Columns\TextColumn::make('redemptions_count')
                    ->counts('redemptions')
                    ->label('Redemptions')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
                Tables\Columns\IconColumn::make('is_featured')
                    ->boolean(),
                Tables\Columns\TextColumn::make('expiry_date')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->options([
                        'discount' => 'Discount',
                        'product' => 'Product',
                        'service' => 'Service',
                        'experience' => 'Experience',
                        'donation' => 'Donation',
                        'other' => 'Other',
                    ]),
                Tables\Filters\Filter::make('is_active')
                    ->query(fn (Builder $query): Builder => $query->where('is_active', true))
                    ->label('Active Only'),
                Tables\Filters\Filter::make('is_featured')
                    ->query(fn (Builder $query): Builder => $query->where('is_featured', true))
                    ->label('Featured Only'),
                Tables\Filters\Filter::make('is_not_expired')
                    ->query(function (Builder $query): Builder {
                        return $query->where(function ($query) {
                            $query->whereNull('expiry_date')
                                  ->orWhere('expiry_date', '>', now());
                        });
                    })
                    ->label('Not Expired'),
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
                Tables\Actions\Action::make('toggle_active')
                    ->label(fn ($record) => $record->is_active ? 'Deactivate' : 'Activate')
                    ->icon(fn ($record) => $record->is_active ? 'heroicon-o-pause' : 'heroicon-o-play')
                    ->color(fn ($record) => $record->is_active ? 'danger' : 'success')
                    ->action(function ($record) {
                        $record->is_active = !$record->is_active;
                        $record->save();
                    }),
                Tables\Actions\Action::make('toggle_featured')
                    ->label(fn ($record) => $record->is_featured ? 'Unfeature' : 'Feature')
                    ->icon('heroicon-o-star')
                    ->color(fn ($record) => $record->is_featured ? 'gray' : 'warning')
                    ->visible(fn () => auth()->user()->account_type === 'Admin')
                    ->action(function ($record) {
                        $record->is_featured = !$record->is_featured;
                        $record->save();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('activate')
                        ->label('Activate Selected')
                        ->icon('heroicon-o-check')
                        ->action(function ($records) {
                            foreach ($records as $record) {
                                $record->is_active = true;
                                $record->save();
                            }
                        }),
                    Tables\Actions\BulkAction::make('deactivate')
                        ->label('Deactivate Selected')
                        ->icon('heroicon-o-x-mark')
                        ->color('danger')
                        ->action(function ($records) {
                            foreach ($records as $record) {
                                $record->is_active = false;
                                $record->save();
                            }
                        }),
                ]),
            ]);
    }
}