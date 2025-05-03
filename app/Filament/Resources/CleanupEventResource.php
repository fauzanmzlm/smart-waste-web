<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CleanupEventResource\Pages;
use App\Filament\Resources\CleanupEventResource\RelationManagers;
use App\Models\CleanupEvent;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CleanupEventResource extends Resource
{
    protected static ?string $model = CleanupEvent::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar';
    
    protected static ?string $navigationGroup = 'Community';
    
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Event Details')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\DatePicker::make('date')
                            ->required(),
                        Forms\Components\TextInput::make('time')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\FileUpload::make('image')
                            ->image()
                            ->directory('cleanup-events'),
                    ])->columns(2),
                
                Forms\Components\Section::make('Location')
                    ->schema([
                        Forms\Components\TextInput::make('location')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('latitude')
                            ->required()
                            ->numeric(),
                        Forms\Components\TextInput::make('longitude')
                            ->required()
                            ->numeric(),
                    ])->columns(2),
                
                Forms\Components\Section::make('Organization')
                    ->schema([
                        Forms\Components\TextInput::make('organizer')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('website')
                            ->url()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('contact_number')
                            ->tel()
                            ->maxLength(20),
                    ])->columns(2),
                
                Forms\Components\Section::make('Description')
                    ->schema([
                        Forms\Components\Textarea::make('description')
                            ->required()
                            ->rows(5),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    ->circular(),
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('time'),
                Tables\Columns\TextColumn::make('location')
                    ->searchable()
                    ->limit(30),
                Tables\Columns\TextColumn::make('organizer')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\Filter::make('upcoming')
                    ->query(fn (Builder $query): Builder => $query->where('date', '>=', now()->format('Y-m-d')))
                    ->label('Upcoming Events'),
                Tables\Filters\Filter::make('past')
                    ->query(fn (Builder $query): Builder => $query->where('date', '<', now()->format('Y-m-d')))
                    ->label('Past Events'),
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCleanupEvents::route('/'),
            'create' => Pages\CreateCleanupEvent::route('/create'),
            'edit' => Pages\EditCleanupEvent::route('/{record}/edit'),
        ];
    }
    
    // Add a calendar view of events
    public static function getWidgets(): array
    {
        return [
            \App\Filament\Widgets\EventsCalendar::class,
        ];
    }
}