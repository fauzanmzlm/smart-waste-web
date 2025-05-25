<?php

namespace App\Filament\Widgets;

use App\Models\User;
use App\Models\PointsTransaction;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PointsLeaderboardWidget extends BaseWidget
{
    protected static ?string $heading = 'Points Leaderboard';
    protected int|string|array $columnSpan = 'full';

    // Add filter options
    protected function getTableFilters(): array
    {
        return [
            Tables\Filters\SelectFilter::make('timeframe')
                ->label('Time Period')
                ->options([
                    'all' => 'All Time',
                    'week' => 'This Week',
                    'month' => 'This Month',
                    'year' => 'This Year',
                ])
                ->default('month'),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(function (Builder $query) {
                // Get filter value from the form
                $filter = $this->tableFilters['timeframe'] ?? 'month';
                
                // Start with all users that have points
                $query = User::query()
                    ->whereHas('pointsTransactions', function ($query) use ($filter) {
                        $query->where('type', 'earned');
                        
                        // Apply date filter if not "all"
                        if ($filter !== 'all') {
                            $startDate = now();
                            
                            switch ($filter) {
                                case 'week':
                                    $startDate = $startDate->subWeek();
                                    break;
                                case 'month':
                                    $startDate = $startDate->subMonth();
                                    break;
                                case 'year':
                                    $startDate = $startDate->subYear();
                                    break;
                            }
                            
                            $query->where('created_at', '>=', $startDate);
                        }
                    })
                    ->withSum(['pointsTransactions as earned_points' => function ($query) use ($filter) {
                        $query->where('type', 'earned');
                        
                        // Apply date filter if not "all"
                        if ($filter !== 'all') {
                            $startDate = now();
                            
                            switch ($filter) {
                                case 'week':
                                    $startDate = $startDate->subWeek();
                                    break;
                                case 'month':
                                    $startDate = $startDate->subMonth();
                                    break;
                                case 'year':
                                    $startDate = $startDate->subYear();
                                    break;
                            }
                            
                            $query->where('created_at', '>=', $startDate);
                        }
                    }], 'points')
                    ->orderByDesc('earned_points')
                    ->limit(10);
                
                return $query;
            })
            ->columns([
                Tables\Columns\TextColumn::make('rank')
                    ->rowIndex()
                    ->badge()
                    ->color('warning'),
                Tables\Columns\ImageColumn::make('photoURL')
                    ->label('Avatar')
                    ->circular(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('earned_points')
                    ->label('Points Earned')
                    ->formatStateUsing(fn ($state) => number_format($state ?? 0))
                    ->sortable()
                    ->badge()
                    ->color('success'),
                Tables\Columns\TextColumn::make('items_recycled')
                    ->label('Items Recycled')
                    ->getStateUsing(function (User $record) {
                        $filter = $this->tableFilters['timeframe'] ?? 'month';
                        
                        $query = $record->recyclingHistories();
                        
                        if ($filter !== 'all') {
                            $startDate = now();
                            
                            switch ($filter) {
                                case 'week':
                                    $startDate = $startDate->subWeek();
                                    break;
                                case 'month':
                                    $startDate = $startDate->subMonth();
                                    break;
                                case 'year':
                                    $startDate = $startDate->subYear();
                                    break;
                            }
                            
                            $query->where('created_at', '>=', $startDate);
                        }
                        
                        return $query->count();
                    })
                    ->badge(),
                Tables\Columns\TextColumn::make('badges_count')
                    ->label('Badges')
                    ->getStateUsing(fn (User $record): int => $record->badges()->count())
                    ->badge()
                    ->color('info'),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->url(fn (User $record): string => url('/admin/resources/users/' . $record->id . '/edit'))
                    ->icon('heroicon-m-eye'),
            ]);
    }
}