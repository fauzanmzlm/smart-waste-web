<x-filament::page>
    <div class="space-y-6">
        {{ $this->form }}

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Recycling Stats -->
            <x-filament::card>
                <div class="flex items-center justify-between">
                    <h2 class="text-xl font-bold tracking-tight">Recycling</h2>
                    <x-heroicon-s-arrow-path class="w-6 h-6 text-primary-500" />
                </div>
                <dl class="mt-4 space-y-2">
                    <div class="flex justify-between">
                        <dt class="text-sm font-medium text-gray-500">Total Items</dt>
                        <dd class="text-sm font-semibold">{{ number_format($recyclingStats['total_items']) }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm font-medium text-gray-500">Unique Users</dt>
                        <dd class="text-sm font-semibold">{{ number_format($recyclingStats['unique_users']) }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm font-medium text-gray-500">Items per User</dt>
                        <dd class="text-sm font-semibold">{{ $recyclingStats['items_per_user'] }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm font-medium text-gray-500">Daily Average</dt>
                        <dd class="text-sm font-semibold">{{ $recyclingStats['daily_average'] }}</dd>
                    </div>
                </dl>
            </x-filament::card>

            <!-- User Stats -->
            <x-filament::card>
                <div class="flex items-center justify-between">
                    <h2 class="text-xl font-bold tracking-tight">Users</h2>
                    <x-heroicon-s-user-group class="w-6 h-6 text-primary-500" />
                </div>
                <dl class="mt-4 space-y-2">
                    <div class="flex justify-between">
                        <dt class="text-sm font-medium text-gray-500">Total Users</dt>
                        <dd class="text-sm font-semibold">{{ number_format($userStats['total_users']) }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm font-medium text-gray-500">Active Users</dt>
                        <dd class="text-sm font-semibold">{{ number_format($userStats['active_users']) }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm font-medium text-gray-500">New Users</dt>
                        <dd class="text-sm font-semibold">{{ number_format($userStats['new_users']) }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm font-medium text-gray-500">Returning Users</dt>
                        <dd class="text-sm font-semibold">{{ number_format($userStats['returning_users']) }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm font-medium text-gray-500">Retention Rate</dt>
                        <dd class="text-sm font-semibold">{{ $userStats['retention_rate'] }}%</dd>
                    </div>
                </dl>
            </x-filament::card>

            <!-- Points Stats -->
            <x-filament::card>
                <div class="flex items-center justify-between">
                    <h2 class="text-xl font-bold tracking-tight">Points & Rewards</h2>
                    <x-heroicon-s-gift class="w-6 h-6 text-primary-500" />
                </div>
                <dl class="mt-4 space-y-2">
                    <div class="flex justify-between">
                        <dt class="text-sm font-medium text-gray-500">Points Awarded</dt>
                        <dd class="text-sm font-semibold">{{ number_format($conversionRates['points_awarded']) }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm font-medium text-gray-500">Points Redeemed</dt>
                        <dd class="text-sm font-semibold">{{ number_format($conversionRates['points_redeemed']) }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm font-medium text-gray-500">Redemption %</dt>
                        <dd class="text-sm font-semibold">{{ $conversionRates['redemption_percentage'] }}%</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm font-medium text-gray-500">Rewards Redeemed</dt>
                        <dd class="text-sm font-semibold">{{ number_format($conversionRates['rewards_redeemed']) }}
                        </dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm font-medium text-gray-500">User Redemption Rate</dt>
                        <dd class="text-sm font-semibold">{{ $conversionRates['user_redemption_rate'] }}%</dd>
                    </div>
                </dl>
            </x-filament::card>

            <!-- Engagement Stats -->
            <x-filament::card>
                <div class="flex items-center justify-between">
                    <h2 class="text-xl font-bold tracking-tight">Engagement</h2>
                    <x-heroicon-s-chart-bar class="w-6 h-6 text-primary-500" />
                </div>
                <dl class="mt-4 space-y-2">
                    <div class="flex justify-between">
                        <dt class="text-sm font-medium text-gray-500">Avg Days Between Visits</dt>
                        <dd class="text-sm font-semibold">{{ $conversionRates['avg_days_between_visits'] }} days</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm font-medium text-gray-500">Active/Total Users</dt>
                        <dd class="text-sm font-semibold">
                            {{ $userStats['total_users'] > 0 ? round(($userStats['active_users'] / $userStats['total_users']) * 100, 1) : 0 }}%
                        </dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm font-medium text-gray-500">Points Per User</dt>
                        <dd class="text-sm font-semibold">
                            {{ $userStats['total_users'] > 0 ? round($conversionRates['points_awarded'] / $userStats['total_users'], 0) : 0 }}
                        </dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm font-medium text-gray-500">Points Per Item</dt>
                        <dd class="text-sm font-semibold">
                            {{ $recyclingStats['total_items'] > 0 ? round($conversionRates['points_awarded'] / $recyclingStats['total_items'], 1) : 0 }}
                        </dd>
                    </div>
                </dl>
            </x-filament::card>
        </div>

        <!-- Charts Row 1 -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Recycling Activity Time Series -->
            <x-filament::card>
                <h2 class="text-xl font-bold tracking-tight">Recycling Activity Over Time</h2>
                <div class="mt-4" style="height: 300px;" x-data="{
                    init() {
                        const ctx = this.$refs.canvas.getContext('2d');
                        new Chart(ctx, {
                            type: 'line',
                            data: {
                                labels: {{ json_encode($timeSeriesData['labels']) }},
                                datasets: [{
                                    label: 'Items Recycled',
                                    data: {{ json_encode($timeSeriesData['counts']) }},
                                    backgroundColor: 'rgba(16, 185, 129, 0.2)',
                                    borderColor: '#10b981',
                                    borderWidth: 2,
                                    tension: 0.3,
                                    fill: true,
                                    pointRadius: 2,
                                    pointHoverRadius: 5,
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                scales: {
                                    y: {
                                        beginAtZero: true,
                                        ticks: {
                                            precision: 0
                                        }
                                    }
                                }
                            }
                        });
                    }
                }">
                    <canvas x-ref="canvas"></canvas>
                </div>
                <div class="mt-2 text-sm text-gray-500 text-center">
                    Showing data grouped by {{ $timeSeriesData['interval'] }}
                </div>
            </x-filament::card>

            <!-- Waste Type Distribution -->
            <x-filament::card>
                <h2 class="text-xl font-bold tracking-tight">Waste Type Distribution</h2>
                <div class="mt-4" style="height: 300px;" x-data="{
                    init() {
                        const ctx = this.$refs.canvas.getContext('2d');
                        new Chart(ctx, {
                            type: 'pie',
                            data: {
                                labels: {{ json_encode(array_column($wasteTypeData, 'name')) }},
                                datasets: [{
                                    data: {{ json_encode(array_column($wasteTypeData, 'count')) }},
                                    backgroundColor: {{ json_encode(array_column($wasteTypeData, 'color')) }},
                                    borderWidth: 1
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: {
                                        position: 'right'
                                    }
                                }
                            }
                        });
                    }
                }">
                    <canvas x-ref="canvas"></canvas>
                </div>
                <div class="mt-2 text-sm text-gray-500 text-center">
                    Distribution of recycled items by waste type
                </div>
            </x-filament::card>
        </div>

        <!-- Charts Row 2 -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Points by Category -->
            <x-filament::card>
                <h2 class="text-xl font-bold tracking-tight">Points by Category</h2>
                <div class="mt-4" style="height: 300px;" x-data="{
                    init() {
                        const ctx = this.$refs.canvas.getContext('2d');
                        new Chart(ctx, {
                            type: 'bar',
                            data: {
                                labels: {{ json_encode($pointsData['categories']) }},
                                datasets: [{
                                        label: 'Earned',
                                        data: {{ json_encode($pointsData['earned']) }},
                                        backgroundColor: '#10b981',
                                        borderColor: '#10b981',
                                        borderWidth: 1
                                    },
                                    {
                                        label: 'Spent',
                                        data: {{ json_encode($pointsData['spent']) }},
                                        backgroundColor: '#ef4444',
                                        borderColor: '#ef4444',
                                        borderWidth: 1
                                    }
                                ]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                scales: {
                                    y: {
                                        beginAtZero: true,
                                        ticks: {
                                            precision: 0
                                        }
                                    }
                                }
                            }
                        });
                    }
                }">
                    <canvas x-ref="canvas"></canvas>
                </div>
                <div class="mt-2 text-sm text-gray-500 text-center">
                    Points earned and spent by category
                </div>
            </x-filament::card>

            <!-- User Engagement -->
            <x-filament::card>
                <h2 class="text-xl font-bold tracking-tight">User Engagement Metrics</h2>
                <div class="mt-4" style="height: 300px;" x-data="{
                    init() {
                        const ctx = this.$refs.canvas.getContext('2d');
                        new Chart(ctx, {
                            type: 'radar',
                            data: {
                                labels: ['Active Users', 'Retention Rate', 'Redemption Rate', 'Multiple Visits', 'Daily Recycling'],
                                datasets: [{
                                    label: 'Current Period',
                                    data: [
                                        {{ $userStats['total_users'] > 0 ? round(($userStats['active_users'] / $userStats['total_users']) * 100, 1) : 0 }},
                                        {{ $userStats['retention_rate'] }},
                                        {{ $conversionRates['user_redemption_rate'] }},
                                        {{ $userStats['total_users'] > 0 ? round(($userStats['returning_users'] / $userStats['total_users']) * 100, 1) : 0 }},
                                        {{ $recyclingStats['daily_average'] > 0 ? min(100, round(($recyclingStats['daily_average'] / max(1, $userStats['total_users'])) * 100, 1)) : 0 }}
                                    ],
                                    fill: true,
                                    backgroundColor: 'rgba(16, 185, 129, 0.2)',
                                    borderColor: '#10b981',
                                    pointBackgroundColor: '#10b981',
                                    pointBorderColor: '#fff',
                                    pointHoverBackgroundColor: '#fff',
                                    pointHoverBorderColor: '#10b981'
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                scales: {
                                    r: {
                                        angleLines: {
                                            display: true
                                        },
                                        suggestedMin: 0,
                                        suggestedMax: 100
                                    }
                                }
                            }
                        });
                    }
                }">
                    <canvas x-ref="canvas"></canvas>
                </div>
                <div class="mt-2 text-sm text-gray-500 text-center">
                    Key engagement metrics on a 0-100% scale
                </div>
            </x-filament::card>
        </div>

        <!-- Data Table -->
        <x-filament::card>
            <h2 class="text-xl font-bold tracking-tight">Top Users</h2>
            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead>
                        <tr>
                            <th
                                class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                User</th>
                            <th
                                class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Items Recycled</th>
                            <th
                                class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Points Earned</th>
                            <th
                                class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Last Activity</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @php
                            // Get top users by recycling activity
                            $topUsers = \App\Models\User::query()
                                ->select('users.*')
                                ->selectRaw('COUNT(recycling_histories.id) as items_count')
                                ->selectRaw('MAX(recycling_histories.created_at) as last_activity')
                                ->selectRaw(
                                    'SUM(CASE WHEN points_transactions.type = "earned" THEN points_transactions.points ELSE 0 END) as points_earned',
                                )
                                ->join('recycling_histories', 'users.id', '=', 'recycling_histories.user_id')
                                ->leftJoin('points_transactions', 'users.id', '=', 'points_transactions.user_id')
                                ->whereBetween('recycling_histories.created_at', [$startDate, $endDate . ' 23:59:59'])
                                ->when($selectedCenter, function ($q) use ($selectedCenter) {
                                    return $q->where('recycling_histories.center_id', $selectedCenter);
                                })
                                ->when($selectedWasteType, function ($q) use ($selectedWasteType) {
                                    return $q->whereHas('recyclingHistories.wasteItem', function ($sq) use (
                                        $selectedWasteType,
                                    ) {
                                        $sq->where('waste_type_id', $selectedWasteType);
                                    });
                                })
                                ->groupBy('users.id')
                                ->orderByDesc('items_count')
                                ->limit(10)
                                ->get();
                        @endphp

                        @foreach ($topUsers as $user)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        @if ($user->photoURL)
                                            <img class="h-8 w-8 rounded-full mr-3" src="{{ $user->photoURL }}"
                                                alt="">
                                        @else
                                            <div
                                                class="h-8 w-8 rounded-full bg-primary-100 text-primary-700 flex items-center justify-center mr-3">
                                                <span
                                                    class="text-sm font-medium">{{ substr($user->name, 0, 1) }}</span>
                                            </div>
                                        @endif
                                        <div>
                                            <div class="text-sm font-medium text-gray-900">{{ $user->name }}</div>
                                            <div class="text-sm text-gray-500">{{ $user->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <span
                                        class="px-2 py-1 text-xs font-medium rounded-full bg-primary-100 text-primary-800">
                                        {{ number_format($user->items_count) }} items
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <span
                                        class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">
                                        {{ number_format($user->points_earned ?? 0) }} points
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ \Carbon\Carbon::parse($user->last_activity)->diffForHumans() }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-filament::card>
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.js"></script>
    @endpush
</x-filament::page>
