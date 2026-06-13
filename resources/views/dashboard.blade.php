@extends('layouts.app')

@section('title', 'Dashboard')
@section('page_title', 'Dashboard Rental')

@section('content')

<!-- Metrics Summary -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    <!-- Status Unit Card -->
    <div class="bg-white border border-slate-100 rounded-2xl p-5 flex items-center justify-between transition-all duration-200 hover:border-slate-200 shadow-sm">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-slate-50 flex items-center justify-center text-slate-700">
                <i class="fa-solid fa-gamepad text-xl"></i>
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Total Unit</p>
                <h3 class="text-2xl font-black text-slate-900 leading-tight" id="metric-total">{{ $totalUnits }}</h3>
            </div>
        </div>
        <!-- Sub-status badges vertical stack -->
        <div class="flex flex-col gap-1 border-l border-slate-100 pl-5 py-0.5">
            <div class="flex items-center justify-between gap-4 text-[10px] font-bold">
                <span class="text-slate-400 flex items-center gap-1.5 uppercase tracking-wider">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>Tersedia
                </span>
                <span class="font-black text-slate-800" id="metric-available">{{ $availableUnits }}</span>
            </div>
            <div class="flex items-center justify-between gap-4 text-[10px] font-bold">
                <span class="text-slate-400 flex items-center gap-1.5 uppercase tracking-wider">
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>Bermain
                </span>
                <span class="font-black text-slate-800" id="metric-playing">{{ $playingUnits }}</span>
            </div>
            <div class="flex items-center justify-between gap-4 text-[10px] font-bold">
                <span class="text-slate-400 flex items-center gap-1.5 uppercase tracking-wider">
                    <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>Disewa
                </span>
                <span class="font-black text-slate-800" id="metric-rented">{{ $rentedUnits }}</span>
            </div>
            <div class="flex items-center justify-between gap-4 text-[10px] font-bold">
                <span class="text-slate-400 flex items-center gap-1.5 uppercase tracking-wider">
                    <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>Perbaikan
                </span>
                <span class="font-black text-slate-800" id="metric-maintenance">{{ $maintenanceUnits }}</span>
            </div>
        </div>
    </div>

    <!-- Transaksi Hari Ini -->
    <div class="bg-white border border-slate-100 rounded-2xl p-5 flex items-center gap-4 transition-all duration-200 hover:border-slate-200 shadow-sm">
        <div class="w-12 h-12 rounded-xl bg-slate-50 flex items-center justify-center text-slate-700">
            <i class="fa-solid fa-receipt text-xl"></i>
        </div>
        <div>
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Transaksi Hari Ini</p>
            <h3 class="text-2xl font-black text-slate-900 leading-tight" id="metric-transactions">{{ $totalTransactionsToday }}</h3>
        </div>
    </div>

    <!-- Pendapatan Hari Ini -->
    <div class="bg-white border border-slate-100 rounded-2xl p-5 flex items-center gap-4 transition-all duration-200 hover:border-slate-200 shadow-sm">
        <div class="w-12 h-12 rounded-xl bg-slate-50 flex items-center justify-center text-slate-700">
            <i class="fa-solid fa-money-bill-wave text-xl"></i>
        </div>
        <div>
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Revenue Hari Ini</p>
            <h3 class="text-2xl font-black text-slate-950 leading-tight" id="metric-revenue">Rp {{ number_format($totalRevenueToday, 0, ',', '.') }}</h3>
        </div>
    </div>
</div>

