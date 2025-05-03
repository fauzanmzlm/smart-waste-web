<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BadgesRelationManager extends RelationManager
{
    protected static string $relationship = 'badges';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('description')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('icon')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('points_reward')
                    ->numeric()
                    ->default(0),
                Forms\Components\DateTimePicker::make('pivot.earned_at')
                    ->label('Earned At')
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('icon'),
                Tables\Columns\TextColumn::make('description')
                    ->limit(30),
                Tables\Columns\TextColumn::make('points_reward')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('pivot.earned_at')
                    ->label('Earned At')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    ->preloadRecordSelect()
                    ->form(fn(Tables\Actions\AttachAction $action): array => [
                        $action->getRecordSelect(),
                        Forms\Components\DateTimePicker::make('earned_at')
                            ->default(now())
                            ->required(),
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->button()
                    ->color('warning')
                    ->mutateRecordDataUsing(function (array $data): array {
                        $data['pivot.earned_at'] = $data['pivot']['earned_at'];
                        return $data;
                    })
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['pivot']['earned_at'] = $data['pivot.earned_at'];
                        return $data;
                    }),
                Tables\Actions\DetachAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DetachBulkAction::make(),
                ]),
            ]);
    }
}
