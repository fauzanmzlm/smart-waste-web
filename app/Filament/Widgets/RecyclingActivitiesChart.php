<?php

namespace App\Filament\Widgets;

use App\Models\RecyclingHistory;
use App\Models\WasteType;
use Filament\Widgets\ChartWidget;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class RecyclingActivitiesChart extends ChartWidget
{
    protected static ?string $heading = 'Recycling Activity Trends';
    protected int|string|array $columnSpan = 'full';
    protected static ?string $maxHeight = '300px';
    protected static ?string $pollingInterval = '60s';
    
    // Add filter options
    protected function getFilters(): ?array
    {
        return [
            'day' => 'Last 30 days',
            'week' => 'Last 12 weeks',
            'month' => 'Last 12 months',
        ];
    }
    
    protected function getData(): array
    {
        $filter = $this->filter ?? 'day';
        
        switch ($filter) {
            case 'week':
                return $this->getWeeklyData();
            case 'month':
                return $this->getMonthlyData();
            default: // day
                return $this->getDailyData();
        }
    }
    
    protected function getDailyData(): array
    {
        // Get the last 30 days of daily recycling activity
        $days = collect();
        $labels = [];
        $now = Carbon::now();
        
        // Build day intervals
        for ($i = 29; $i >= 0; $i--) {
            $date = $now->copy()->subDays($i);
            $days->push([
                'date' => $date->format('Y-m-d'),
                'label' => $date->format('M d'),
            ]);
            $labels[] = $date->format('M d');
        }
        
        // Get daily recycling counts
        $recyclingCounts = RecyclingHistory::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as count')
            )
            ->where('created_at', '>=', $now->copy()->subDays(30))
            ->groupBy('date')
            ->get()
            ->keyBy('date');
        
        // Initialize data array
        $data = [];
        
        // Populate data array
        foreach ($days as $day) {
            $date = $day['date'];
            $data[] = $recyclingCounts->get($date)?->count ?? 0;
        }
        
        return [
            'datasets' => [
                [
                    'label' => 'Items Recycled',
                    'data' => $data,
                    'backgroundColor' => 'rgba(16, 185, 129, 0.2)',
                    'borderColor' => '#10b981',
                    'borderWidth' => 2,
                    'tension' => 0.3,
                    'fill' => true,
                    'pointRadius' => 2,
                    'pointHoverRadius' => 5,
                ],
            ],
            'labels' => $labels,
        ];
    }
    
    protected function getWeeklyData(): array
    {
        // Get the last 12 weeks of weekly recycling activity
        $weeks = collect();
        $labels = [];
        $now = Carbon::now();
        
        // Build week intervals
        for ($i = 11; $i >= 0; $i--) {
            $startDate = $now->copy()->subWeeks($i)->startOfWeek();
            $endDate = $startDate->copy()->endOfWeek();
            
            $weeks->push([
                'start' => $startDate->format('Y-m-d'),
                'end' => $endDate->format('Y-m-d'),
                'label' => $startDate->format('M d') . ' - ' . $endDate->format('M d'),
            ]);
            
            $labels[] = 'Week ' . ($i === 0 ? 'Current' : (12 - $i));
        }
        
        // Get weekly recycling counts
        $data = [];
        
        foreach ($weeks as $week) {
            $count = RecyclingHistory::whereBetween(
                    'created_at', 
                    [$week['start'] . ' 00:00:00', $week['end'] . ' 23:59:59']
                )
                ->count();
                
            $data[] = $count;
        }
        
        return [
            'datasets' => [
                [
                    'label' => 'Items Recycled',
                    'data' => $data,
                    'backgroundColor' => '#10b981',
                    'borderColor' => '#10b981',
                ],
            ],
            'labels' => $labels,
        ];
    }
    
    protected function getMonthlyData(): array
    {
        // Get the last 12 months of monthly recycling activity
        $months = collect();
        $labels = [];
        $now = Carbon::now();
        
        // Build month intervals
        for ($i = 11; $i >= 0; $i--) {
            $date = $now->copy()->subMonths($i);
            
            $months->push([
                'month' => $date->month,
                'year' => $date->year,
                'label' => $date->format('M Y'),
            ]);
            
            $labels[] = $date->format('M Y');
        }
        
        // Get monthly recycling counts
        $data = [];
        
        foreach ($months as $month) {
            $count = RecyclingHistory::whereMonth('created_at', $month['month'])
                ->whereYear('created_at', $month['year'])
                ->count();
                
            $data[] = $count;
        }
        
        return [
            'datasets' => [
                [
                    'label' => 'Items Recycled',
                    'data' => $data,
                    'backgroundColor' => '#10b981',
                    'borderColor' => '#10b981',
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        $filter = $this->filter ?? 'day';
        
        return $filter === 'day' ? 'line' : 'bar';
    }
    
    protected function getOptions(): array
    {
        return [
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'precision' => 0,
                    ],
                    'title' => [
                        'display' => true,
                        'text' => 'Items Recycled',
                    ],
                ],
                'x' => [
                    'grid' => [
                        'display' => false,
                    ],
                ],
            ],
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'top',
                ],
                'tooltip' => [
                    'enabled' => true,
                ],
            ],
        ];
    }
}