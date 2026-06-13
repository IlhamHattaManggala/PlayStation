@extends('layouts.app')

@section('title', 'Laporan Pendapatan')
@section('page_title', 'Laporan Pendapatan')

@section('content')

<!-- Print-Only Header -->
<div class="hidden print:block mb-6">
    <div class="flex items-center justify-between border-b-2 border-slate-950 pb-3">
        <div>
            <h1 class="text-xl font-black text-slate-950 uppercase tracking-tight">{{ \App\Models\AppSetting::first()->app_name ?? 'Rental PlayStation' }}</h1>
            <p class="text-xs text-slate-500 font-bold">Laporan Keuangan & Pendapatan Operasional</p>
        </div>
        <div class="text-right text-[10px] text-slate-500 font-semibold leading-tight">
            <p>Tanggal Cetak: {{ date('d M Y, H:i') }}</p>
            <p id="print-periode-meta">Periode: -</p>
        </div>
    </div>
</div>

<!-- Month Selector Card -->
<div class="bg-white border border-slate-100 rounded-2xl p-4 flex flex-col lg:flex-row lg:items-center justify-between gap-4 shadow-sm no-print">
    <div class="space-y-0.5">
        <h3 class="text-base font-bold text-slate-900">Laporan Keuangan</h3>
        <p class="text-[11px] text-slate-500 font-medium">Lihat ringkasan dan rincian pendapatan operasional.</p>
    </div>
    
    <!-- Controls Row -->
    <div class="flex flex-wrap items-center gap-3">
        <!-- Period Selection -->
        <div class="flex items-center gap-2">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Periode:</span>
            <select id="report-timeframe" onchange="changeTimeframe()" class="px-2.5 py-1.5 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:border-slate-900 focus:ring-1 focus:ring-slate-900 outline-none text-xs font-semibold text-slate-600 transition-all">
                <option value="day">Harian (Per Hari)</option>
                <option value="week">Mingguan (Per Minggu)</option>
                <option value="month" selected>Bulanan (Per Bulan)</option>
                <option value="year">Tahunan (Per Tahun)</option>
            </select>
        </div>

        <!-- Dynamic Input Pickers -->
        <div class="flex items-center gap-2">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider" id="picker-label">Pilih Bulan:</span>
            
            <!-- Daily Date Picker -->
            <input type="date" id="report-date" onchange="loadReport()" class="hidden px-3 py-1.5 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:border-slate-900 focus:ring-1 focus:ring-slate-900 outline-none text-xs font-semibold transition-all">
            
            <!-- Weekly Week Picker -->
            <input type="week" id="report-week" onchange="loadReport()" class="hidden px-3 py-1.5 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:border-slate-900 focus:ring-1 focus:ring-slate-900 outline-none text-xs font-semibold transition-all">
            
            <!-- Monthly Month Picker -->
            <input type="month" id="report-month" onchange="loadReport()" class="px-3 py-1.5 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:border-slate-900 focus:ring-1 focus:ring-slate-900 outline-none text-xs font-semibold transition-all">
            
            <!-- Yearly Dropdown -->
            <select id="report-year" onchange="loadReport()" class="hidden px-2.5 py-1.5 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:border-slate-900 focus:ring-1 focus:ring-slate-900 outline-none text-xs font-semibold text-slate-600 transition-all">
                <!-- Javascript populated -->
            </select>
        </div>

        <!-- Live Search Input -->
        <div class="relative w-full sm:w-auto">
            <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-slate-400">
                <i class="fa-solid fa-magnifying-glass text-xs"></i>
            </span>
            <input 
                type="text" 
                id="search-input" 
                oninput="applyLiveSearch()" 
                placeholder="Cari rincian..." 
                class="w-full sm:w-44 pl-8 pr-3 py-1.5 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:border-slate-900 focus:ring-1 focus:ring-slate-900 outline-none text-xs font-semibold transition-all placeholder:text-slate-400"
            >
        </div>
    </div>
</div>

