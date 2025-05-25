<?php

namespace App\Filament\Widgets;

use App\Models\RecyclingCenter;
use App\Models\RecyclingHistory;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TopRecyclingCentersTable extends BaseWidget
{
    protected static ?string $heading = 'Top Performing Recycling Centers';
    protected static ?string $pollingInterval = '60s';
    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        // Get centers with the most recycling activity
        return $table
            ->query(
                RecyclingCenter::query()
                    ->select('recycling_centers.*')
                    ->addSelect(DB::raw('COUNT(recycling_histories.id) as recycling_count'))
                    ->addSelect(DB::raw('SUM(points_transactions.points) as points_awarded'))
                    ->leftJoin('recycling_histories', 'recycling_centers.id', '=', 'recycling_histories.center_id')
                    ->leftJoin('points_transactions', function ($join) {
                        $join->on('recycling_centers.id', '=', 'points_transactions.center_id')
                            ->where('points_transactions.type', '=', 'earned')
                            ->where('points_transactions.category', '=', 'recycling');
                    })
                    ->where('recycling_centers.status', 'approved')
                    ->where('recycling_centers.is_active', true)
                    ->groupBy('recycling_centers.id')
                    ->orderByDesc('recycling_count')
                    ->orderByDesc('points_awarded')
                    ->limit(5)
            )
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    ->label('Image')
                    ->circular(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('recycling_count')
                    ->label('Items Recycled')
                    ->sortable()
                    ->badge(),
                Tables\Columns\TextColumn::make('points_awarded')
                    ->label('Points Awarded')
                    ->sortable()
                    ->formatStateUsing(fn($state) => $state ? number_format($state) : 0)
                    ->badge()
                    ->color('success'),
                Tables\Columns\TextColumn::make('wasteTypes.name')
                    ->label('Waste Types')
                    ->badge(),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->url(fn(RecyclingCenter $record): string => url('/admin/recycling-centers/' . $record->id . '/edit'))
                    ->icon('heroicon-m-eye'),
                Tables\Actions\Action::make('view_activity')
                    ->label('View Activity')
                    ->icon('heroicon-m-chart-bar')
                    ->url(fn(RecyclingCenter $record): string => url('/admin/analytics?center_id=' . $record->id)),
            ]);
    }
}
