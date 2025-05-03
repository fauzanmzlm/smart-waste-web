<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use Filament\Pages\Page;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Cache;

class Settings extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static string $view = 'filament.pages.settings';
    
    protected static ?string $navigationGroup = 'System';
    
    protected static ?int $navigationSort = 2;
    
    public ?array $data = [];
    
    public function mount(): void
    {
        $settings = Setting::all()->pluck('value', 'key')->toArray();
        
        $this->form->fill($settings);
    }
    
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('General Settings')
                    ->schema([
                        Forms\Components\TextInput::make('app_name')
                            ->label('Application Name')
                            ->required()
                            ->maxLength(255)
                            ->default('Smart Waste'),
                        Forms\Components\TextInput::make('contact_email')
                            ->label('Contact Email')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->default('contact@smartwaste.com'),
                        Forms\Components\TextInput::make('contact_phone')
                            ->label('Contact Phone')
                            ->tel()
                            ->maxLength(20),
                        Forms\Components\Toggle::make('maintenance_mode')
                            ->label('Maintenance Mode')
                            ->helperText('Enable maintenance mode for the application')
                            ->default(false),
                    ])->columns(2),
                
                Forms\Components\Section::make('Points & Rewards Settings')
                    ->schema([
                        Forms\Components\TextInput::make('base_points_multiplier')
                            ->label('Base Points Multiplier')
                            ->numeric()
                            ->minValue(0.1)
                            ->step(0.1)
                            ->default(1.0)
                            ->required(),
                        Forms\Components\TextInput::make('minimum_points_redemption')
                            ->label('Minimum Points for Redemption')
                            ->numeric()
                            ->minValue(1)
                            ->step(1)
                            ->default(100)
                            ->required(),
                        Forms\Components\Toggle::make('enable_rewards')
                            ->label('Enable Rewards System')
                            ->default(true)
                            ->required(),
                        Forms\Components\Toggle::make('auto_approve_rewards')
                            ->label('Auto-Approve Center Rewards')
                            ->helperText('Automatically approve new rewards created by center owners')
                            ->default(false),
                        Forms\Components\TextInput::make('max_points_per_day')
                            ->label('Maximum Points Per Day')
                            ->helperText('Set 0 for unlimited')
                            ->numeric()
                            ->minValue(0)
                            ->default(0),
                    ])->columns(2),
                
                Forms\Components\Section::make('Recycling Center Settings')
                    ->schema([
                        Forms\Components\Toggle::make('auto_approve_centers')
                            ->label('Auto-Approve New Centers')
                            ->helperText('Automatically approve new recycling center registrations')
                            ->default(false),
                        Forms\Components\TextInput::make('center_approval_interval')
                            ->label('Center Approval Interval (Hours)')
                            ->helperText('Set how many hours before automatic approval (if enabled)')
                            ->numeric()
                            ->minValue(1)
                            ->default(24),
                        Forms\Components\TextInput::make('max_centers_per_user')
                            ->label('Max Centers Per User')
                            ->helperText('Maximum number of centers a user can register (0 for unlimited)')
                            ->numeric()
                            ->minValue(0)
                            ->default(1),
                    ])->columns(2),
                
                Forms\Components\Section::make('Mobile App Settings')
                    ->schema([
                        Forms\Components\TextInput::make('min_app_version')
                            ->label('Minimum App Version')
                            ->helperText('Minimum required version for the mobile app')
                            ->required()
                            ->default('1.0.0'),
                        Forms\Components\Toggle::make('force_update')
                            ->label('Force App Update')
                            ->helperText('Force users to update their app to the latest version')
                            ->default(false),
                        Forms\Components\Textarea::make('app_announcement')
                            ->label('App Announcement')
                            ->helperText('Display an announcement message in the app')
                            ->rows(3),
                    ])->columns(2),
                
                Forms\Components\Section::make('Notification Settings')
                    ->schema([
                        Forms\Components\Toggle::make('email_notifications')
                            ->label('Email Notifications')
                            ->default(true),
                        Forms\Components\Toggle::make('push_notifications')
                            ->label('Push Notifications')
                            ->default(true),
                        Forms\Components\Checkbox::make('notify_center_registration')
                            ->label('Center Registration')
                            ->default(true),
                        Forms\Components\Checkbox::make('notify_reward_redemption')
                            ->label('Reward Redemption')
                            ->default(true),
                        Forms\Components\Checkbox::make('notify_center_approval')
                            ->label('Center Approval')
                            ->default(true),
                    ])->columns(2),
            ])
            ->statePath('data');
    }
    
    public function save(): void
    {
        $data = $this->form->getState();
        
        foreach ($data as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }
        
        // Clear settings cache
        Cache::forget('settings');
        
        Notification::make()
            ->title('Settings saved successfully')
            ->success()
            ->send();
    }
    
    public function restoreDefaults(): void
    {
        $this->form->fill([
            'app_name' => 'Smart Waste',
            'contact_email' => 'contact@smartwaste.com',
            'contact_phone' => '',
            'maintenance_mode' => false,
            'base_points_multiplier' => 1.0,
            'minimum_points_redemption' => 100,
            'enable_rewards' => true,
            'auto_approve_rewards' => false,
            'max_points_per_day' => 0,
            'auto_approve_centers' => false,
            'center_approval_interval' => 24,
            'max_centers_per_user' => 1,
            'min_app_version' => '1.0.0',
            'force_update' => false,
            'app_announcement' => '',
            'email_notifications' => true,
            'push_notifications' => true,
            'notify_center_registration' => true,
            'notify_reward_redemption' => true,
            'notify_center_approval' => true,
        ]);
    }
}