<!-- Monthly Totals Metrics -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-4">
    <!-- Total Revenue -->
    <div class="bg-white border border-slate-100 rounded-2xl p-4 flex flex-col justify-between shadow-sm print:bg-slate-50 print:border-slate-200">
        <div class="flex items-center justify-between">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Total Pendapatan</span>
            <div class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center no-print">
                <i class="fa-solid fa-money-bill-trend-up"></i>
            </div>
        </div>
        <div class="mt-3">
            <h2 class="text-xl font-black text-slate-900" id="total-revenue">Rp 0</h2>
            <p class="text-[9px] text-slate-400 font-semibold mt-1">Gabungan seluruh transaksi</p>
        </div>
    </div>

    <!-- Onsite Play Revenue -->
    <div class="bg-white border border-slate-100 rounded-2xl p-4 flex flex-col justify-between shadow-sm print:bg-slate-50 print:border-slate-200">
        <div class="flex items-center justify-between">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Di Tempat</span>
            <div class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center no-print">
                <i class="fa-solid fa-tv"></i>
            </div>
        </div>
        <div class="mt-3">
            <h2 class="text-xl font-black text-slate-900" id="onsite-revenue">Rp 0</h2>
            <p class="text-[9px] text-amber-600 font-bold mt-1" id="onsite-count">0 Transaksi Selesai</p>
        </div>
    </div>

    <!-- Rental Revenue -->
    <div class="bg-white border border-slate-100 rounded-2xl p-4 flex flex-col justify-between shadow-sm print:bg-slate-50 print:border-slate-200">
        <div class="flex items-center justify-between">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Sewa Bawa Pulang</span>
            <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center no-print">
                <i class="fa-solid fa-truck-ramp-box"></i>
            </div>
        </div>
        <div class="mt-3">
            <h2 class="text-xl font-black text-slate-900" id="rental-revenue">Rp 0</h2>
            <p class="text-[9px] text-blue-600 font-bold mt-1" id="rental-count">0 Transaksi Terdaftar</p>
        </div>
    </div>
</div>

<!-- Daily Breakdown Title -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pt-4 border-t border-slate-100 no-print">
    <div>
        <h3 class="text-lg font-bold text-slate-900">Rincian Pendapatan Operasional</h3>
        <p class="text-xs text-slate-500 font-medium">Breakdown laporan rincian pendapatan dari transaksi yang terpilih.</p>
    </div>
    
    <!-- Action buttons -->
    <div class="flex items-center gap-2">
        <button onclick="window.print()" class="px-3.5 py-1.5 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-xl text-xs flex items-center gap-1.5 transition-all duration-200 active:scale-[0.98]">
            <i class="fa-solid fa-file-pdf"></i> Ekspor PDF / Cetak
        </button>
        <button onclick="exportToExcel()" class="px-3.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs flex items-center gap-1.5 transition-all duration-200 active:scale-[0.98]">
            <i class="fa-solid fa-file-excel"></i> Ekspor Excel (CSV)
        </button>
    </div>
</div>

<!-- Print-Only Title Placeholder -->
<div class="hidden print:block mt-4 mb-2">
    <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wide">Rincian Pendapatan Operasional</h3>
</div>

