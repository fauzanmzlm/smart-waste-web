<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MaterialResource\Pages;
use App\Filament\Resources\MaterialResource\RelationManagers;
use App\Models\Material;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class MaterialResource extends Resource
{
    protected static ?string $model = Material::class;

    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';
    
    protected static ?string $navigationGroup = 'Waste Management';
    
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Material Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('icon')
                            ->required()
                            ->maxLength(255)
                            ->helperText('Enter emoji or icon code'),
                        Forms\Components\TextInput::make('default_points')
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->default(1),
                        Forms\Components\Textarea::make('description')
                            ->rows(3),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('icon')
                    ->searchable(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('default_points')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('pointConfigs_count')
                    ->counts('pointConfigs')
                    ->label('Custom Configs')
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
            RelationManagers\PointConfigsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMaterials::route('/'),
            'create' => Pages\CreateMaterial::route('/create'),
            'edit' => Pages\EditMaterial::route('/{record}/edit'),
        ];
    }
    
    // Add a convenient action to seed default materials
    public static function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('seedDefaults')
                ->label('Seed Default Materials')
                ->icon('heroicon-o-sparkles')
                ->color('warning')
                ->requiresConfirmation()
                ->action(function () {
                    Material::seedDefaults();
                }),
        ];
    }
}