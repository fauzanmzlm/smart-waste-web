<?php

namespace App\Filament\Resources\RecyclingCenterResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Models\Material;

class MaterialPointConfigsRelationManager extends RelationManager
{
    protected static string $relationship = 'materialPointConfigs';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('material_id')
                    ->label('Material')
                    ->options(Material::pluck('name', 'id'))
                    ->required()
                    ->searchable()
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set) {
                        if ($state) {
                            $material = Material::find($state);
                            if ($material) {
                                $set('points', $material->default_points);
                            }
                        }
                    }),
                Forms\Components\TextInput::make('points')
                    ->required()
                    ->numeric()
                    ->minValue(0)
                    ->default(0),
                Forms\Components\Toggle::make('is_enabled')
                    ->label('Enabled')
                    ->default(true),
                Forms\Components\TextInput::make('multiplier')
                    ->numeric()
                    ->step(0.1)
                    ->default(1.0)
                    ->minValue(0.1),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('material.name')
                    ->label('Material')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('material.icon')
                    ->label('Icon'),
                Tables\Columns\TextColumn::make('points')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_enabled')
                    ->label('Enabled')
                    ->boolean(),
                Tables\Columns\TextColumn::make('multiplier')
                    ->sortable(),
                Tables\Columns\TextColumn::make('getEffectivePoints')
                    ->label('Effective Points')
                    ->getStateUsing(function ($record) {
                        return $record->getEffectivePoints();
                    }),
            ])
            ->filters([
                Tables\Filters\Filter::make('is_enabled')
                    ->query(fn(Builder $query): Builder => $query->where('is_enabled', true))
                    ->label('Enabled Only'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->mutateFormDataUsing(function (array $data) {
                        $data['center_id'] = $this->getOwnerRecord()->id;
                        return $data;
                    }),
                Tables\Actions\Action::make('sync_materials')
                    ->label('Sync All Materials')
                    ->color('warning')
                    ->icon('heroicon-o-arrow-path')
                    ->action(function () {
                        $center = $this->getOwnerRecord();
                        $materials = Material::all();

                        foreach ($materials as $material) {
                            $center->materialPointConfigs()->updateOrCreate(
                                ['material_id' => $material->id],
                                [
                                    'points' => $material->default_points,
                                    'is_enabled' => true,
                                    'multiplier' => 1.0,
                                ]
                            );
                        }
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->button()->color('warning'),
                Tables\Actions\DeleteAction::make()->button()->color('danger'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('enable')
                        ->label('Enable Selected')
                        ->icon('heroicon-o-check')
                        ->action(function ($records) {
                            foreach ($records as $record) {
                                $record->is_enabled = true;
                                $record->save();
                            }
                        }),
                    Tables\Actions\BulkAction::make('disable')
                        ->label('Disable Selected')
                        ->icon('heroicon-o-x-mark')
                        ->color('danger')
                        ->action(function ($records) {
                            foreach ($records as $record) {
                                $record->is_enabled = false;
                                $record->save();
                            }
                        }),
                ]),
            ]);
    }
}
