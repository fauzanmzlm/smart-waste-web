<?php

namespace App\Filament\Resources\MaterialResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Models\RecyclingCenter;

class PointConfigsRelationManager extends RelationManager
{
    protected static string $relationship = 'pointConfigs';

    protected static ?string $recordTitleAttribute = 'id';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('center_id')
                    ->label('Recycling Center')
                    ->options(RecyclingCenter::pluck('name', 'id'))
                    ->required()
                    ->searchable(),
                Forms\Components\TextInput::make('points')
                    ->required()
                    ->numeric()
                    ->minValue(0)
                    ->default(function (RelationManager $livewire) {
                        return $livewire->getOwnerRecord()->default_points;
                    }),
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
            ->columns([
                Tables\Columns\TextColumn::make('center.name')
                    ->label('Recycling Center')
                    ->searchable()
                    ->sortable(),
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
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\Filter::make('is_enabled')
                    ->query(fn (Builder $query): Builder => $query->where('is_enabled', true))
                    ->label('Enabled Only'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
                Tables\Actions\Action::make('sync_centers')
                    ->label('Sync to All Centers')
                    ->color('warning')
                    ->icon('heroicon-o-arrow-path')
                    ->action(function (RelationManager $livewire) {
                        $material = $livewire->getOwnerRecord();
                        $centers = RecyclingCenter::where('status', 'approved')->get();
                        
                        foreach ($centers as $center) {
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