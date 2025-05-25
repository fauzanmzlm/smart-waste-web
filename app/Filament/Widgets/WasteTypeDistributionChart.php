<?php

namespace App\Filament\Widgets;

use App\Models\RecyclingHistory;
use App\Models\WasteType;
use App\Models\WasteItem;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class WasteTypeDistributionChart extends ChartWidget
{
    protected static ?string $heading = 'Waste Type Distribution';
    protected static ?string $pollingInterval = '300s';
    protected int|string|array $columnSpan = 'full';
    protected static ?string $maxHeight = '300px';

    protected function getData(): array
    {
        // Get recycling history distribution by waste type
        $wasteTypes = WasteType::all();
        
        $recyclingData = RecyclingHistory::select('waste_items.waste_type_id', DB::raw('count(*) as total'))
            ->join('waste_items', 'recycling_histories.waste_item_id', '=', 'waste_items.id')
            ->groupBy('waste_items.waste_type_id')
            ->get()
            ->keyBy('waste_type_id');
            
        $labels = [];
        $data = [];
        $backgroundColor = [];
        
        foreach ($wasteTypes as $wasteType) {
            $labels[] = $wasteType->name;
            $data[] = $recyclingData->get($wasteType->id)?->total ?? 0;
            $backgroundColor[] = $wasteType->color ?? '#' . substr(md5($wasteType->name), 0, 6);
        }
        
        return [
            'datasets' => [
                [
                    'label' => 'Items Recycled',
                    'data' => $data,
                    'backgroundColor' => $backgroundColor,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'pie';
    }
    
    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'position' => 'right',
                ],
                'tooltip' => [
                    'enabled' => true,
                ],
            ],
        ];
    }
}