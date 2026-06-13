@extends('layouts.app')

@section('title', 'Riwayat Transaksi')
@section('page_title', 'Riwayat Transaksi')

@section('content')

<!-- Filters Panel Card -->
<div class="bg-white border border-slate-100 rounded-2xl p-4 space-y-3 shadow-sm">
    <div class="flex items-center justify-between pb-2 border-b border-slate-50">
        <div>
            <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Filter Riwayat</h3>
            <p class="text-[9px] text-slate-400 font-medium">Saring transaksi berdasarkan parameter di bawah ini.</p>
        </div>
        <button onclick="resetFilters()" class="text-[11px] font-semibold text-slate-400 hover:text-slate-900 flex items-center gap-1.5 transition-all">
            <i class="fa-solid fa-arrow-rotate-left text-[10px]"></i> Reset Filter
        </button>
    </div>

    <!-- Inputs Row -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
        <!-- Date -->
        <div class="space-y-1">
            <label class="text-[9px] font-bold text-slate-400 uppercase tracking-wider">Tanggal Transaksi</label>
            <input type="date" id="filter-date" onchange="applyFilters()" class="w-full px-3 py-1.5 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:border-slate-900 focus:ring-1 focus:ring-slate-900 outline-none text-xs font-semibold transition-all">
        </div>

        <!-- Service Type -->
        <div class="space-y-1">
            <label class="text-[9px] font-bold text-slate-400 uppercase tracking-wider">Jenis Layanan</label>
            <select id="filter-type" onchange="adjustStatusOptions(); applyFilters();" class="w-full px-2.5 py-1.5 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:border-slate-900 focus:ring-1 focus:ring-slate-900 outline-none text-xs font-semibold text-slate-600 transition-all">
                <option value="all">Semua Layanan</option>
                <option value="Di Tempat">Di Tempat</option>
                <option value="Sewa PS">Sewa PS</option>
            </select>
        </div>

        <!-- Status -->
        <div class="space-y-1">
            <label class="text-[9px] font-bold text-slate-400 uppercase tracking-wider">Status Transaksi</label>
            <select id="filter-status" onchange="applyFilters()" class="w-full px-2.5 py-1.5 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:border-slate-900 focus:ring-1 focus:ring-slate-900 outline-none text-xs font-semibold text-slate-600 transition-all">
                <option value="all">Semua Status</option>
                <!-- populated dynamically -->
            </select>
        </div>

        <!-- PlayStation Unit -->
        <div class="space-y-1">
            <label class="text-[9px] font-bold text-slate-400 uppercase tracking-wider">Pilih Unit PS</label>
            <select id="filter-unit" onchange="applyFilters()" class="w-full px-2.5 py-1.5 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:border-slate-900 focus:ring-1 focus:ring-slate-900 outline-none text-xs font-semibold text-slate-600 transition-all">
                <option value="all">Semua Unit</option>
                @foreach($units as $unit)
                    <option value="{{ $unit->id }}">{{ $unit->name }} ({{ $unit->type }})</option>
                @endforeach
            </select>
        </div>
    </div>
</div>

<!-- History Table Card -->
<div class="bg-white border border-slate-100 rounded-2xl overflow-hidden shadow-sm">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="border-b border-slate-100 bg-slate-50/50 text-[10px] font-bold uppercase tracking-wider text-slate-400">
                    <th class="px-4 py-2.5">Layanan</th>
                    <th class="px-4 py-2.5">Unit PS</th>
                    <th class="px-4 py-2.5">Pelanggan</th>
                    <th class="px-4 py-2.5">Mulai</th>
                    <th class="px-4 py-2.5">Selesai / Kembali</th>
                    <th class="px-4 py-2.5">Durasi / Hari</th>
                    <th class="px-4 py-2.5">Tarif</th>
                    <th class="px-4 py-2.5">Total</th>
                    <th class="px-4 py-2.5">Status</th>
                </tr>
            </thead>
            <tbody id="history-table-body" class="divide-y divide-slate-100 text-xs font-semibold text-slate-700">
                <tr>
                    <td colspan="9" class="text-center py-8 text-slate-400">
                        <i class="fa-solid fa-spinner animate-spin text-2xl mb-2 block mx-auto"></i>
                        Memuat riwayat transaksi...
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

@endsection

