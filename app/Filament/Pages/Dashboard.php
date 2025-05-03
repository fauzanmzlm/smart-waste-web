<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class Dashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-home';

    protected static string $view = 'filament.pages.dashboard';

    public function getWidgets(): array
    {
        return [
            \App\Filament\Widgets\StatsOverview::class,
            \App\Filament\Widgets\LatestUsers::class,
            \App\Filament\Widgets\PendingCentersChart::class,
            \App\Filament\Widgets\RecyclingActivitiesChart::class,
            \App\Filament\Widgets\RewardsRedemptionChart::class,
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Widgets\EventsCalendar::class,
            \App\Filament\Widgets\StatsOverview::class,
        ];
    }
}
