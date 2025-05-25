<?php

namespace App\Filament\Pages;

use App\Models\RecyclingCenter;
use App\Models\RecyclingHistory;
use App\Models\WasteType;
use App\Models\User;
use App\Models\PointsTransaction;
use App\Models\RewardRedemption;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;

class Analytics extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static string $view = 'filament.pages.analytics';
    protected static ?string $navigationLabel = 'Analytics & Insights';
    protected static ?string $title = 'Analytics & Insights';

    // Filter state properties
    public $startDate = null;
    public $endDate = null;
    public $selectedCenter = null;
    public $selectedWasteType = null;

    // Data properties
    public $recyclingStats = [];
    public $userStats = [];
    public $wasteTypeData = [];
    public $pointsData = [];
    public $timeSeriesData = [];
    public $conversionRates = [];

    public function mount()
    {
        $this->startDate = now()->subDays(30)->format('Y-m-d');
        $this->endDate = now()->format('Y-m-d');

        $this->form->fill([
            'startDate' => $this->startDate,
            'endDate' => $this->endDate,
            'selectedCenter' => $this->selectedCenter,
            'selectedWasteType' => $this->selectedWasteType,
        ]);

        $this->loadData();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Filter Analytics')
                    ->description('Customize the data shown in the analytics')
                    ->compact()
                    ->schema([
                        Grid::make()
                            ->columns(4)
                            ->schema([
                                DatePicker::make('startDate')
                                    ->label('Start Date')
                                    ->default(now()->subDays(30))
                                    ->maxDate(now())
                                    ->closeOnDateSelection(),

                                DatePicker::make('endDate')
                                    ->label('End Date')
                                    ->default(now())
                                    ->maxDate(now())
                                    ->minDate(function (callable $get) {
                                        return $get('startDate');
                                    })
                                    ->closeOnDateSelection(),

                                Select::make('selectedCenter')
                                    ->label('Recycling Center')
                                    ->options(RecyclingCenter::where('status', 'approved')
                                        ->where('is_active', true)
                                        ->pluck('name', 'id')
                                        ->toArray())
                                    ->placeholder('All Centers')
                                    ->searchable(),

                                Select::make('selectedWasteType')
                                    ->label('Waste Type')
                                    ->options(WasteType::pluck('name', 'id')->toArray())
                                    ->placeholder('All Waste Types')
                                    ->searchable(),
                            ]),
                    ])
                    ->columnSpan('full'),
            ]);
    }

    public function loadData()
    {
        // Get the form state
        $formState = $this->form->getState();

        // Update properties with form values
        $this->startDate = $formState['startDate'] ?? now()->subDays(30)->format('Y-m-d');
        $this->endDate = $formState['endDate'] ?? now()->format('Y-m-d');
        $this->selectedCenter = $formState['selectedCenter'] ?? null;
        $this->selectedWasteType = $formState['selectedWasteType'] ?? null;

        // 1. Load recycling statistics
        $this->loadRecyclingStats();

        // 2. Load user statistics
        $this->loadUserStats();

        // 3. Load waste type distribution
        $this->loadWasteTypeData();

        // 4. Load points data
        $this->loadPointsData();

        // 5. Load time series data
        $this->loadTimeSeriesData();

        // 6. Load conversion rates and performance metrics
        $this->loadConversionRates();
    }

    private function loadRecyclingStats()
    {
        $query = RecyclingHistory::query()
            ->whereBetween('created_at', [$this->startDate, $this->endDate . ' 23:59:59']);

        if ($this->selectedCenter) {
            $query->where('center_id', $this->selectedCenter);
        }

        if ($this->selectedWasteType) {
            $query->whereHas('wasteItem', function (Builder $q) {
                $q->where('waste_type_id', $this->selectedWasteType);
            });
        }

        $this->recyclingStats = [
            'total_items' => $query->count(),
            'unique_users' => $query->distinct('user_id')->count('user_id'),
            'items_per_user' => $query->count() > 0 && $query->distinct('user_id')->count('user_id') > 0
                ? round($query->count() / $query->distinct('user_id')->count('user_id'), 1)
                : 0,
            'daily_average' => $query->count() > 0
                ? round($query->count() / Carbon::parse($this->startDate)->diffInDays(Carbon::parse($this->endDate)), 1)
                : 0,
        ];
    }

    private function loadUserStats()
    {
        // Base query for all users with activity in the date range
        $query = User::query()
            ->whereHas('recyclingHistories', function (Builder $q) {
                $q->whereBetween('created_at', [$this->startDate, $this->endDate . ' 23:59:59']);

                if ($this->selectedCenter) {
                    $q->where('center_id', $this->selectedCenter);
                }

                if ($this->selectedWasteType) {
                    $q->whereHas('wasteItem', function (Builder $sq) {
                        $sq->where('waste_type_id', $this->selectedWasteType);
                    });
                }
            });

        // Get active users (users who recycled more than once)
        $activeUsers = $query->withCount(['recyclingHistories' => function (Builder $q) {
            $q->whereBetween('created_at', [$this->startDate, $this->endDate . ' 23:59:59']);

            if ($this->selectedCenter) {
                $q->where('center_id', $this->selectedCenter);
            }

            if ($this->selectedWasteType) {
                $q->whereHas('wasteItem', function (Builder $sq) {
                    $sq->where('waste_type_id', $this->selectedWasteType);
                });
            }
        }])
            ->having('recycling_histories_count', '>', 1)
            ->count();

        // Get new users (users who registered in the date range)
        $newUsers = User::whereBetween('created_at', [$this->startDate, $this->endDate . ' 23:59:59'])->count();

        // Get returning users (users who recycled multiple times on different days)
        $returningUsers = DB::table('recycling_histories')
            ->select('user_id', DB::raw('COUNT(DISTINCT DATE(created_at)) as unique_days'))
            ->whereBetween('created_at', [$this->startDate, $this->endDate . ' 23:59:59'])
            ->when($this->selectedCenter, function ($q) {
                return $q->where('center_id', $this->selectedCenter);
            })
            ->when($this->selectedWasteType, function ($q) {
                return $q->whereIn('waste_item_id', function ($sq) {
                    $sq->select('id')
                        ->from('waste_items')
                        ->where('waste_type_id', $this->selectedWasteType);
                });
            })
            ->groupBy('user_id')
            ->having('unique_days', '>', 1)
            ->count();

        $this->userStats = [
            'total_users' => $query->count(),
            'active_users' => $activeUsers,
            'new_users' => $newUsers,
            'returning_users' => $returningUsers,
            'retention_rate' => $query->count() > 0 ? round(($returningUsers / $query->count()) * 100, 1) : 0,
        ];
    }

    private function loadWasteTypeData()
    {
        // Get waste type distribution
        $query = RecyclingHistory::query()
            ->select('waste_items.waste_type_id', 'waste_types.name', 'waste_types.color', DB::raw('COUNT(*) as count'))
            ->join('waste_items', 'recycling_histories.waste_item_id', '=', 'waste_items.id')
            ->join('waste_types', 'waste_items.waste_type_id', '=', 'waste_types.id')
            ->whereBetween('recycling_histories.created_at', [$this->startDate, $this->endDate . ' 23:59:59'])
            ->when($this->selectedCenter, function ($q) {
                return $q->where('recycling_histories.center_id', $this->selectedCenter);
            })
            ->when($this->selectedWasteType, function ($q) {
                return $q->where('waste_items.waste_type_id', $this->selectedWasteType);
            })
            ->groupBy('waste_items.waste_type_id', 'waste_types.name', 'waste_types.color')
            ->orderByDesc('count');

        $this->wasteTypeData = $query->get()->toArray();
    }

    private function loadPointsData()
    {
        // Get points distribution by category
        $query = PointsTransaction::query()
            ->select('category', 'type', DB::raw('SUM(points) as total'))
            ->whereBetween('created_at', [$this->startDate, $this->endDate . ' 23:59:59'])
            ->when($this->selectedCenter, function ($q) {
                return $q->where('center_id', $this->selectedCenter);
            })
            ->groupBy('category', 'type')
            ->orderBy('category', 'asc')
            ->orderBy('type', 'asc');

        $pointsData = $query->get();

        // Format the data for the chart
        $categories = [];
        $earnedPoints = [];
        $spentPoints = [];

        foreach ($pointsData as $data) {
            if (!in_array($data->category, $categories)) {
                $categories[] = $data->category;
            }

            if ($data->type === 'earned') {
                $earnedPoints[$data->category] = $data->total;
            } else {
                $spentPoints[$data->category] = $data->total;
            }
        }

        $this->pointsData = [
            'categories' => $categories,
            'earned' => array_map(function ($category) use ($earnedPoints) {
                return $earnedPoints[$category] ?? 0;
            }, $categories),
            'spent' => array_map(function ($category) use ($spentPoints) {
                return $spentPoints[$category] ?? 0;
            }, $categories),
        ];
    }

    private function loadTimeSeriesData()
    {
        // Calculate the appropriate interval based on date range
        $diff = Carbon::parse($this->startDate)->diffInDays(Carbon::parse($this->endDate));
        $interval = $diff <= 31 ? 'day' : ($diff <= 90 ? 'week' : 'month');

        // Get recycling activity over time
        if ($interval === 'day') {
            $query = RecyclingHistory::query()
                ->select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as count'))
                ->whereBetween('created_at', [$this->startDate, $this->endDate . ' 23:59:59'])
                ->when($this->selectedCenter, function ($q) {
                    return $q->where('center_id', $this->selectedCenter);
                })
                ->when($this->selectedWasteType, function ($q) {
                    return $q->whereHas('wasteItem', function (Builder $sq) {
                        $sq->where('waste_type_id', $this->selectedWasteType);
                    });
                })
                ->groupBy('date')
                ->orderBy('date');

            $activityData = $query->get();

            // Fill in missing dates
            $start = Carbon::parse($this->startDate);
            $end = Carbon::parse($this->endDate);
            $dates = [];
            $counts = [];

            while ($start <= $end) {
                $dateStr = $start->format('Y-m-d');
                $dates[] = $start->format('M d');

                $found = false;
                foreach ($activityData as $data) {
                    if ($data->date === $dateStr) {
                        $counts[] = $data->count;
                        $found = true;
                        break;
                    }
                }

                if (!$found) {
                    $counts[] = 0;
                }

                $start->addDay();
            }

            $this->timeSeriesData = [
                'interval' => $interval,
                'labels' => $dates,
                'counts' => $counts,
            ];
        } else if ($interval === 'week') {
            // Group by week
            $activityData = [];
            $start = Carbon::parse($this->startDate)->startOfWeek();
            $end = Carbon::parse($this->endDate)->endOfWeek();
            $current = $start->copy();

            while ($current <= $end) {
                $weekStart = $current->copy();
                $weekEnd = $current->copy()->endOfWeek();

                $count = RecyclingHistory::query()
                    ->whereBetween('created_at', [$weekStart, $weekEnd])
                    ->when($this->selectedCenter, function ($q) {
                        return $q->where('center_id', $this->selectedCenter);
                    })
                    ->when($this->selectedWasteType, function ($q) {
                        return $q->whereHas('wasteItem', function (Builder $sq) {
                            $sq->where('waste_type_id', $this->selectedWasteType);
                        });
                    })
                    ->count();

                $activityData[] = [
                    'date' => $weekStart->format('M d') . ' - ' . $weekEnd->format('M d'),
                    'count' => $count,
                ];

                $current->addWeek();
            }

            $this->timeSeriesData = [
                'interval' => $interval,
                'labels' => array_column($activityData, 'date'),
                'counts' => array_column($activityData, 'count'),
            ];
        } else {
            // Group by month
            $activityData = [];
            $start = Carbon::parse($this->startDate)->startOfMonth();
            $end = Carbon::parse($this->endDate)->endOfMonth();
            $current = $start->copy();

            while ($current <= $end) {
                $monthStart = $current->copy()->startOfMonth();
                $monthEnd = $current->copy()->endOfMonth();

                $count = RecyclingHistory::query()
                    ->whereBetween('created_at', [$monthStart, $monthEnd])
                    ->when($this->selectedCenter, function ($q) {
                        return $q->where('center_id', $this->selectedCenter);
                    })
                    ->when($this->selectedWasteType, function ($q) {
                        return $q->whereHas('wasteItem', function (Builder $sq) {
                            $sq->where('waste_type_id', $this->selectedWasteType);
                        });
                    })
                    ->count();

                $activityData[] = [
                    'date' => $monthStart->format('M Y'),
                    'count' => $count,
                ];

                $current->addMonth();
            }

            $this->timeSeriesData = [
                'interval' => $interval,
                'labels' => array_column($activityData, 'date'),
                'counts' => array_column($activityData, 'count'),
            ];
        }
    }

    private function loadConversionRates()
    {
        // Calculate conversion rates and performance metrics

        // 1. Points to redemption ratio
        $pointsAwarded = PointsTransaction::query()
            ->where('type', 'earned')
            ->whereBetween('created_at', [$this->startDate, $this->endDate . ' 23:59:59'])
            ->when($this->selectedCenter, function ($q) {
                return $q->where('center_id', $this->selectedCenter);
            })
            ->sum('points');

        $pointsRedeemed = PointsTransaction::query()
            ->where('type', 'spent')
            ->whereBetween('created_at', [$this->startDate, $this->endDate . ' 23:59:59'])
            ->when($this->selectedCenter, function ($q) {
                return $q->where('center_id', $this->selectedCenter);
            })
            ->sum('points');

        // 2. Redemption rate
        $rewardsRedeemed = RewardRedemption::query()
            ->whereBetween('created_at', [$this->startDate, $this->endDate . ' 23:59:59'])
            ->when($this->selectedCenter, function ($q) {
                return $q->whereHas('reward', function (Builder $sq) {
                    $sq->where('center_id', $this->selectedCenter);
                });
            })
            ->count();

        $activeUsers = User::query()
            ->whereHas('recyclingHistories', function (Builder $q) {
                $q->whereBetween('created_at', [$this->startDate, $this->endDate . ' 23:59:59']);

                if ($this->selectedCenter) {
                    $q->where('center_id', $this->selectedCenter);
                }
            })
            ->count();

        // 3. Average time between recycling visits
        $avgTimeBetweenVisits = DB::select("
            SELECT AVG(days_between) as avg_days
            FROM (
                SELECT 
                    user_id,
                    DATEDIFF(
                        LEAD(created_at) OVER (PARTITION BY user_id ORDER BY created_at),
                        created_at
                    ) as days_between
                FROM (
                    SELECT 
                        user_id,
                        DATE(created_at) as created_at
                    FROM recycling_histories
                    WHERE created_at BETWEEN ? AND ?
                    " . ($this->selectedCenter ? "AND center_id = ?" : "") . "
                    GROUP BY user_id, DATE(created_at)
                ) as unique_visits
            ) as visit_gaps
            WHERE days_between IS NOT NULL
        ", array_filter([
            $this->startDate,
            $this->endDate . ' 23:59:59',
            $this->selectedCenter
        ]));

        $this->conversionRates = [
            'points_awarded' => $pointsAwarded,
            'points_redeemed' => $pointsRedeemed,
            'redemption_percentage' => $pointsAwarded > 0 ? round(($pointsRedeemed / $pointsAwarded) * 100, 1) : 0,
            'rewards_redeemed' => $rewardsRedeemed,
            'user_redemption_rate' => $activeUsers > 0 ? round(($rewardsRedeemed / $activeUsers) * 100, 1) : 0,
            'avg_days_between_visits' => isset($avgTimeBetweenVisits[0]->avg_days) ? round($avgTimeBetweenVisits[0]->avg_days, 1) : 0,
        ];
    }

    public function updatedFormData($state)
    {
        $this->loadData();
    }

    public function filter()
    {
        $this->loadData();
    }
}
