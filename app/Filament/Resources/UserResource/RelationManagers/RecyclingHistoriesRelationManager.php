<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class RecyclingHistoriesRelationManager extends RelationManager
{
    protected static string $relationship = 'recyclingHistories';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('waste_item_id')
                    ->relationship('wasteItem', 'name')
                    ->required(),
                Forms\Components\TextInput::make('location')
                    ->maxLength(255),
                Forms\Components\FileUpload::make('image')
                    ->image()
                    ->directory('recycling-history'),
                Forms\Components\TextInput::make('points_earned')
                    ->numeric()
                    ->default(0),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                Tables\Columns\TextColumn::make('wasteItem.name')
                    ->label('Waste Item')
                    ->searchable(),
                Tables\Columns\TextColumn::make('wasteItem.wasteType.name')
                    ->label('Waste Type')
                    ->searchable(),
                Tables\Columns\TextColumn::make('points_earned')
                    ->sortable(),
                Tables\Columns\ImageColumn::make('image')
                    ->label('Image')
                    ->circular(),
                Tables\Columns\TextColumn::make('location')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
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