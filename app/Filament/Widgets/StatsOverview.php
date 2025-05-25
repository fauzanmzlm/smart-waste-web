<?php

namespace App\Filament\Widgets;

use App\Models\User;
use App\Models\RecyclingCenter;
use App\Models\RecyclingHistory;
use App\Models\RewardRedemption;
use App\Models\WasteItem;
use App\Models\WasteType;
use App\Models\PointsTransaction;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Carbon\Carbon;

class StatsOverview extends BaseWidget
{
    protected static ?string $pollingInterval = '60s';

    protected function getStats(): array
    {
        $month = Carbon::now()->format('F');

        // Enhanced user metrics
        $totalUsers = User::count();
        $newUsers = User::whereMonth('created_at', Carbon::now()->month)->count();
        $userGrowthPercent = $this->calculateGrowthPercentage(
            User::whereMonth('created_at', Carbon::now()->subMonth()->month)->count(),
            $newUsers
        );

        // Enhanced center metrics
        $totalCenters = RecyclingCenter::count();
        $pendingCenters = RecyclingCenter::where('status', 'pending')->count();
        $activeCenters = RecyclingCenter::where('is_active', true)->count();
        $centerActivePercent = $totalCenters > 0 ? round(($activeCenters / $totalCenters) * 100) : 0;

        // Enhanced recycling metrics
        $totalRecycled = RecyclingHistory::count();
        $recycledThisMonth = RecyclingHistory::whereMonth('created_at', Carbon::now()->month)->count();
        $recycledLastMonth = RecyclingHistory::whereMonth('created_at', Carbon::now()->subMonth()->month)->count();
        $recyclingGrowthPercent = $this->calculateGrowthPercentage($recycledLastMonth, $recycledThisMonth);

        // Points metrics (new)
        $totalPointsAwarded = PointsTransaction::earned()->sum('points');
        $pointsThisMonth = PointsTransaction::earned()
            ->whereMonth('created_at', Carbon::now()->month)
            ->sum('points');
        $pointsLastMonth = PointsTransaction::earned()
            ->whereMonth('created_at', Carbon::now()->subMonth()->month)
            ->sum('points');
        $pointsGrowthPercent = $this->calculateGrowthPercentage($pointsLastMonth, $pointsThisMonth);

        // Redemption metrics
        $pendingRedemptions = RewardRedemption::where('status', 'pending')->count();
        $completedRedemptions = RewardRedemption::where('status', 'approved')->count();
        $redemptionsThisMonth = RewardRedemption::whereMonth('created_at', Carbon::now()->month)->count();
        $redemptionsLastMonth = RewardRedemption::whereMonth('created_at', Carbon::now()->subMonth()->month)->count();
        $redemptionsGrowthPercent = $this->calculateGrowthPercentage($redemptionsLastMonth, $redemptionsThisMonth);

        // Waste type metrics (new)
        $totalWasteTypes = WasteType::count();
        $totalWasteItems = WasteItem::count();
        $recyclableItems = WasteItem::where('recyclable', true)->count();
        $recyclablePercent = $totalWasteItems > 0 ? round(($recyclableItems / $totalWasteItems) * 100) : 0;

        return [
            // User stats with growth indicator
            Stat::make('Total Users', $totalUsers)
                ->description($userGrowthPercent >= 0
                    ? "+{$userGrowthPercent}% growth ({$newUsers} new)"
                    : "{$userGrowthPercent}% decrease ({$newUsers} new)")
                ->descriptionIcon($userGrowthPercent >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->chart([
                    $this->getUserCountForMonth(6),
                    $this->getUserCountForMonth(5),
                    $this->getUserCountForMonth(4),
                    $this->getUserCountForMonth(3),
                    $this->getUserCountForMonth(2),
                    $this->getUserCountForMonth(1)
                ])
                ->color($userGrowthPercent >= 0 ? 'success' : 'danger'),

            // Center stats with activity percentage
            Stat::make('Recycling Centers', $totalCenters)
                ->description("{$centerActivePercent}% active, {$pendingCenters} pending")
                ->descriptionIcon('heroicon-m-building-storefront')
                ->chart([
                    $this->getCenterCountForMonth(6),
                    $this->getCenterCountForMonth(5),
                    $this->getCenterCountForMonth(4),
                    $this->getCenterCountForMonth(3),
                    $this->getCenterCountForMonth(2),
                    $this->getCenterCountForMonth(1)
                ])
                ->color($pendingCenters > 0 ? 'warning' : 'success'),

            // Recycling activity with growth comparison
            Stat::make('Recycling Activities', $totalRecycled)
                ->description($recyclingGrowthPercent >= 0
                    ? "+{$recyclingGrowthPercent}% vs last month"
                    : "{$recyclingGrowthPercent}% vs last month")
                ->descriptionIcon($recyclingGrowthPercent >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->chart([
                    $this->getRecyclingCountForMonth(6),
                    $this->getRecyclingCountForMonth(5),
                    $this->getRecyclingCountForMonth(4),
                    $this->getRecyclingCountForMonth(3),
                    $this->getRecyclingCountForMonth(2),
                    $this->getRecyclingCountForMonth(1)
                ])
                ->color($recyclingGrowthPercent >= 0 ? 'success' : 'danger'),

            // Points metrics (new)
            Stat::make('Total Points Awarded', number_format($totalPointsAwarded))
                ->description($pointsGrowthPercent >= 0
                    ? "+{$pointsGrowthPercent}% vs last month"
                    : "{$pointsGrowthPercent}% vs last month")
                ->descriptionIcon($pointsGrowthPercent >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->chart([
                    $this->getPointsForMonth(6),
                    $this->getPointsForMonth(5),
                    $this->getPointsForMonth(4),
                    $this->getPointsForMonth(3),
                    $this->getPointsForMonth(2),
                    $this->getPointsForMonth(1)
                ])
                ->color($pointsGrowthPercent >= 0 ? 'success' : 'danger'),

            // Redemption stats with pending actions
            Stat::make('Rewards Redeemed', $completedRedemptions)
                ->description($pendingRedemptions === 1
                    ? "1 pending redemption"
                    : "{$pendingRedemptions} pending redemptions")
                ->descriptionIcon('heroicon-m-gift')
                ->chart([
                    $this->getRedemptionsForMonth(6),
                    $this->getRedemptionsForMonth(5),
                    $this->getRedemptionsForMonth(4),
                    $this->getRedemptionsForMonth(3),
                    $this->getRedemptionsForMonth(2),
                    $this->getRedemptionsForMonth(1)
                ])
                ->color($pendingRedemptions > 0 ? 'warning' : 'success'),

            // Waste metrics (new)
            Stat::make('Waste Database (Types, Items)', "{$totalWasteTypes}, {$totalWasteItems}")
                ->description("{$recyclablePercent}% of items are recyclable")
                ->descriptionIcon('heroicon-m-squares-2x2')
                ->color('success'),
        ];
    }

    // Helper methods to get historical data for charts

    private function calculateGrowthPercentage($previous, $current)
    {
        if ($previous === 0) {
            return $current > 0 ? 100 : 0;
        }

        return round((($current - $previous) / $previous) * 100);
    }

    private function getUserCountForMonth($monthsAgo)
    {
        $date = Carbon::now()->subMonths($monthsAgo - 1);
        return User::whereMonth('created_at', $date->month)
            ->whereYear('created_at', $date->year)
            ->count();
    }

    private function getCenterCountForMonth($monthsAgo)
    {
        $date = Carbon::now()->subMonths($monthsAgo - 1);
        return RecyclingCenter::whereMonth('created_at', $date->month)
            ->whereYear('created_at', $date->year)
            ->count();
    }

    private function getRecyclingCountForMonth($monthsAgo)
    {
        $date = Carbon::now()->subMonths($monthsAgo - 1);
        return RecyclingHistory::whereMonth('created_at', $date->month)
            ->whereYear('created_at', $date->year)
            ->count();
    }

    private function getPointsForMonth($monthsAgo)
    {
        $date = Carbon::now()->subMonths($monthsAgo - 1);
        return PointsTransaction::earned()
            ->whereMonth('created_at', $date->month)
            ->whereYear('created_at', $date->year)
            ->sum('points') / 1000; // Scaled for chart visualization
    }

    private function getRedemptionsForMonth($monthsAgo)
    {
        $date = Carbon::now()->subMonths($monthsAgo - 1);
        return RewardRedemption::whereMonth('created_at', $date->month)
            ->whereYear('created_at', $date->year)
            ->count();
    }
}
