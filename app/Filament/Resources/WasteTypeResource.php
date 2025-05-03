<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WasteTypeResource\Pages;
use App\Filament\Resources\WasteTypeResource\RelationManagers;
use App\Models\WasteType;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Colors\Color;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class WasteTypeResource extends Resource
{
    protected static ?string $model = WasteType::class;

    protected static ?string $navigationIcon = 'heroicon-o-trash';
    
    protected static ?string $navigationGroup = 'Waste Management';
    
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Waste Type Details')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('icon')
                            ->required()
                            ->maxLength(255)
                            ->helperText('Enter emoji or icon code'),
                        Forms\Components\ColorPicker::make('color')
                            ->required(),
                        Forms\Components\TextInput::make('class')
                            ->maxLength(255)
                            ->helperText('CSS class if needed'),
                    ])->columns(2),
                
                Forms\Components\Section::make('Description & Tips')
                    ->schema([
                        Forms\Components\Textarea::make('description')
                            ->required()
                            ->rows(3),
                        Forms\Components\Textarea::make('tips')
                            ->rows(3),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('icon')
                    ->label('Icon')
                    ->searchable(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\ColorColumn::make('color'),
                Tables\Columns\TextColumn::make('wasteItems_count')
                    ->counts('wasteItems')
                    ->label('Items')
                    ->sortable(),
                Tables\Columns\TextColumn::make('recyclingCenters_count')
                    ->counts('recyclingCenters')
                    ->label('Centers')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()->button()->color(Color::Amber),
                Tables\Actions\DeleteAction::make()->button(),
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
            RelationManagers\WasteItemsRelationManager::class,
            RelationManagers\RecyclingCentersRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWasteTypes::route('/'),
            'create' => Pages\CreateWasteType::route('/create'),
            'edit' => Pages\EditWasteType::route('/{record}/edit'),
        ];
    }
}