<!-- Charts Section -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 pt-4 border-t border-slate-100">
    <!-- Revenue & Transactions Line Chart Card (Takes 2 columns on large screens) -->
    <div class="lg:col-span-2 bg-white border border-slate-100 rounded-2xl p-5 shadow-sm hover:border-slate-200 transition-all duration-200 flex flex-col justify-between">
        <div class="flex items-center justify-between border-b border-slate-50 pb-4 mb-4">
            <div>
                <h3 class="text-base font-bold text-slate-900">Tren Pendapatan & Aktivitas</h3>
                <p class="text-xs text-slate-400 font-medium">Grafik pendapatan dan frekuensi transaksi dalam 7 hari terakhir.</p>
            </div>
            <div class="flex items-center gap-4 text-xs font-bold text-slate-500 bg-slate-50/50 p-1.5 rounded-xl border border-slate-100">
                <span class="flex items-center gap-1.5 text-indigo-600">
                    <span class="w-2.5 h-2.5 rounded bg-indigo-600"></span> Pendapatan (Rp)
                </span>
                <span class="flex items-center gap-1.5 text-slate-400">
                    <span class="w-2.5 h-2.5 rounded bg-slate-400"></span> Transaksi
                </span>
            </div>
        </div>
        <div class="relative w-full h-72">
            <canvas id="revenueFlowChart"></canvas>
        </div>
    </div>

    <!-- PlayStation Status Distribution Card (Takes 1 column) -->
    <div class="bg-white border border-slate-100 rounded-2xl p-5 shadow-sm hover:border-slate-200 transition-all duration-200 flex flex-col">
        <div class="border-b border-slate-50 pb-4 mb-4">
            <h3 class="text-base font-bold text-slate-900">Distribusi Status Unit</h3>
            <p class="text-xs text-slate-400 font-medium">Proporsi status operasional unit PlayStation saat ini.</p>
        </div>
        <div class="relative flex-1 flex items-center justify-center h-48 lg:h-auto">
            <canvas id="unitStatusDoughnut"></canvas>
        </div>
        <!-- Custom Legends for Doughnut Chart -->
        <div class="grid grid-cols-2 gap-2 mt-4 pt-4 border-t border-slate-50 text-xs font-semibold">
            <div class="flex items-center justify-between bg-slate-50/50 p-2 rounded-xl border border-slate-100/50">
                <span class="text-slate-400 flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>Tersedia
                </span>
                <span class="font-extrabold text-slate-800" id="legend-available">{{ $availableUnits }}</span>
            </div>
            <div class="flex items-center justify-between bg-slate-50/50 p-2 rounded-xl border border-slate-100/50">
                <span class="text-slate-400 flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-amber-500"></span>Bermain
                </span>
                <span class="font-extrabold text-slate-800" id="legend-playing">{{ $playingUnits }}</span>
            </div>
            <div class="flex items-center justify-between bg-slate-50/50 p-2 rounded-xl border border-slate-100/50">
                <span class="text-slate-400 flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-blue-500"></span>Disewa
                </span>
                <span class="font-extrabold text-slate-800" id="legend-rented">{{ $rentedUnits }}</span>
            </div>
            <div class="flex items-center justify-between bg-slate-50/50 p-2 rounded-xl border border-slate-100/50">
                <span class="text-slate-400 flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-rose-500"></span>Perbaikan
                </span>
                <span class="font-extrabold text-slate-800" id="legend-maintenance">{{ $maintenanceUnits }}</span>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // --- Chart.js Setup ---
    const chartData = @json($chartData);
    const labels = chartData.map(d => d.label);
    const revenues = chartData.map(d => d.revenue);
    const transactions = chartData.map(d => d.transactions);

    // 1. Line/Area Chart for Revenue and Transactions Flow
    const ctxRevenue = document.getElementById('revenueFlowChart').getContext('2d');
    
    // Create Indigo gradient fill for line chart
    const purpleGradient = ctxRevenue.createLinearGradient(0, 0, 0, 300);
    purpleGradient.addColorStop(0, 'rgba(79, 70, 229, 0.2)');
    purpleGradient.addColorStop(1, 'rgba(79, 70, 229, 0.005)');

    const revenueFlowChart = new Chart(ctxRevenue, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Pendapatan (Rp)',
                    data: revenues,
                    borderColor: '#4f46e5', // Indigo-600
                    borderWidth: 3,
                    backgroundColor: purpleGradient,
                    fill: true,
                    tension: 0.35,
                    pointBackgroundColor: '#4f46e5',
                    pointHoverRadius: 7,
                    pointHoverBackgroundColor: '#ffffff',
                    pointHoverBorderColor: '#4f46e5',
                    pointHoverBorderWidth: 3,
                    yAxisID: 'y'
                },
                {
                    label: 'Transaksi',
                    data: transactions,
                    borderColor: '#94a3b8', // Slate-400
                    borderWidth: 2,
                    borderDash: [5, 5],
                    backgroundColor: 'transparent',
                    pointBackgroundColor: '#94a3b8',
                    pointHoverRadius: 6,
                    tension: 0.35,
                    yAxisID: 'y1'
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false // We use our own custom header legends
                },
                tooltip: {
                    padding: 12,
                    backgroundColor: 'rgba(15, 23, 42, 0.95)', // Slate-900 opacity
                    titleFont: {
                        family: 'Plus Jakarta Sans',
                        size: 11,
                        weight: 'bold'
                    },
                    bodyFont: {
                        family: 'Plus Jakarta Sans',
                        size: 12,
                        weight: 'bold'
                    },
                    cornerRadius: 12,
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) {
                                label += ': ';
                            }
                            if (context.datasetIndex === 0) {
                                label += 'Rp ' + context.parsed.y.toLocaleString('id-ID');
                            } else {
                                label += context.parsed.y + ' trx';
                            }
                            return label;
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        font: {
                            family: 'Plus Jakarta Sans',
                            size: 10,
                            weight: '600'
                        },
                        color: '#94a3b8'
                    }
                },
                y: {
                    position: 'left',
                    grid: {
                        color: 'rgba(241, 245, 249, 0.8)', // slate-100
                        borderDash: [4, 4]
                    },
                    ticks: {
                        font: {
                            family: 'Plus Jakarta Sans',
                            size: 10,
                            weight: '600'
                        },
                        color: '#94a3b8',
                        callback: function(value) {
                            return 'Rp ' + (value >= 1000 ? (value / 1000) + 'k' : value);
                        }
                    }
                },
                y1: {
                    position: 'right',
                    grid: {
                        drawOnChartArea: false // avoid duplicate gridlines
                    },
                    ticks: {
                        font: {
                            family: 'Plus Jakarta Sans',
                            size: 10,
                            weight: '600'
                        },
                        color: '#cbd5e1'
                    }
                }
            }
        }
    });

    // 2. Doughnut Chart for PlayStation Status Distribution
    const ctxStatus = document.getElementById('unitStatusDoughnut').getContext('2d');
    const unitStatusDoughnut = new Chart(ctxStatus, {
        type: 'doughnut',
        data: {
            labels: ['Tersedia', 'Bermain', 'Disewa', 'Perbaikan'],
            datasets: [{
                data: [
                    {{ $availableUnits }},
                    {{ $playingUnits }},
                    {{ $rentedUnits }},
                    {{ $maintenanceUnits }}
                ],
                backgroundColor: [
                    '#10b981', // Emerald-500
                    '#f59e0b', // Amber-500
                    '#3b82f6', // Blue-500
                    '#f43f5e'  // Rose-500
                ],
                borderWidth: 4,
                borderColor: '#ffffff',
                hoverOffset: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '70%',
            plugins: {
                legend: {
                    display: false // Use custom legend elements
                },
                tooltip: {
                    padding: 10,
                    backgroundColor: 'rgba(15, 23, 42, 0.95)',
                    titleFont: {
                        family: 'Plus Jakarta Sans',
                        size: 11,
                        weight: 'bold'
                    },
                    bodyFont: {
                        family: 'Plus Jakarta Sans',
                        size: 11,
                        weight: '600'
                    },
                    cornerRadius: 8,
                    callbacks: {
                        label: function(context) {
                            const val = context.raw;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percent = Math.round((val / total) * 100);
                            return ` ${context.label}: ${val} unit (${percent}%)`;
                        }
                    }
                }
            }
        }
    });

    // --- UI Update Helper ---
    function updateDashboardUI(data) {
        // Update metrics summary cards
        document.getElementById('metric-available').textContent = data.availableUnits;
        document.getElementById('metric-playing').textContent = data.playingUnits;
        document.getElementById('metric-rented').textContent = data.rentedUnits;
        document.getElementById('metric-maintenance').textContent = data.maintenanceUnits;
        document.getElementById('metric-transactions').textContent = data.totalTransactionsToday;
        document.getElementById('metric-revenue').textContent = data.totalRevenueTodayFormatted;

        // Update doughnut custom legends
        document.getElementById('legend-available').textContent = data.availableUnits;
        document.getElementById('legend-playing').textContent = data.playingUnits;
        document.getElementById('legend-rented').textContent = data.rentedUnits;
        document.getElementById('legend-maintenance').textContent = data.maintenanceUnits;

        // Update doughnut chart datasets
        unitStatusDoughnut.data.datasets[0].data = [
            data.availableUnits,
            data.playingUnits,
            data.rentedUnits,
            data.maintenanceUnits
        ];
        unitStatusDoughnut.update();
    }

    // --- AJAX Sync & Dynamic Refresh ---
    async function refreshDashboard() {
        try {
            const res = await ajaxRequest("{{ route('dashboard.metrics') }}");
            if (res.success) {
                // Cache metrics locally
                localStorage.setItem('ps_cached_metrics', JSON.stringify(res.data));
                updateDashboardUI(res.data);
            }
        } catch (err) {
            console.warn("Failed to fetch live metrics, loading cached offline metrics...", err);
            const cachedDataStr = localStorage.getItem('ps_cached_metrics');
            if (cachedDataStr) {
                const cachedData = JSON.parse(cachedDataStr);
                updateDashboardUI(cachedData);
            }
        }
    }

    // Dynamic auto-sync every 30 seconds
    setInterval(refreshDashboard, 30000);
</script>
@endsection
