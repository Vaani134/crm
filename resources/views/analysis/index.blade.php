@extends('layouts.app')

@section('title', 'Sales Analysis - Inventory & Sales')

@section('content')
<div class="row">
    <div class="col-12">
        <h2><i class="fas fa-chart-line"></i> Sales Analysis</h2>
        <p class="text-muted">Comprehensive sales analytics and performance insights</p>
    </div>
</div>

<!-- Sales Charts -->
<div class="row mb-4">
    <!-- Category-wise Sales Chart -->
    <div class="col-lg-4 col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6><i class="fas fa-chart-pie"></i> Category Sales</h6>
                <select id="categoryYearSelect" class="form-select form-select-sm" style="width: auto;">
                    @foreach($availableYears as $year)
                        <option value="{{ $year }}" {{ $year == $categoryYear ? 'selected' : '' }}>
                            {{ $year }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="card-body" style="height: 350px;">
                @if(count($categorySalesData['labels']) > 0)
                    <canvas id="categorySalesChart" height="300"></canvas>
                @else
                    <div class="text-center py-5" id="categoryNoData">
                        <i class="fas fa-chart-pie fa-3x text-muted mb-3"></i>
                        <h6 class="text-muted">No Category Sales Data</h6>
                        <p class="text-muted small">No sales data available for {{ $categoryYear }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Monthly Sales Chart -->
    <div class="col-lg-4 col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6><i class="fas fa-chart-bar"></i> Monthly Sales</h6>
                <select id="monthlyYearSelect" class="form-select form-select-sm" style="width: auto;">
                    @foreach($availableYears as $year)
                        <option value="{{ $year }}" {{ $year == $monthlyYear ? 'selected' : '' }}>
                            {{ $year }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="card-body" style="height: 350px;">
                <canvas id="monthlySalesChart" height="300"></canvas>
            </div>
        </div>
    </div>

    <!-- Yearly Comparison Chart -->
    <div class="col-lg-4 col-md-12 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h6><i class="fas fa-chart-line"></i> Yearly Comparison (Last 10 Years)</h6>
            </div>
            <div class="card-body" style="height: 350px;">
                <canvas id="yearlySalesChart" height="300"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Analysis Summary Cards -->
<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="card-title">Best Category</h6>
                        <h5 id="bestCategory">
                            @if(count($categorySalesData['labels']) > 0)
                                {{ $categorySalesData['labels'][0] }}
                            @else
                                N/A
                            @endif
                        </h5>
                        <small id="bestCategoryAmount">
                            @if(count($categorySalesData['data']) > 0)
                                ${{ number_format($categorySalesData['data'][0], 2) }}
                            @else
                                $0.00
                            @endif
                        </small>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-trophy fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3 mb-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="card-title">Best Month</h6>
                        <h5 id="bestMonth">
                            @php
                                $maxIndex = array_keys($timeSalesData['monthly']['data'], max($timeSalesData['monthly']['data']))[0] ?? 0;
                                $bestMonth = $timeSalesData['monthly']['labels'][$maxIndex] ?? 'N/A';
                            @endphp
                            {{ $bestMonth }}
                        </h5>
                        <small id="bestMonthAmount">
                            ${{ number_format(max($timeSalesData['monthly']['data']), 2) }}
                        </small>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-calendar-check fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3 mb-3">
        <div class="card bg-info text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="card-title">Growth Trend</h6>
                        <h5 id="growthTrend">
                            @php
                                $currentYearSales = end($yearlySalesData['salesData']);
                                $previousYearSales = count($yearlySalesData['salesData']) > 1 ? $yearlySalesData['salesData'][count($yearlySalesData['salesData']) - 2] : 0;
                                $growth = $previousYearSales > 0 ? (($currentYearSales - $previousYearSales) / $previousYearSales) * 100 : 0;
                            @endphp
                            {{ $growth >= 0 ? '+' : '' }}{{ number_format($growth, 1) }}%
                        </h5>
                        <small>vs last year</small>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-trending-{{ $growth >= 0 ? 'up' : 'down' }} fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3 mb-3">
        <div class="card bg-warning text-dark">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="card-title">Total Years</h6>
                        <h5>{{ count($availableYears) }}</h5>
                        <small>years of data</small>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-database fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Additional Analysis -->
<div class="row">
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h6><i class="fas fa-info-circle"></i> Analysis Insights</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-12 mb-3">
                        <h6 class="text-primary">Category Performance</h6>
                        @if(count($categorySalesData['labels']) > 0)
                            <ul class="list-unstyled">
                                @foreach(array_slice($categorySalesData['labels'], 0, 3) as $index => $category)
                                    <li class="mb-2">
                                        <span class="badge" style="background-color: {{ $categorySalesData['colors'][$index] ?? '#6c757d' }}">
                                            {{ $index + 1 }}
                                        </span>
                                        <strong>{{ $category }}</strong>
                                        <span class="float-end">${{ number_format($categorySalesData['data'][$index], 2) }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <p class="text-muted">No category data available for {{ $categoryYear }}</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h6><i class="fas fa-chart-area"></i> Sales Trends</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-12 mb-3">
                        <h6 class="text-success">Monthly Highlights ({{ $monthlyYear }})</h6>
                        @php
                            $monthlyData = $timeSalesData['monthly']['data'];
                            $monthlyLabels = $timeSalesData['monthly']['labels'];
                            $maxValue = max($monthlyData);
                            $minValue = min(array_filter($monthlyData)); // Exclude zeros
                            $maxIndex = array_search($maxValue, $monthlyData);
                            $minIndex = array_search($minValue, $monthlyData);
                        @endphp
                        <ul class="list-unstyled">
                            <li class="mb-2">
                                <i class="fas fa-arrow-up text-success"></i>
                                <strong>Best:</strong> {{ $monthlyLabels[$maxIndex] }} 
                                <span class="float-end">${{ number_format($maxValue, 2) }}</span>
                            </li>
                            @if($minValue > 0)
                            <li class="mb-2">
                                <i class="fas fa-arrow-down text-warning"></i>
                                <strong>Lowest:</strong> {{ $monthlyLabels[$minIndex] }} 
                                <span class="float-end">${{ number_format($minValue, 2) }}</span>
                            </li>
                            @endif
                            <li class="mb-2">
                                <i class="fas fa-calculator text-info"></i>
                                <strong>Average:</strong> Monthly 
                                <span class="float-end">${{ number_format(array_sum($monthlyData) / 12, 2) }}</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Chart data from server
let categorySalesData = @json($categorySalesData);
let timeSalesData = @json($timeSalesData);
let yearlySalesData = @json($yearlySalesData);
let categoryYear = {{ $categoryYear }};
let monthlyYear = {{ $monthlyYear }};

// Chart instances
let categorySalesChart = null;
let monthlySalesChart = null;
let yearlySalesChart = null;

// Initialize charts
function initializeCharts() {
    // Category Sales Chart (Enhanced Doughnut)
    if (categorySalesData.labels.length > 0) {
        const ctx1 = document.getElementById('categorySalesChart').getContext('2d');
        categorySalesChart = new Chart(ctx1, {
            type: 'doughnut',
            data: {
                labels: categorySalesData.labels,
                datasets: [{
                    data: categorySalesData.data,
                    backgroundColor: categorySalesData.colors.length > 0 ? categorySalesData.colors : [
                        '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40'
                    ],
                    borderWidth: 3,
                    borderColor: '#fff',
                    hoverBorderWidth: 4,
                    hoverBorderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            boxWidth: 15,
                            padding: 10,
                            font: {
                                size: 11
                            }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const value = context.parsed;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = ((value / total) * 100).toFixed(1);
                                return context.label + ': $' + value.toFixed(0) + ' (' + percentage + '%)';
                            }
                        }
                    }
                }
            }
        });
    }

    // Monthly Sales Chart (Enhanced Bar)
    const ctx2 = document.getElementById('monthlySalesChart').getContext('2d');
    monthlySalesChart = new Chart(ctx2, {
        type: 'bar',
        data: {
            labels: timeSalesData.monthly.labels,
            datasets: [{
                data: timeSalesData.monthly.data,
                backgroundColor: '#007bff',
                borderColor: '#0056b3',
                borderWidth: 1,
                borderRadius: 4,
                borderSkipped: false,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return 'Sales: $' + context.parsed.y.toLocaleString();
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        font: {
                            size: 10
                        },
                        callback: function(value) {
                            return '$' + value.toLocaleString();
                        }
                    },
                    grid: {
                        color: 'rgba(0,0,0,0.1)'
                    }
                },
                x: {
                    ticks: {
                        font: {
                            size: 10
                        }
                    },
                    grid: {
                        display: false
                    }
                }
            }
        }
    });

    // Yearly Comparison Chart (Enhanced Line Chart)
    const ctx3 = document.getElementById('yearlySalesChart').getContext('2d');
    yearlySalesChart = new Chart(ctx3, {
        type: 'line',
        data: {
            labels: yearlySalesData.labels,
            datasets: [{
                label: 'Sales',
                data: yearlySalesData.salesData,
                borderColor: '#28a745',
                backgroundColor: 'rgba(40, 167, 69, 0.1)',
                borderWidth: 3,
                fill: true,
                tension: 0.4,
                pointBackgroundColor: '#28a745',
                pointBorderColor: '#fff',
                pointBorderWidth: 3,
                pointRadius: 5,
                pointHoverRadius: 7
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const salesValue = context.parsed.y;
                            const year = context.label;
                            const transactionIndex = context.dataIndex;
                            const transactions = yearlySalesData.transactionData[transactionIndex];
                            return [
                                `Year: ${year}`,
                                `Sales: $${salesValue.toLocaleString()}`,
                                `Transactions: ${transactions.toLocaleString()}`
                            ];
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        font: {
                            size: 10
                        },
                        callback: function(value) {
                            return '$' + value.toLocaleString();
                        }
                    },
                    grid: {
                        color: 'rgba(0,0,0,0.1)'
                    }
                },
                x: {
                    ticks: {
                        font: {
                            size: 10
                        }
                    },
                    grid: {
                        display: false
                    }
                }
            }
        }
    });
}