<!-- Daily Breakdown Table -->
<div class="bg-white border border-slate-100 rounded-2xl overflow-hidden shadow-sm print:border-slate-200">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="border-b border-slate-100 bg-slate-50/50 text-[10px] font-bold uppercase tracking-wider text-slate-400 print:bg-slate-100 print:text-slate-800">
                    <th class="px-4 py-2.5">Sumber / Periode</th>
                    <th class="px-4 py-2.5">Keterangan</th>
                    <th class="px-4 py-2.5">Pendapatan Main</th>
                    <th class="px-4 py-2.5">Pendapatan Sewa</th>
                    <th class="px-4 py-2.5 text-right">Total Pendapatan</th>
                </tr>
            </thead>
            <tbody id="breakdown-table-body" class="divide-y divide-slate-100 text-sm font-medium text-slate-700">
                <tr>
                    <td colspan="5" class="text-center py-8 text-slate-400">
                        <i class="fa-solid fa-spinner animate-spin text-2xl mb-2 block mx-auto"></i>
                        Memuat rincian laporan...
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- CSS style block specifically designed for custom print layout -->
<style>
    @media print {
        /* Hide layout sidebar & header */
        aside, header, #sidebar, #sidebar-overlay, .no-print {
            display: none !important;
        }

        /* Set base container and layouts */
        body {
            background-color: white !important;
            color: black !important;
            font-size: 11px !important;
            margin: 0 !important;
            padding: 0 !important;
        }

        .flex-1 {
            padding-left: 0 !important;
            margin: 0 !important;
        }

        main {
            padding: 0 !important;
            margin: 0 !important;
            max-width: 100% !important;
            width: 100% !important;
        }

        /* Structure totals summary cards grid */
        .grid {
            display: grid !important;
            grid-template-columns: repeat(3, 1fr) !important;
            gap: 15px !important;
            margin-bottom: 25px !important;
        }

        .grid > div {
            border: 1px solid #cbd5e1 !important;
            border-radius: 8px !important;
            padding: 12px !important;
            background-color: #f8fafc !important;
        }

        /* Tables layout */
        table {
            width: 100% !important;
            border-collapse: collapse !important;
        }

        th, td {
            border: 1px solid #cbd5e1 !important;
            padding: 6px 10px !important;
            font-size: 10px !important;
        }

        th {
            background-color: #f1f5f9 !important;
            color: #1e293b !important;
            font-weight: bold !important;
        }

        tr {
            page-break-inside: avoid !important;
        }
    }
</style>

@endsection

