<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RecyclingCenterResource\Pages;
use App\Filament\Resources\RecyclingCenterResource\RelationManagers;
use App\Models\RecyclingCenter;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Support\Colors\Color;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class RecyclingCenterResource extends Resource
{
    protected static ?string $model = RecyclingCenter::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-storefront';

    protected static ?string $navigationGroup = 'Recycling Centers';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Basic Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Select::make('user_id')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('email')
                                    ->email()
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('password')
                                    ->password()
                                    ->required()
                                    ->maxLength(255),
                            ]),
                        Forms\Components\FileUpload::make('image')
                            ->image()
                            ->directory('recycling-centers')
                            ->columnSpanFull(),
                        Forms\Components\RichEditor::make('description')
                            // ->rows(3)
                            ->columnSpanFull(),
                    ])->columns(2),

                Forms\Components\Section::make('Location & Contact')
                    ->schema([
                        Forms\Components\Textarea::make('address')
                            ->required()
                            ->rows(3)
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('phone')
                            ->tel()
                            ->maxLength(20),
                        Forms\Components\TextInput::make('website')
                            ->url()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('latitude')
                            ->numeric()
                            ->required(),
                        Forms\Components\TextInput::make('longitude')
                            ->numeric()
                            ->required(),
                        Forms\Components\KeyValue::make('hours')
                            ->keyLabel('Day')
                            ->valueLabel('Hours')
                            ->columnSpanFull(),
                    ])->columns(2),

                Forms\Components\Section::make('Status & Settings')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->options([
                                'pending' => 'Pending',
                                'approved' => 'Approved',
                                'rejected' => 'Rejected',
                            ])
                            ->required()
                            ->default('pending'),
                        Forms\Components\TextInput::make('points_multiplier')
                            ->numeric()
                            ->default(1.0)
                            ->step(0.1),
                        Forms\Components\Toggle::make('is_active')
                            ->label('Active')
                            ->default(false),
                        Forms\Components\Textarea::make('rejection_reason')
                            ->rows(2)
                            ->maxLength(500)
                            ->visible(fn(Forms\Get $get) => $get('status') === 'rejected'),
                    ])->columns(2),

                Forms\Components\Section::make('Accepted Waste Types')
                    ->schema([
                        Forms\Components\CheckboxList::make('wasteTypes')
                            ->relationship('wasteTypes', 'name')
                            ->columns(3)
                            ->gridDirection('row'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    ->circular(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
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
                    })
                    ->formatStateUsing(fn(string $state) => Str::ucfirst($state)),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
                Tables\Columns\TextColumn::make('address')
                    ->limit(30)
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('phone')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('points_multiplier')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ]),
                Filter::make('is_active')
                    ->label('Active Centers')
                    ->query(fn(Builder $query): Builder => $query->where('is_active', true)),
                Filter::make('created_this_month')
                    ->label('Created This Month')
                    ->query(fn(Builder $query): Builder => $query->whereMonth('created_at', now()->month)),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->button()->color(Color::Amber),
                Tables\Actions\Action::make('approve')
                    ->label('Approve')
                    ->button()
                    ->icon('heroicon-s-check-circle')
                    ->color('success')
                    ->visible(fn(RecyclingCenter $record) => $record->status === 'pending')
                    ->action(function (RecyclingCenter $record) {
                        $record->status = 'approved';
                        $record->save();
                    }),
                Tables\Actions\Action::make('reject')
                    ->label('Reject')
                    ->button()
                    ->icon('heroicon-s-x-circle')
                    ->color('danger')
                    ->form([
                        Forms\Components\Textarea::make('rejection_reason')
                            ->label('Reason for Rejection')
                            ->required(),
                    ])
                    ->visible(fn(RecyclingCenter $record) => $record->status === 'pending')
                    ->action(function (RecyclingCenter $record, array $data) {
                        $record->status = 'rejected';
                        $record->rejection_reason = $data['rejection_reason'];
                        $record->is_active = false;
                        $record->save();
                    }),
                Tables\Actions\Action::make('toggle_active')
                    ->label(fn(RecyclingCenter $record) => $record->is_active ? 'Deactivate' : 'Activate')
                    ->icon(fn(RecyclingCenter $record) => $record->is_active ? 'heroicon-o-pause' : 'heroicon-o-play')
                    ->color(fn(RecyclingCenter $record) => $record->is_active ? 'danger' : 'success')
                    ->visible(fn(RecyclingCenter $record) => $record->status === 'approved')
                    ->action(function (RecyclingCenter $record) {
                        $record->is_active = !$record->is_active;
                        $record->save();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('approve_selected')
                        ->label('Approve Selected')
                        ->icon('heroicon-o-check')
                        ->color('success')
                        ->action(function (Collection $records) {
                            $records->each(function ($record) {
                                if ($record->status === 'pending') {
                                    $record->status = 'approved';
                                    $record->save();
                                }
                            });
                        }),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\WasteTypesRelationManager::class,
            RelationManagers\MaterialPointConfigsRelationManager::class,
            RelationManagers\RewardsRelationManager::class,
            RelationManagers\PointsTransactionsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRecyclingCenters::route('/'),
            'create' => Pages\CreateRecyclingCenter::route('/create'),
            'edit' => Pages\EditRecyclingCenter::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'pending')->count() ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }
}
