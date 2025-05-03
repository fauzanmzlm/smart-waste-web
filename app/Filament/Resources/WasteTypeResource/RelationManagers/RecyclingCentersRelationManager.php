<?php

namespace App\Filament\Resources\WasteTypeResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class RecyclingCentersRelationManager extends RelationManager
{
    protected static string $relationship = 'recyclingCenters';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('address')
                    ->required()
                    ->rows(2),
                Forms\Components\TextInput::make('phone')
                    ->tel()
                    ->maxLength(20),
                Forms\Components\TextInput::make('website')
                    ->url()
                    ->maxLength(255),
                Forms\Components\Select::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ])
                    ->required()
                    ->default('pending'),
                Forms\Components\Toggle::make('is_active')
                    ->label('Active')
                    ->default(false),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    ->circular(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('address')
                    ->limit(30)
                    ->searchable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Owner')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'approved' => 'success',
                        'pending' => 'warning',
                        'rejected' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ]),
                Tables\Filters\Filter::make('is_active')
                    ->label('Active Centers')
                    ->query(fn(Builder $query): Builder => $query->where('is_active', true)),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
                Tables\Actions\AttachAction::make()
                    ->preloadRecordSelect(),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->button()->color('warning'),
                Tables\Actions\DetachAction::make()->button()->color('danger'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DetachBulkAction::make(),
                ]),
            ]);
    }
}
