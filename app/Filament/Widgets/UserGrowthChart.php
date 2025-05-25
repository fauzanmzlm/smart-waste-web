<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Widgets\ChartWidget;
use Carbon\Carbon;

class UserGrowthChart extends ChartWidget
{
    protected static ?string $heading = 'User Growth Trend';
    protected int|string|array $columnSpan = 'full';
    protected static ?string $maxHeight = '300px';

    protected function getData(): array
    {
        // Get last 12 months of user growth
        $months = collect();
        $cumulativeUsers = [];
        $newUsers = [];
        $labels = [];
        
        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $startOfMonth = $date->copy()->startOfMonth();
            
            $labels[] = $date->format('M Y');
            
            // New users that month
            $newCount = User::whereMonth('created_at', $date->month)
                ->whereYear('created_at', $date->year)
                ->count();
                
            $newUsers[] = $newCount;
            
            // Cumulative users up to that month
            $cumulativeCount = User::where('created_at', '<', $date->copy()->endOfMonth())
                ->count();
                
            $cumulativeUsers[] = $cumulativeCount;
        }
        
        return [
            'datasets' => [
                [
                    'label' => 'New Users',
                    'data' => $newUsers,
                    'backgroundColor' => '#10b981',
                    'borderColor' => '#10b981',
                    'type' => 'bar',
                ],
                [
                    'label' => 'Total Users',
                    'data' => $cumulativeUsers,
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'borderColor' => '#3b82f6',
                    'borderWidth' => 2,
                    'type' => 'line',
                    'yAxisID' => 'y1',
                    'tension' => 0.3,
                    'fill' => true,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar'; // This will be a mixed chart due to our dataset configuration
    }
    
    protected function getOptions(): array
    {
        return [
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'title' => [
                        'display' => true,
                        'text' => 'New Users',
                    ],
                    'ticks' => [
                        'precision' => 0,
                    ],
                    'grid' => [
                        'display' => false,
                    ],
                ],
                'y1' => [
                    'beginAtZero' => true,
                    'position' => 'right',
                    'title' => [
                        'display' => true,
                        'text' => 'Total Users',
                    ],
                    'ticks' => [
                        'precision' => 0,
                    ],
                    'grid' => [
                        'display' => false,
                    ],
                ],
                'x' => [
                    'grid' => [
                        'display' => false,
                    ],
                ],
            ],
        ];
    }
}