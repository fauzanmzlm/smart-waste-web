<?php

namespace App\Filament\Widgets;

use App\Models\User;
use App\Models\RecyclingCenter;
use App\Models\RecyclingHistory;
use App\Models\RewardRedemption;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Carbon\Carbon;

class StatsOverview extends BaseWidget
{
    protected static ?string $pollingInterval = '60s';

    protected function getStats(): array
    {
        $month = Carbon::now()->format('F');
        
        // Get user count and new users this month
        $totalUsers = User::count();
        $newUsers = User::whereMonth('created_at', Carbon::now()->month)->count();
        
        // Get center stats
        $totalCenters = RecyclingCenter::count();
        $pendingCenters = RecyclingCenter::where('status', 'pending')->count();
        
        // Get recycling activity stats
        $totalRecycled = RecyclingHistory::count();
        $recycledThisMonth = RecyclingHistory::whereMonth('created_at', Carbon::now()->month)->count();
        
        // Get redemption stats
        $pendingRedemptions = RewardRedemption::where('status', 'pending')->count();
        $completedRedemptions = RewardRedemption::where('status', 'approved')->count();
        
        return [
            Stat::make('Total Users', $totalUsers)
                ->description($newUsers . ' new users this month')
                ->descriptionIcon('heroicon-m-user-plus')
                ->chart([5, 4, 6, 7, 8, $newUsers])
                ->color('success'),
                
            Stat::make('Recycling Centers', $totalCenters)
                ->description($pendingCenters . ' centers pending approval')
                ->descriptionIcon('heroicon-m-building-storefront')
                ->color($pendingCenters > 0 ? 'warning' : 'success'),
                
            Stat::make('Recycling Activities', $totalRecycled)
                ->description($recycledThisMonth . ' items recycled in ' . $month)
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->chart([6, 9, 8, 10, 11, $recycledThisMonth])
                ->color('success'),
                
            Stat::make('Rewards Redemptions', $completedRedemptions)
                ->description($pendingRedemptions . ' pending redemptions')
                ->descriptionIcon('heroicon-m-gift')
                ->color($pendingRedemptions > 0 ? 'warning' : 'success'),
        ];
    }
}