<?php

namespace App\Filament\Resources\WasteTypeResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class WasteItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'wasteItems';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Waste Item Details')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\FileUpload::make('image')
                            ->image()
                            ->directory('waste-items'),
                        Forms\Components\Toggle::make('recyclable')
                            ->default(true),
                        Forms\Components\TextInput::make('points')
                            ->numeric()
                            ->default(0)
                            ->minValue(0),
                    ])->columns(2),
                
                Forms\Components\Section::make('Additional Information')
                    ->schema([
                        Forms\Components\Textarea::make('description')
                            ->rows(3),
                        Forms\Components\Textarea::make('restrictions')
                            ->rows(2),
                        Forms\Components\Textarea::make('alternatives')
                            ->rows(2),
                    ]),
                
                Forms\Components\Section::make('Disposal Instructions')
                    ->schema([
                        Forms\Components\KeyValue::make('disposal_instructions')
                            ->keyLabel('Step')
                            ->valueLabel('Instruction'),
                    ]),
                
                Forms\Components\Section::make('Environmental Impact')
                    ->schema([
                        Forms\Components\KeyValue::make('ocean_impact_factors')
                            ->keyLabel('Factor')
                            ->valueLabel('Impact'),
                    ]),
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
                Tables\Columns\IconColumn::make('recyclable')
                    ->boolean(),
                Tables\Columns\TextColumn::make('points')
                    ->sortable(),
                Tables\Columns\TextColumn::make('recyclingHistories_count')
                    ->counts('recyclingHistories')
                    ->label('Recycled')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\Filter::make('recyclable')
                    ->query(fn (Builder $query): Builder => $query->where('recyclable', true)),
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
                    Tables\Actions\BulkAction::make('mark_recyclable')
                        ->label('Mark as Recyclable')
                        ->icon('heroicon-o-check-circle')
                        ->action(function ($records) {
                            foreach ($records as $record) {
                                $record->recyclable = true;
                                $record->save();
                            }
                        }),
                    Tables\Actions\BulkAction::make('mark_not_recyclable')
                        ->label('Mark as Not Recyclable')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->action(function ($records) {
                            foreach ($records as $record) {
                                $record->recyclable = false;
                                $record->save();
                            }
                        }),
                ]),
            ]);
    }
}