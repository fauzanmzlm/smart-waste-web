<x-filament-panels::page>
    {{-- <x-filament::section>
        {{ $this->form }}
    </x-filament::section>

    <div class="grid grid-cols-1 gap-y-8 mt-8">
        <!-- User Analytics -->
        <x-filament::section>
            <x-slot name="heading">User Analytics</x-slot>
            
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <x-filament::card>
                    <div class="text-sm font-medium text-gray-500">Total Users</div>
                    <div class="text-3xl font-bold">{{ $this->getUserStats()['totalUsers'] }}</div>
                </x-filament::card>
                
                <x-filament::card>
                    <div class="text-sm font-medium text-gray-500">New Users (This Period)</div>
                    <div class="text-3xl font-bold">{{ $this->getUserStats()['newUsers'] }}</div>
                </x-filament::card>
                
                <x-filament::card>
                    <div class="text-sm font-medium text-gray-500">Active Users (Last 30 Days)</div>
                    <div class="text-3xl font-bold">{{ $this->getUserStats()['activeUsers'] }}</div>
                </x-filament::card>
                
                <x-filament::card>
                    <div class="text-sm font-medium text-gray-500">Center Owners</div>
                    <div class="text-3xl font-bold">{{ $this->getUserStats()['centerOwners'] }}</div>
                </x-filament::card>
            </div>
            
            <div id="userGrowthChart" class="mt-6 h-80"></div>
        </x-filament::section>
        
        <!-- Recycling Analytics -->
        <x-filament::section>
            <x-slot name="heading">Recycling Analytics</x-slot>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <x-filament::card>
                    <div class="text-sm font-medium text-gray-500">Total Recycling Activities</div>
                    <div class="text-3xl font-bold">{{ $this->getRecyclingStats()['totalActivities'] }}</div>
                </x-filament::card>
                
                <x-filament::card>
                    <div class="text-sm font-medium text-gray-500">Activities (This Period)</div>
                    <div class="text-3xl font-bold">{{ $this->getRecyclingStats()['activitiesInPeriod'] }}</div>
                </x-filament::card>
                
                <x-filament::card>
                    <div class="text-sm font-medium text-gray-500">Active Recyclers (This Period)</div>
                    <div class="text-3xl font-bold">{{ $this->getRecyclingStats()['activeRecyclers'] }}</div>
                </x-filament::card>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-6">
                <div>
                    <h3 class="text-base font-medium mb-2">Recycling by Waste Type</h3>
                    <div id="recyclingByTypeChart" class="h-80"></div>
                </div>
                <div>
                    <h3 class="text-base font-medium mb-2">Recycling Trend</h3>
                    <div id="recyclingTrendChart" class="h-80"></div>
                </div>
            </div>
        </x-filament::section>
        
        <!-- Recycling Center Analytics -->
        <x-filament::section>
            <x-slot name="heading">Recycling Center Analytics</x-slot>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <x-filament::card>
                    <div class="text-sm font-medium text-gray-500">Total Centers</div>
                    <div class="text-3xl font-bold">{{ $this->getCenterStats()['totalCenters'] }}</div>
                </x-filament::card>
                
                <x-filament::card>
                    <div class="text-sm font-medium text-gray-500">Active Centers</div>
                    <div class="text-3xl font-bold">{{ $this->getCenterStats()['activeCenters'] }}</div>
                </x-filament::card>
                
                <x-filament::card>
                    <div class="text-sm font-medium text-gray-500">Pending Centers</div>
                    <div class="text-3xl font-bold">{{ $this->getCenterStats()['pendingCenters'] }}</div>
                </x-filament::card>
            </div>
            
            <div class="mt-6">
                <h3 class="text-base font-medium mb-2">Centers with Most Activity</h3>
                <div id="centersActivityChart" class="h-80"></div>
            </div>
        </x-filament::section>
        
        <!-- Points & Rewards Analytics -->
        <x-filament::section>
            <x-slot name="heading">Points & Rewards Analytics</x-slot>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <h3 class="text-base font-medium mb-2">Points Overview</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <x-filament::card>
                            <div class="text-sm font-medium text-gray-500">Total Points Earned</div>
                            <div class="text-3xl font-bold">{{ number_format($this->getPointsStats()['totalPointsEarned']) }}</div>
                        </x-filament::card>
                        
                        <x-filament::card>
                            <div class="text-sm font-medium text-gray-500">Total Points Spent</div>
                            <div class="text-3xl font-bold">{{ number_format($this->getPointsStats()['totalPointsSpent']) }}</div>
                        </x-filament::card>
                    </div>
                    
                    <div id="pointsTrendChart" class="h-80"></div>
                </div>
                
                <div>
                    <h3 class="text-base font-medium mb-2">Rewards Overview</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <x-filament::card>
                            <div class="text-sm font-medium text-gray-500">Total Redemptions</div>
                            <div class="text-3xl font-bold">{{ $this->getRewardsStats()['totalRedemptions'] }}</div>
                        </x-filament::card>
                        
                        <x-filament::card>
                            <div class="text-sm font-medium text-gray-500">Redemptions (This Period)</div>
                            <div class="text-3xl font-bold">{{ $this->getRewardsStats()['redemptionsInPeriod'] }}</div>
                        </x-filament::card>
                    </div>
                    
                    <div class="grid grid-cols-1 gap-4">
                        <div>
                            <h3 class="text-base font-medium mb-2">Redemptions by Status</h3>
                            <div id="redemptionsStatusChart" class="h-64"></div>
                        </div>
                        
                        <div>
                            <h3 class="text-base font-medium mb-2">Top Redeemed Rewards</h3>
                            <div id="topRewardsChart" class="h-64"></div>
                        </div>
                    </div>
                </div>
            </div>
        </x-filament::section>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
        // User Growth Chart
        document.addEventListener('DOMContentLoaded', function() {
            const userGrowthData = @json(array_values($this->getUserStats()['userGrowth']));
            const userGrowthLabels = @json(array_keys($this->getUserStats()['userGrowth']));
            
            const userGrowthOptions = {
                series: [{
                    name: 'New Users',
                    data: userGrowthData
                }],
                chart: {
                    height: 300,
                    type: 'area',
                    toolbar: {
                        show: false
                    }
                },
                dataLabels: {
                    enabled: false
                },
                stroke: {
                    curve: 'smooth'
                },
                xaxis: {
                    type: 'datetime',
                    categories: userGrowthLabels
                },
                tooltip: {
                    x: {
                        format: 'dd MMM yyyy'
                    }
                },
                colors: ['#10b981']
            };
            
            const userGrowthChart = new ApexCharts(document.querySelector("#userGrowthChart"), userGrowthOptions);
            userGrowthChart.render();
            
            // Recycling By Type Chart
            const recyclingByTypeData = @json(array_values($this->getRecyclingStats()['recyclingByType']));
            const recyclingByTypeLabels = @json(array_keys($this->getRecyclingStats()['recyclingByType']));
            
            const recyclingByTypeOptions = {
                series: recyclingByTypeData,
                chart: {
                    type: 'pie',
                    height: 300
                },
                labels: recyclingByTypeLabels,
                legend: {
                    position: 'bottom'
                },
                responsive: [{
                    breakpoint: 480,
                    options: {
                        chart: {
                            width: 300
                        },
                        legend: {
                            position: 'bottom'
                        }
                    }
                }]
            };
            
            const recyclingByTypeChart = new ApexCharts(document.querySelector("#recyclingByTypeChart"), recyclingByTypeOptions);
            recyclingByTypeChart.render();
            
            // Recycling Trend Chart
            const recyclingTrendData = @json(array_values($this->getRecyclingStats()['recyclingTrend']));
            const recyclingTrendLabels = @json(array_keys($this->getRecyclingStats()['recyclingTrend']));
            
            const recyclingTrendOptions = {
                series: [{
                    name: 'Recycling Activities',
                    data: recyclingTrendData
                }],
                chart: {
                    height: 300,
                    type: 'bar',
                    toolbar: {
                        show: false
                    }
                },
                plotOptions: {
                    bar: {
                        columnWidth: '70%',
                    }
                },
                dataLabels: {
                    enabled: false
                },
                xaxis: {
                    type: 'datetime',
                    categories: recyclingTrendLabels
                },
                colors: ['#6366f1']
            };
            
            const recyclingTrendChart = new ApexCharts(document.querySelector("#recyclingTrendChart"), recyclingTrendOptions);
            recyclingTrendChart.render();
            
            // Centers Activity Chart
            const centersActivityData = @json(array_values($this->getCenterStats()['centersWithMostActivity']));
            const centersActivityLabels = @json(array_keys($this->getCenterStats()['centersWithMostActivity']));
            
            const centersActivityOptions = {
                series: [{
                    name: 'Activities',
                    data: centersActivityData
                }],
                chart: {
                    height: 300,
                    type: 'bar',
                    toolbar: {
                        show: false
                    }
                },
                plotOptions: {
                    bar: {
                        borderRadius: 4,
                        horizontal: true,
                    }
                },
                dataLabels: {
                    enabled: false
                },
                xaxis: {
                    categories: centersActivityLabels,
                },
                colors: ['#f59e0b']
            };
            
            const centersActivityChart = new ApexCharts(document.querySelector("#centersActivityChart"), centersActivityOptions);
            centersActivityChart.render();
            
            // Points Trend Chart
            const pointsTrendLabels = @json(array_keys($this->getPointsStats()['pointsTrend']));
            const pointsEarnedData = [];
            const pointsSpentData = [];
            const pointsNetData = [];
            
            @foreach($this->getPointsStats()['pointsTrend'] as $date => $data)
                pointsEarnedData.push({{ $data['earned'] }});
                pointsSpentData.push({{ $data['spent'] }});
                pointsNetData.push({{ $data['net'] }});
            @endforeach
            
            const pointsTrendOptions = {
                series: [
                    {
                        name: 'Earned',
                        data: pointsEarnedData
                    },
                    {
                        name: 'Spent',
                        data: pointsSpentData
                    },
                    {
                        name: 'Net',
                        data: pointsNetData
                    }
                ],
                chart: {
                    height: 300,
                    type: 'line',
                    toolbar: {
                        show: false
                    }
                },
                stroke: {
                    width: [3, 3, 5],
                    curve: 'smooth',
                    dashArray: [0, 0, 0]
                },
                xaxis: {
                    type: 'datetime',
                    categories: pointsTrendLabels
                },
                yaxis: {
                    title: {
                        text: 'Points'
                    },
                },
                tooltip: {
                    y: {
                        formatter: function(val) {
                            return val.toFixed(0)
                        }
                    }
                },
                legend: {
                    position: 'top'
                },
                colors: ['#10b981', '#ef4444', '#6366f1']
            };
            
            const pointsTrendChart = new ApexCharts(document.querySelector("#pointsTrendChart"), pointsTrendOptions);
            pointsTrendChart.render();
            
            // Redemptions Status Chart
            const redemptionsStatusData = [
                @isset($this->getRewardsStats()['redemptionsByStatus']['pending'])
                    {{ $this->getRewardsStats()['redemptionsByStatus']['pending'] }},
                @else
                    0,
                @endisset
                @isset($this->getRewardsStats()['redemptionsByStatus']['approved'])
                    {{ $this->getRewardsStats()['redemptionsByStatus']['approved'] }},
                @else
                    0,
                @endisset
                @isset($this->getRewardsStats()['redemptionsByStatus']['rejected'])
                    {{ $this->getRewardsStats()['redemptionsByStatus']['rejected'] }}
                @else
                    0
                @endisset
            ];
            
            const redemptionsStatusOptions = {
                series: redemptionsStatusData,
                chart: {
                    height: 250,
                    type: 'donut',
                },
                labels: ['Pending', 'Approved', 'Rejected'],
                colors: ['#f59e0b', '#10b981', '#ef4444'],
                legend: {
                    position: 'bottom'
                },
                responsive: [{
                    breakpoint: 480,
                    options: {
                        chart: {
                            width: 200
                        },
                        legend: {
                            position: 'bottom'
                        }
                    }
                }]
            };
            
            const redemptionsStatusChart = new ApexCharts(document.querySelector("#redemptionsStatusChart"), redemptionsStatusOptions);
            redemptionsStatusChart.render();
            
            // Top Rewards Chart
            const topRewardsData = @json(array_values($this->getRewardsStats()['topRewards']));
            const topRewardsLabels = @json(array_keys($this->getRewardsStats()['topRewards']));
            
            const topRewardsOptions = {
                series: [{
                    name: 'Redemptions',
                    data: topRewardsData
                }],
                chart: {
                    height: 250,
                    type: 'bar',
                    toolbar: {
                        show: false
                    }
                },
                plotOptions: {
                    bar: {
                        borderRadius: 4,
                        horizontal: true,
                    }
                },
                dataLabels: {
                    enabled: false
                },
                xaxis: {
                    categories: topRewardsLabels,
                },
                colors: ['#6366f1']
            };
            
            const topRewardsChart = new ApexCharts(document.querySelector("#topRewardsChart"), topRewardsOptions);
            topRewardsChart.render();
        });
    </script> --}}
</x-filament-panels::page>