@section('scripts')
<script>
    let globalBreakdown = [];
    let globalMetrics = {};

    // Set current month/week/date values in the input by default
    function initReport() {
        const now = new Date();
        const year = now.getFullYear();
        const month = String(now.getMonth() + 1).padStart(2, '0');
        const date = String(now.getDate()).padStart(2, '0');

        // Populate values
        document.getElementById('report-month').value = `${year}-${month}`;
        document.getElementById('report-date').value = `${year}-${month}-${date}`;
        
        // Setup week input value e.g. "2026-W24"
        const weekNum = getWeekNumber(now);
        document.getElementById('report-week').value = `${year}-W${String(weekNum).padStart(2, '0')}`;

        // Populate years dropdown (current year +/- 5 years)
        const yearSelect = document.getElementById('report-year');
        let yearOptions = '';
        for (let y = year + 2; y >= year - 5; y--) {
            const selected = y === year ? 'selected' : '';
            yearOptions += `<option value="${y}" ${selected}>${y}</option>`;
        }
        yearSelect.innerHTML = yearOptions;

        changeTimeframe();
    }

    // Get ISO-8601 week number
    function getWeekNumber(d) {
        d = new Date(Date.UTC(d.getFullYear(), d.getMonth(), d.getDate()));
        d.setUTCDate(d.getUTCDate() + 4 - (d.getUTCDay()||7));
        var yearStart = new Date(Date.UTC(d.getUTCFullYear(),0,1));
        var weekNo = Math.ceil(( ( (d - yearStart) / 86400000) + 1)/7);
        return weekNo;
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initReport);
    } else {
        initReport();
    }

    // Toggle input pickers based on selected timeframe
    function changeTimeframe() {
        const timeframe = document.getElementById('report-timeframe').value;
        const pickerLabel = document.getElementById('picker-label');
        
        const dateInput = document.getElementById('report-date');
        const weekInput = document.getElementById('report-week');
        const monthInput = document.getElementById('report-month');
        const yearInput = document.getElementById('report-year');

        // Hide all
        dateInput.classList.add('hidden');
        weekInput.classList.add('hidden');
        monthInput.classList.add('hidden');
        yearInput.classList.add('hidden');

        // Show selected
        if (timeframe === 'day') {
            pickerLabel.textContent = 'Pilih Tanggal:';
            dateInput.classList.remove('hidden');
        } else if (timeframe === 'week') {
            pickerLabel.textContent = 'Pilih Minggu:';
            weekInput.classList.remove('hidden');
        } else if (timeframe === 'year') {
            pickerLabel.textContent = 'Pilih Tahun:';
            yearInput.classList.remove('hidden');
        } else {
            pickerLabel.textContent = 'Pilih Bulan:';
            monthInput.classList.remove('hidden');
        }

        // Reset search input
        document.getElementById('search-input').value = '';

        loadReport();
    }

    // Load report data
    async function loadReport() {
        const timeframe = document.getElementById('report-timeframe').value;
        const date = document.getElementById('report-date').value;
        const week = document.getElementById('report-week').value;
        const month = document.getElementById('report-month').value;
        const year = document.getElementById('report-year').value;

        const tbody = document.getElementById('breakdown-table-body');
        
        tbody.innerHTML = `
            <tr>
                <td colspan="5" class="text-center py-8 text-slate-400">
                    <i class="fa-solid fa-spinner animate-spin text-2xl mb-2 block mx-auto"></i>
                    Memuat data laporan...
                </td>
            </tr>
        `;

        try {
            const queryParams = new URLSearchParams({
                timeframe: timeframe,
                date: date,
                week: week,
                month: month,
                year: year
            });

            const res = await ajaxRequest(`/admin/api/reports/filter?${queryParams.toString()}`);
            if (res.success) {
                globalMetrics = res.data;
                updateMetrics(res.data);
                globalBreakdown = res.data.daily_breakdown;
                applyLiveSearch(); // Apply filter to render table
            }
        } catch (err) {
            showToast('error', 'Gagal memuat data laporan pendapatan.');
        }
    }

    // Update aggregate metrics display
    function updateMetrics(data) {
        document.getElementById('total-revenue').textContent = data.total_revenue;
        document.getElementById('onsite-revenue').textContent = data.onsite_revenue;
        document.getElementById('onsite-count').textContent = `${data.onsite_count} Transaksi Selesai`;
        document.getElementById('rental-revenue').textContent = data.rental_revenue;
        document.getElementById('rental-count').textContent = `${data.rental_count} Transaksi Terdaftar`;
        
        // Update print meta
        document.getElementById('print-periode-meta').textContent = `Periode: ${data.month_name}`;
    }

    // Apply local client-side search/filter on breakdown rows
    function applyLiveSearch() {
        const query = document.getElementById('search-input').value.toLowerCase().trim();
        
        const filtered = globalBreakdown.filter(item => {
            const matchesLabel = item.date_formatted.toLowerCase().includes(query);
            const matchesSublabel = item.day_name.toLowerCase().includes(query);
            return matchesLabel || matchesSublabel;
        });

        renderTable(filtered);
    }

    // Render Table
    function renderTable(breakdown) {
        const tbody = document.getElementById('breakdown-table-body');
        if (breakdown.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="5" class="text-center py-8 text-slate-400 font-medium">
                        Tidak ada rincian transaksi yang cocok dengan filter atau pencarian.
                    </td>
                </tr>
            `;
            return;
        }

        tbody.innerHTML = breakdown.map(day => {
            const totalClass = day.total_revenue > 0 ? 'text-slate-900 font-extrabold' : 'text-slate-400 font-normal';
            
            return `
                <tr class="hover:bg-slate-50/50 transition-colors duration-150 text-[11px]">
                    <td class="px-4 py-2.5 font-bold text-slate-800">${day.date_formatted}</td>
                    <td class="px-4 py-2.5 text-slate-400 font-semibold capitalize">${day.day_name}</td>
                    <td class="px-4 py-2.5 text-slate-500 font-medium">${day.onsite_revenue_formatted}</td>
                    <td class="px-4 py-2.5 text-slate-500 font-medium">${day.rental_revenue_formatted}</td>
                    <td class="px-4 py-2.5 text-right ${totalClass}">${day.total_revenue_formatted}</td>
                </tr>
            `;
        }).join('');
    }

    // Export Breakdown Table to Excel (.xls) format preserving styles, merge cells, and formatting
    function exportToExcel() {
        const timeframe = document.getElementById('report-timeframe').value;
        const timeframeText = document.getElementById('report-timeframe').options[document.getElementById('report-timeframe').selectedIndex].text;
        
        let activePicker = '';
        if (timeframe === 'day') activePicker = document.getElementById('report-date').value;
        else if (timeframe === 'week') activePicker = document.getElementById('report-week').value;
        else if (timeframe === 'year') activePicker = document.getElementById('report-year').value;
        else activePicker = document.getElementById('report-month').value;

        // Dynamic construction of Excel XML namespace tags to prevent Laravel Blade compiler 
        // from incorrectly parsing them as custom Blade component tags (e.g. x-ExcelWorkbook)
        const pfx = 'x';
        const xWorkbookOpen = '<' + pfx + ':ExcelWorkbook>';
        const xWorkbookClose = '</' + pfx + ':ExcelWorkbook>';
        const xWorksheetsOpen = '<' + pfx + ':ExcelWorksheets>';
        const xWorksheetsClose = '</' + pfx + ':ExcelWorksheets>';
        const xWorksheetOpen = '<' + pfx + ':ExcelWorksheet>';
        const xWorksheetClose = '</' + pfx + ':ExcelWorksheet>';
        const xNameOpen = '<' + pfx + ':Name>';
        const xNameClose = '</' + pfx + ':Name>';
        const xWorksheetOptionsOpen = '<' + pfx + ':WorksheetOptions>';
        const xWorksheetOptionsClose = '</' + pfx + ':WorksheetOptions>';
        const xDisplayGridlines = '<' + pfx + ':DisplayGridlines/>';

        // Build the HTML template for Excel
        let excelHtml = `
<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">
<head>
    <meta charset="UTF-8">
    <!--[if gte mso 9]>
    <xml>
        ${xWorkbookOpen}
            ${xWorksheetsOpen}
                ${xWorksheetOpen}
                    ${xNameOpen}Laporan Pendapatan${xNameClose}
                    ${xWorksheetOptionsOpen}
                        ${xDisplayGridlines}
                    ${xWorksheetOptionsClose}
                ${xWorksheetClose}
            ${xWorksheetsClose}
        ${xWorkbookClose}
    </xml>
    <![endif]-->
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        table {
            border-collapse: collapse;
            width: 100%;
        }
        th, td {
            border: 0.5pt solid #cbd5e1;
            padding: 6px 8px;
            font-size: 10pt;
        }
        .title {
            font-size: 14pt;
            font-weight: bold;
            text-align: center;
            height: 30px;
        }
        .meta-row td {
            font-size: 11pt;
            font-weight: bold;
            height: 22px;
        }
        .header-cell {
            background-color: #e2e8f0;
            font-weight: bold;
            text-align: center;
        }
        .text-center {
            text-align: center;
        }
        .text-right {
            text-align: right;
        }
    </style>
</head>
<body>
    <table>
        <!-- Row 1: Title (Merged A-E) -->
        <tr>
            <td colspan="5" class="title" style="font-size: 14pt; font-weight: bold; text-align: center; height: 30px; border: none;">
                LAPORAN PENDAPATAN - PLAYSTATION RENTAL
            </td>
        </tr>
        
        <!-- Row 2: Tipe Periode (Merged A-E) -->
        <tr>
            <td colspan="5" style="font-weight: bold; font-size: 11pt; border: none;">
                Tipe Periode : ${timeframeText}
            </td>
        </tr>
        
        <!-- Row 3: Nilai Periode (Merged A-E) -->
        <tr>
            <td colspan="5" style="font-weight: bold; font-size: 11pt; border: none;">
                Nilai Periode : ${activePicker}
            </td>
        </tr>
        
        <!-- Row 4: Total Pendapatan (Merged A-E, formatted text) -->
        <tr>
            <td colspan="5" style="font-weight: bold; font-size: 11pt; border: none; color: #1e1b4b;">
                Total Pendapatan : Rp. ${(globalMetrics.total_revenue_raw || 0).toLocaleString('id-ID')}
            </td>
        </tr>
        
        <!-- Row 5: Pendapatan Main (Merged A-E, formatted text) -->
        <tr>
            <td colspan="5" style="font-size: 10pt; border: none; color: #b45309;">
                Pendapatan Main : Rp. ${(globalMetrics.onsite_revenue_raw || 0).toLocaleString('id-ID')} (${globalMetrics.onsite_count || 0} Transaksi)
            </td>
        </tr>
        
        <!-- Row 6: Pendapatan Sewa (Merged A-E, formatted text) -->
        <tr>
            <td colspan="5" style="font-size: 10pt; border: none; color: #1d4ed8;">
                Pendapatan Sewa : Rp. ${(globalMetrics.rental_revenue_raw || 0).toLocaleString('id-ID')} (${globalMetrics.rental_count || 0} Transaksi)
            </td>
        </tr>
        
        <!-- Row 7: Empty Spacing Row -->
        <tr>
            <td colspan="5" style="border: none; height: 15px;"></td>
        </tr>
        
        <!-- Row 8: Table Header -->
        <tr>
            <th style="background-color: #e2e8f0; font-weight: bold; border: 0.5pt solid #94a3b8; text-align: left;">Sumber / Periode</th>
            <th style="background-color: #e2e8f0; font-weight: bold; border: 0.5pt solid #94a3b8; text-align: left;">Keterangan</th>
            <th style="background-color: #e2e8f0; font-weight: bold; border: 0.5pt solid #94a3b8; text-align: right;">Pendapatan Main</th>
            <th style="background-color: #e2e8f0; font-weight: bold; border: 0.5pt solid #94a3b8; text-align: right;">Pendapatan Sewa</th>
            <th style="background-color: #e2e8f0; font-weight: bold; border: 0.5pt solid #94a3b8; text-align: right;">Total Pendapatan</th>
        </tr>
`;

        // Filter elements locally based on current search input to match exactly what is in UI
        const query = document.getElementById('search-input').value.toLowerCase().trim();
        const filtered = globalBreakdown.filter(item => {
            const matchesLabel = item.date_formatted.toLowerCase().includes(query);
            const matchesSublabel = item.day_name.toLowerCase().includes(query);
            return matchesLabel || matchesSublabel;
        });

        if (filtered.length === 0) {
            excelHtml += `
        <tr>
            <td colspan="5" style="text-align: center; border: 0.5pt solid #cbd5e1; color: #94a3b8;">Tidak ada rincian data</td>
        </tr>
`;
        } else {
            filtered.forEach(day => {
                const col1 = day.date_formatted;
                const col2 = day.day_name;
                const col3 = parseFloat(day.onsite_revenue) || 0;
                const col4 = parseFloat(day.rental_revenue) || 0;
                const col5 = parseFloat(day.total_revenue) || 0;
                
                excelHtml += `
        <tr>
            <td style="border: 0.5pt solid #cbd5e1; text-align: left;">${col1}</td>
            <td style="border: 0.5pt solid #cbd5e1; text-align: left; text-transform: capitalize;">${col2}</td>
            <td style="border: 0.5pt solid #cbd5e1; text-align: right; mso-number-format:'&quot;Rp. &quot;#,##0';">${col3}</td>
            <td style="border: 0.5pt solid #cbd5e1; text-align: right; mso-number-format:'&quot;Rp. &quot;#,##0';">${col4}</td>
            <td style="border: 0.5pt solid #cbd5e1; text-align: right; mso-number-format:'&quot;Rp. &quot;#,##0'; font-weight: bold;">${col5}</td>
        </tr>
`;
            });
        }

        excelHtml += `
    </table>
</body>
</html>
`;
        
        const blob = new Blob([excelHtml], { type: 'application/vnd.ms-excel;charset=utf-8;' });
        const url = URL.createObjectURL(blob);
        const link = document.createElement("a");
        link.setAttribute("href", url);
        link.setAttribute("download", `laporan_pendapatan_${timeframe}_${activePicker.replace(/[^a-zA-Z0-9]/g, '_')}.xls`);
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }
</script>
@endsection
