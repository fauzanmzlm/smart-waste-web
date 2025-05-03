<?php

namespace App\Filament\Widgets;

use App\Models\RecyclingCenter;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class PendingCentersChart extends ChartWidget
{
    protected static ?string $heading = 'Recycling Center Registrations';

    protected int|string|array $columnSpan = 'full';
    
    protected static ?string $maxHeight = '300px';

    protected function getData(): array
    {
        $data = $this->getCenterRegistrationData();
        
        return [
            'datasets' => [
                [
                    'label' => 'Pending Centers',
                    'data' => $data['pending'],
                    'backgroundColor' => '#f59e0b',
                    'borderColor' => '#f59e0b',
                ],
                [
                    'label' => 'Approved Centers',
                    'data' => $data['approved'],
                    'backgroundColor' => '#10b981',
                    'borderColor' => '#10b981',
                ],
                [
                    'label' => 'Rejected Centers',
                    'data' => $data['rejected'],
                    'backgroundColor' => '#ef4444',
                    'borderColor' => '#ef4444',
                ],
            ],
            'labels' => $data['labels'],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
    
    protected function getCenterRegistrationData(): array
    {
        // Get the last 6 months
        $months = collect();
        $labels = [];
        $now = Carbon::now();
        
        // Build month intervals
        for ($i = 5; $i >= 0; $i--) {
            $date = $now->copy()->subMonths($i);
            $months->push([
                'month' => $date->month,
                'year' => $date->year,
                'label' => $date->format('M Y'),
            ]);
            $labels[] = $date->format('M Y');
        }
        
        // Initialize data arrays
        $pendingData = array_fill(0, 6, 0);
        $approvedData = array_fill(0, 6, 0);
        $rejectedData = array_fill(0, 6, 0);
        
        // Query centers by month and status
        foreach ($months as $index => $month) {
            $pendingData[$index] = RecyclingCenter::whereMonth('created_at', $month['month'])
                ->whereYear('created_at', $month['year'])
                ->where('status', 'pending')
                ->count();
                
            $approvedData[$index] = RecyclingCenter::whereMonth('created_at', $month['month'])
                ->whereYear('created_at', $month['year'])
                ->where('status', 'approved')
                ->count();
                
            $rejectedData[$index] = RecyclingCenter::whereMonth('created_at', $month['month'])
                ->whereYear('created_at', $month['year'])
                ->where('status', 'rejected')
                ->count();
        }
        
        return [
            'labels' => $labels,
            'pending' => $pendingData,
            'approved' => $approvedData,
            'rejected' => $rejectedData,
        ];
    }
}