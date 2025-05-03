<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Colors\Color;
use Filament\Tables;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Hash;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    
    protected static ?string $navigationGroup = 'User Management';
    
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('User Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('password')
                            ->password()
                            ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                            ->dehydrated(fn ($state) => filled($state))
                            ->required(fn (string $context): bool => $context === 'create'),
                        Forms\Components\TextInput::make('phone')
                            ->tel()
                            ->maxLength(20),
                        Forms\Components\TextInput::make('location')
                            ->maxLength(255),
                        Forms\Components\FileUpload::make('photoURL')
                            ->label('Profile Picture')
                            ->image()
                            ->directory('profile-pictures'),
                    ])->columns(2),
                
                Forms\Components\Section::make('Additional Information')
                    ->schema([
                        Forms\Components\Textarea::make('bio')
                            ->maxLength(500)
                            ->columnSpanFull(),
                        Forms\Components\Select::make('account_type')
                            ->options([
                                'Standard' => 'Standard',
                                'CenterOwner' => 'Center Owner',
                                'Admin' => 'Admin',
                            ])
                            ->default('Standard')
                            ->required(),
                        Forms\Components\Select::make('subscription_status')
                            ->options([
                                'Free' => 'Free',
                                'Pro' => 'Pro',
                                'Premium' => 'Premium',
                            ])
                            ->default('Free')
                            ->required(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('photoURL')
                    ->label('Avatar')
                    ->circular(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('account_type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Admin' => 'danger',
                        'CenterOwner' => 'warning',
                        default => 'success',
                    }),
                Tables\Columns\TextColumn::make('location')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('subscription_status')
                    ->badge(),
                Tables\Columns\TextColumn::make('last_login_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('account_type')
                    ->options([
                        'Standard' => 'Standard',
                        'CenterOwner' => 'Center Owner',
                        'Admin' => 'Admin',
                    ]),
                Filter::make('has_recycling_center')
                    ->label('Has Recycling Center')
                    ->query(fn (Builder $query): Builder => $query->whereHas('recyclingCenter')),
                Filter::make('created_this_month')
                    ->label('Created This Month')
                    ->query(fn (Builder $query): Builder => $query->whereMonth('created_at', now()->month)),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->button()->color(Color::Amber),
                Tables\Actions\DeleteAction::make()->button(),
                // Tables\Actions\Action::make('impersonate')
                //     ->label('Login As')
                //     ->icon('heroicon-o-finger-print')
                //     ->color('danger')
                //     ->requiresConfirmation()
                //     ->action(function (User $user) {
                //         // In a real application, you'd implement impersonation here
                //         // This is just a placeholder
                //     }),
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
            RelationManagers\RecyclingHistoriesRelationManager::class,
            RelationManagers\BadgesRelationManager::class,
            RelationManagers\PointsTransactionsRelationManager::class,
            RelationManagers\RewardRedemptionsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}