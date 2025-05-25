<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Filament\Widgets\StatsOverview;
use App\Filament\Widgets\LatestUsers;
use App\Filament\Widgets\PendingCentersWidget; // New widget
use App\Filament\Widgets\RecyclingActivitiesChart; // Enhanced chart
use App\Filament\Widgets\RewardsRedemptionChart;
use App\Filament\Widgets\WasteTypeDistributionChart; // New chart
use App\Filament\Widgets\UserGrowthChart; // New chart
use App\Filament\Widgets\TopRecyclingCentersTable; // New widget
use App\Filament\Widgets\PointsLeaderboardWidget; // New widget

class Dashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-home';
    protected static string $view = 'filament.pages.dashboard';

    // Only include widgets in one place to avoid duplication
    protected function getHeaderWidgets(): array
    {
        return [
            StatsOverview::class,
            UserGrowthChart::class,
            RecyclingActivitiesChart::class,
            WasteTypeDistributionChart::class,
            RewardsRedemptionChart::class,
            TopRecyclingCentersTable::class,
            PendingCentersWidget::class,
            PointsLeaderboardWidget::class,
            LatestUsers::class,
        ];
    }

    public function getWidgets(): array
    {
        return [
            // UserGrowthChart::class,
            // RecyclingActivitiesChart::class,
            // WasteTypeDistributionChart::class,
            // RewardsRedemptionChart::class,

            // PendingCentersWidget::class,

            // TopRecyclingCentersTable::class,
            // PointsLeaderboardWidget::class,
            // LatestUsers::class,
        ];
    }
}