@section('scripts')
<script>
    // Adjust status select input options dynamically based on selected service type
    function adjustStatusOptions() {
        const type = document.getElementById('filter-type').value;
        const statusSelect = document.getElementById('filter-status');
        
        let options = '<option value="all">Semua Status</option>';
        
        if (type === 'all') {
            options += `
                <option value="Berjalan">Berjalan (Main)</option>
                <option value="Selesai">Selesai (Main)</option>
                <option value="Disewa">Disewa (Pulang)</option>
                <option value="Dikembalikan">Dikembalikan (Pulang)</option>
            `;
        } else if (type === 'Di Tempat') {
            options += `
                <option value="Berjalan">Berjalan</option>
                <option value="Selesai">Selesai</option>
            `;
        } else if (type === 'Sewa PS') {
            options += `
                <option value="Disewa">Disewa</option>
                <option value="Dikembalikan">Dikembalikan</option>
            `;
        }
        
        statusSelect.innerHTML = options;
        statusSelect.value = 'all';
    }

    // Reset all filters
    function resetFilters() {
        document.getElementById('filter-date').value = '';
        document.getElementById('filter-type').value = 'all';
        document.getElementById('filter-unit').value = 'all';
        adjustStatusOptions();
        applyFilters();
    }

    // Apply filters and fetch filtered results
    async function applyFilters() {
        const date = document.getElementById('filter-date').value;
        const type = document.getElementById('filter-type').value;
        const status = document.getElementById('filter-status').value;
        const unitId = document.getElementById('filter-unit').value;

        const tbody = document.getElementById('history-table-body');
        tbody.innerHTML = `
            <tr>
                <td colspan="9" class="text-center py-8 text-slate-400">
                    <i class="fa-solid fa-spinner animate-spin text-2xl mb-2 block mx-auto"></i>
                    Menyaring data...
                </td>
            </tr>
        `;

        try {
            const queryParams = new URLSearchParams({
                date: date,
                type: type,
                status: status,
                playstation_unit_id: unitId
            });

            const res = await ajaxRequest(`/admin/api/transactions/filter?${queryParams.toString()}`);
            if (res.success) {
                renderTable(res.data);
            }
        } catch (err) {
            showToast('error', 'Gagal menyaring data transaksi.');
        }
    }

    // Render Table
    function renderTable(trxs) {
        const tbody = document.getElementById('history-table-body');
        if (trxs.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="9" class="text-center py-8 text-slate-400 font-medium">
                        Tidak ada riwayat transaksi yang cocok dengan filter.
                    </td>
                </tr>
            `;
            return;
        }

        tbody.innerHTML = trxs.map(trx => {
            const serviceBadge = trx.type === 'Di Tempat' ? 
                                 'bg-amber-50 text-amber-700 border-amber-100' : 
                                 'bg-blue-50 text-blue-700 border-blue-100';
            
            const serviceIcon = trx.type === 'Di Tempat' ? 
                                 '<i class="fa-solid fa-tv mr-1.5"></i>' : 
                                 '<i class="fa-solid fa-truck-ramp-box mr-1.5"></i>';

            let statusClass = '';
            if (trx.status === 'Berjalan') statusClass = 'bg-amber-50 text-amber-600 border-amber-100 animate-pulse';
            else if (trx.status === 'Selesai') statusClass = 'bg-emerald-50 text-emerald-600 border-emerald-100';
            else if (trx.status === 'Disewa') statusClass = 'bg-blue-50 text-blue-600 border-blue-100';
            else if (trx.status === 'Dikembalikan') statusClass = 'bg-slate-50 text-slate-500 border-slate-200';

            const phoneDisplay = trx.phone && trx.phone !== '-' ? `<br><span class="text-[9px] text-slate-400 font-medium">${trx.phone}</span>` : '';
            const ktpLink = trx.identity_card_url ? 
                            `<br><a href="${trx.identity_card_url}" target="_blank" class="inline-flex items-center gap-1 text-[9px] text-indigo-500 hover:text-indigo-700 font-bold mt-0.5"><i class="fa-solid fa-address-card"></i> Lihat Jaminan</a>` : '';

            return `
                <tr class="hover:bg-slate-50/50 transition-colors duration-150 text-[11px]">
                    <td class="px-4 py-2.5">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[9px] font-bold border ${serviceBadge}">
                            ${serviceIcon} ${trx.type}
                        </span>
                    </td>
                    <td class="px-4 py-2.5 font-bold text-slate-800">${trx.unit_name}</td>
                    <td class="px-4 py-2.5">
                        <span class="text-slate-800 font-bold">${trx.customer}</span>
                        ${phoneDisplay}
                        ${ktpLink}
                    </td>
                    <td class="px-4 py-2.5 text-slate-500 font-medium">${trx.start_time}</td>
                    <td class="px-4 py-2.5 text-slate-500 font-medium">${trx.end_time}</td>
                    <td class="px-4 py-2.5 text-slate-800 font-bold">${trx.duration_or_days}</td>
                    <td class="px-4 py-2.5 text-slate-400 font-medium">${trx.rate}</td>
                    <td class="px-4 py-2.5 text-slate-900 font-extrabold">${trx.total_price}</td>
                    <td class="px-4 py-2.5">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[9px] font-bold border ${statusClass}">${trx.status}</span>
                    </td>
                </tr>
            `;
        }).join('');
    }

    // Initial Load
    function initTransactions() {
        adjustStatusOptions();
        applyFilters();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initTransactions);
    } else {
        initTransactions();
    }
</script>
@endsection