// Function to update category chart with new year data
function updateCategoryChart(year) {
    // Show loading state
    document.getElementById('categorySalesChart').style.opacity = '0.5';
    
    // Fetch new data via AJAX
    fetch(`/analysis?category_year=${year}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        // Update category chart
        if (categorySalesChart && data.categorySalesData.labels.length > 0) {
            categorySalesChart.data.labels = data.categorySalesData.labels;
            categorySalesChart.data.datasets[0].data = data.categorySalesData.data;
            categorySalesChart.data.datasets[0].backgroundColor = data.categorySalesData.colors.length > 0 
                ? data.categorySalesData.colors 
                : ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40'];
            categorySalesChart.update();
            
            // Update best category card
            document.getElementById('bestCategory').textContent = data.categorySalesData.labels[0] || 'N/A';
            document.getElementById('bestCategoryAmount').textContent = data.categorySalesData.data[0] 
                ? '$' + data.categorySalesData.data[0].toLocaleString() 
                : '$0.00';
            
            // Hide no data message if it exists
            const noDataDiv = document.getElementById('categoryNoData');
            if (noDataDiv) {
                noDataDiv.style.display = 'none';
            }
            document.getElementById('categorySalesChart').style.display = 'block';
        } else {
            // No data for this year - show no data message
            if (categorySalesChart) {
                categorySalesChart.data.labels = [];
                categorySalesChart.data.datasets[0].data = [];
                categorySalesChart.update();
            }
            
            // Update best category card
            document.getElementById('bestCategory').textContent = 'N/A';
            document.getElementById('bestCategoryAmount').textContent = '$0.00';
            
            // Show no data message
            let noDataDiv = document.getElementById('categoryNoData');
            if (!noDataDiv) {
                noDataDiv = document.createElement('div');
                noDataDiv.id = 'categoryNoData';
                noDataDiv.className = 'text-center py-5';
                noDataDiv.innerHTML = `
                    <i class="fas fa-chart-pie fa-3x text-muted mb-3"></i>
                    <h6 class="text-muted">No Category Sales Data</h6>
                    <p class="text-muted small">No sales data available for ${year}</p>
                `;
                document.getElementById('categorySalesChart').parentNode.appendChild(noDataDiv);
            } else {
                noDataDiv.innerHTML = `
                    <i class="fas fa-chart-pie fa-3x text-muted mb-3"></i>
                    <h6 class="text-muted">No Category Sales Data</h6>
                    <p class="text-muted small">No sales data available for ${year}</p>
                `;
                noDataDiv.style.display = 'block';
            }
            document.getElementById('categorySalesChart').style.display = 'none';
        }
        
        // Remove loading state
        document.getElementById('categorySalesChart').style.opacity = '1';
    })
    .catch(error => {
        console.error('Error updating category chart:', error);
        document.getElementById('categorySalesChart').style.opacity = '1';
    });
}

// Function to update monthly chart with new year data
function updateMonthlyChart(year) {
    // Show loading state
    document.getElementById('monthlySalesChart').style.opacity = '0.5';
    
    // Fetch new data via AJAX
    fetch(`/analysis?monthly_year=${year}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        // Update monthly chart
        if (monthlySalesChart) {
            monthlySalesChart.data.datasets[0].data = data.timeSalesData.monthly.data;
            monthlySalesChart.update();
            
            // Update best month card
            const monthlyData = data.timeSalesData.monthly.data;
            const monthlyLabels = data.timeSalesData.monthly.labels;
            const maxValue = Math.max(...monthlyData);
            const maxIndex = monthlyData.indexOf(maxValue);
            
            document.getElementById('bestMonth').textContent = monthlyLabels[maxIndex] || 'N/A';
            document.getElementById('bestMonthAmount').textContent = '$' + maxValue.toLocaleString();
        }
        
        // Remove loading state
        document.getElementById('monthlySalesChart').style.opacity = '1';
    })
    .catch(error => {
        console.error('Error updating monthly chart:', error);
        document.getElementById('monthlySalesChart').style.opacity = '1';
    });
}

// Event listeners for independent year selection
document.getElementById('categoryYearSelect').addEventListener('change', function() {
    const year = this.value;
    categoryYear = year;
    updateCategoryChart(year);
});

document.getElementById('monthlyYearSelect').addEventListener('change', function() {
    const year = this.value;
    monthlyYear = year;
    updateMonthlyChart(year);
});

// Initialize charts when page loads
document.addEventListener('DOMContentLoaded', function() {
    initializeCharts();
});
</script>
@endsection