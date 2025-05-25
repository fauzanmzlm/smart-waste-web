<?php

namespace App\Providers\Filament;

use App\Filament\Widgets\StatsOverview;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\MenuItem;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->colors([
                'primary' => Color::Emerald,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
                Widgets\FilamentInfoWidget::class,
                StatsOverview::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->authGuard('web')
            ->login()
            ->registration()
            ->passwordReset()
            ->emailVerification()
            ->profile()
            ->brandName('Smart Waste Admin')
            // ->brandLogo('https://placehold.co/1200x400')
            ->brandLogo(asset('assets/images/smartwaste-logo.png'))
            ->favicon('https://placehold.co/60x60')
            // ->favicon(asset('assets/images/favicon.png'))
            ->navigationGroups([
                'User Management',
                'Recycling Centers',
                'Waste Management',
                'Points & Rewards',
                'Community',
                'System',
            ])
            ->globalSearchKeyBindings(['command+k', 'ctrl+k'])
            ->sidebarCollapsibleOnDesktop()
            ->maxContentWidth('full')
            ->databaseNotifications()
            ->databaseNotificationsPolling('30s')
            // Define access for different user types
            ->tenantMenuItems([
                MenuItem::make()
                    ->label('Admin Dashboard')
                    ->icon('heroicon-o-home')
                    ->url(fn(): string => '/admin')
                    ->visible(fn(): bool => auth()->user()->account_type === 'Admin'),
                MenuItem::make()
                    ->label('Center Owner Dashboard')
                    ->icon('heroicon-o-building-storefront')
                    ->url(fn(): string => '/center-dashboard')
                    ->visible(fn(): bool => auth()->user()->account_type === 'CenterOwner'),
                MenuItem::make()
                    ->label('User Dashboard')
                    ->icon('heroicon-o-user')
                    ->url('/')
                    ->visible(fn(): bool => auth()->user()->account_type === 'Standard'),
            ]);
    }
}
