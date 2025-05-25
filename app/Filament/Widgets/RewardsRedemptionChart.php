<?php

namespace App\Filament\Widgets;

use App\Models\RewardRedemption;
use Filament\Widgets\ChartWidget;

class RewardsRedemptionChart extends ChartWidget
{
    protected static ?string $heading = 'Rewards Redemption Status';
    protected static ?string $pollingInterval = '60s';

    protected function getData(): array
    {
        $pendingCount = RewardRedemption::where('status', 'pending')->count();
        $approvedCount = RewardRedemption::where('status', 'approved')->count();
        $rejectedCount = RewardRedemption::where('status', 'rejected')->count();

        return [
            'datasets' => [
                [
                    'data' => [$pendingCount, $approvedCount, $rejectedCount],
                    'backgroundColor' => ['#f59e0b', '#10b981', '#ef4444'],
                ],
            ],
            'labels' => ['Pending', 'Approved', 'Rejected'],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'bottom',
                ],
                'tooltip' => [
                    'enabled' => true,
                ],
            ],
            'cutout' => '70%',
        ];
    }
}
