<?php

namespace App\Filament\Resources\RecyclingCenterResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class WasteTypesRelationManager extends RelationManager
{
    protected static string $relationship = 'wasteTypes';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('icon')
                    ->required()
                    ->maxLength(255),
                Forms\Components\ColorPicker::make('color')
                    ->required(),
                Forms\Components\Textarea::make('description')
                    ->required()
                    ->rows(3),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('icon')
                    ->searchable(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\ColorColumn::make('color'),
                Tables\Columns\TextColumn::make('description')
                    ->limit(30)
                    ->toggleable(),
                Tables\Columns\TextColumn::make('totalWasteItems')
                    // ->counts('wasteItems')
                    ->label('Items')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    ->preloadRecordSelect(),
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->button()->color('warning'),
                Tables\Actions\DeleteAction::make()->button()->color('danger'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DetachBulkAction::make(),
                ]),
            ]);
    }
}