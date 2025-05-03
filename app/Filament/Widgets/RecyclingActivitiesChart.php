<?php

namespace App\Filament\Widgets;

use App\Models\RecyclingHistory;
use App\Models\WasteType;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class RecyclingActivitiesChart extends ChartWidget
{
    protected static ?string $heading = 'Recycling Activities';
    
    protected int|string|array $columnSpan = 'full';
    
    protected static ?string $maxHeight = '300px';
    
    protected static ?string $pollingInterval = '60s';
    
    protected function getData(): array
    {
        $data = $this->getRecyclingData();
        
        return [
            'datasets' => [
                [
                    'label' => 'Recycling Activities',
                    'data' => $data['data'],
                    'backgroundColor' => '#10b981',
                    'borderColor' => '#10b981',
                    'tension' => 0.3,
                    'fill' => false,
                ],
            ],
            'labels' => $data['labels'],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
    
    protected function getRecyclingData(): array
    {
        // Get the last 30 days
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
        
        // Initialize data array
        $data = array_fill(0, 30, 0);
        
        // Query recycling activities by day
        foreach ($days as $index => $day) {
            $data[$index] = RecyclingHistory::whereDate('created_at', $day['date'])->count();
        }
        
        return [
            'labels' => $labels,
            'data' => $data,
        ];
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
                ],
            ],
            'plugins' => [
                'legend' => [
                    'display' => true,
                ],
                'tooltip' => [
                    'enabled' => true,
                ],
            ],
        ];
    }
}