@extends('layouts.app')

@section('title', 'Kelola Unit PlayStation')
@section('page_title', 'Kelola Unit PlayStation')

@section('content')

<!-- Header Action Card -->
<div class="bg-white border border-slate-100 rounded-2xl p-4 flex flex-col md:flex-row md:items-center justify-between gap-3 shadow-sm">
    <div>
        <h3 class="text-base font-bold text-slate-900">Daftar PlayStation / Room</h3>
        <p class="text-[11px] text-slate-500 font-medium">Tambah, edit, atau hapus unit dan ruangan PlayStation yang disewakan.</p>
    </div>
    <button onclick="openAddModal()" class="py-2 px-3 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-xl text-xs flex items-center gap-2 shadow-sm transition-all duration-200 active:scale-[0.98]">
        <i class="fa-solid fa-plus text-[11px]"></i> Tambah Unit Baru
    </button>
</div>

<!-- Table Card -->
<div class="bg-white border border-slate-100 rounded-2xl overflow-hidden shadow-sm">
    <!-- Live Search & Filter Bar -->
    <div class="px-4 py-3 bg-slate-50/50 border-b border-slate-100 flex flex-col sm:flex-row items-center gap-3 justify-between">
        <div class="relative w-full sm:max-w-xs">
            <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-slate-400">
                <i class="fa-solid fa-magnifying-glass text-xs"></i>
            </span>
            <input type="text" id="search-input" oninput="applyFilters()" placeholder="Cari unit/room..." class="w-full pl-9 pr-3 py-1.5 rounded-xl border border-slate-200 bg-white placeholder:text-slate-400 focus:border-slate-900 focus:ring-1 focus:ring-slate-900 outline-none text-xs font-semibold transition-all">
        </div>
        <div class="flex items-center gap-2 w-full sm:w-auto">
            <select id="filter-type" onchange="applyFilters()" class="w-1/2 sm:w-32 px-2.5 py-1.5 rounded-xl border border-slate-200 bg-white focus:border-slate-900 focus:ring-1 focus:ring-slate-900 outline-none text-xs font-semibold text-slate-600 transition-all">
                <option value="">Semua Tipe</option>
                <option value="PS3">PS3</option>
                <option value="PS4">PS4</option>
                <option value="PS5">PS5</option>
            </select>
            <select id="filter-status" onchange="applyFilters()" class="w-1/2 sm:w-36 px-2.5 py-1.5 rounded-xl border border-slate-200 bg-white focus:border-slate-900 focus:ring-1 focus:ring-slate-900 outline-none text-xs font-semibold text-slate-600 transition-all">
                <option value="">Semua Status</option>
                <option value="Tersedia">Tersedia</option>
                <option value="Bermain">Bermain</option>
                <option value="Disewa">Disewa</option>
                <option value="Maintenance">Maintenance</option>
            </select>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="border-b border-slate-100 bg-slate-50/50 text-[10px] font-bold uppercase tracking-wider text-slate-400">
                    <th class="px-4 py-2.5">Nama Unit/Room</th>
                    <th class="px-4 py-2.5">Tipe PS</th>
                    <th class="px-4 py-2.5">Status</th>
                    <th class="px-4 py-2.5">Keterangan</th>
                    <th class="px-4 py-2.5 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody id="units-table-body" class="divide-y divide-slate-100 text-sm font-medium text-slate-700">
                <!-- Data populated dynamically via JS -->
                <tr>
                    <td colspan="5" class="text-center py-8 text-slate-400">
                        <i class="fa-solid fa-spinner animate-spin text-2xl mb-2 block mx-auto"></i>
                        Memuat data unit...
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Add/Edit Unit -->
<div id="unit-modal" class="fixed inset-0 bg-slate-950/50 flex items-center justify-center z-50 p-4 hidden">
    <div class="bg-white border border-slate-100 rounded-2xl w-full max-w-md shadow-2xl p-6 space-y-5 transition-all duration-300">
        
        <!-- Header -->
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <div>
                <h3 class="text-lg font-bold text-slate-900" id="modal-title">Tambah Unit PlayStation</h3>
                <p class="text-xs text-slate-400 font-medium">Lengkapi formulir di bawah ini.</p>
            </div>
            <button onclick="closeModal()" class="text-slate-400 hover:text-slate-600 p-2 hover:bg-slate-50 rounded-xl transition-all">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <!-- Form -->
        <form id="unit-form" onsubmit="submitForm(event)" class="space-y-4">
            <input type="hidden" name="id" id="unit-id">
            
            <div class="space-y-1.5">
                <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">Nama Unit / Room</label>
                <input type="text" name="name" id="unit-name" required placeholder="Contoh: PS 4 B, VIP Room 3" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 placeholder:text-slate-400 focus:bg-white focus:border-slate-900 focus:ring-1 focus:ring-slate-900 outline-none text-sm font-semibold transition-all">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div class="space-y-1.5">
                    <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">Tipe PlayStation</label>
                    <select name="type" id="unit-type" required class="w-full px-3 py-2.5 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:border-slate-900 focus:ring-1 focus:ring-slate-900 outline-none text-sm font-semibold transition-all">
                        <option value="PS3">PS3</option>
                        <option value="PS4">PS4</option>
                        <option value="PS5">PS5</option>
                    </select>
                </div>
                <div class="space-y-1.5">
                    <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">Status Awal</label>
                    <select name="status" id="unit-status" required class="w-full px-3 py-2.5 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:border-slate-900 focus:ring-1 focus:ring-slate-900 outline-none text-sm font-semibold transition-all">
                        <option value="Tersedia">Tersedia</option>
                        <option value="Maintenance">Maintenance</option>
                        <option value="Bermain" disabled>Bermain (Otomatis)</option>
                        <option value="Disewa" disabled>Disewa (Otomatis)</option>
                    </select>
                </div>
            </div>

            <div class="space-y-1.5">
                <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">Keterangan / Fasilitas</label>
                <textarea name="description" id="unit-description" rows="3" placeholder="Fasilitas tambahan, kondisi unit, dll..." class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 placeholder:text-slate-400 focus:bg-white focus:border-slate-900 focus:ring-1 focus:ring-slate-900 outline-none text-sm font-medium transition-all resize-none"></textarea>
            </div>

            <!-- Buttons -->
            <div class="flex items-center justify-end gap-3 pt-2">
                <button type="button" onclick="closeModal()" class="py-2.5 px-4 border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-bold rounded-xl text-xs active:scale-[0.98] transition-all">
                    Batal
                </button>
                <button type="submit" class="py-2.5 px-5 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-xl text-xs shadow-md active:scale-[0.98] transition-all">
                    Simpan Unit
                </button>
            </div>
        </form>

    </div>
</div>

@endsection

@section('scripts')
<script>
    let allUnits = [];

    // Load Data
    async function loadUnits() {
        try {
            const res = await ajaxRequest('/admin/api/units');
            if (res.success) {
                allUnits = res.data;
                applyFilters();
            }
        } catch (err) {
            showToast('error', 'Gagal memuat data unit.');
        }
    }

    // Apply Live Filters and Search
    function applyFilters() {
        const query = document.getElementById('search-input').value.toLowerCase().trim();
        const type = document.getElementById('filter-type').value;
        const status = document.getElementById('filter-status').value;

        const filtered = allUnits.filter(unit => {
            const nameMatch = unit.name.toLowerCase().includes(query);
            const descMatch = (unit.description || '').toLowerCase().includes(query);
            const typeMatch = !type || unit.type === type;
            const statusMatch = !status || unit.status === status;
            return (nameMatch || descMatch) && typeMatch && statusMatch;
        });

        renderTable(filtered);
    }

    // Render Table
    function renderTable(units) {
        const tbody = document.getElementById('units-table-body');
        if (units.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="5" class="text-center py-6 text-slate-400 font-medium">
                        Tidak ada unit PlayStation yang cocok dengan pencarian / filter.
                    </td>
                </tr>
            `;
            return;
        }

        tbody.innerHTML = units.map(unit => {
            const statusClass = unit.status === 'Tersedia' ? 'bg-emerald-50 text-emerald-700 border-emerald-100' :
                                unit.status === 'Bermain' ? 'bg-amber-50 text-amber-700 border-amber-100' :
                                unit.status === 'Disewa' ? 'bg-blue-50 text-blue-700 border-blue-100' :
                                'bg-rose-50 text-rose-700 border-rose-100';

            const typeBadge = unit.type === 'PS5' ? 'bg-indigo-50 text-indigo-700 border-indigo-100' :
                              unit.type === 'PS4' ? 'bg-violet-50 text-violet-700 border-violet-100' :
                              'bg-slate-50 text-slate-600 border-slate-200';

            return `
                <tr class="hover:bg-slate-50/50 transition-colors duration-150">
                    <td class="px-4 py-2.5 font-bold text-slate-800 text-xs">${unit.name}</td>
                    <td class="px-4 py-2.5">
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase border ${typeBadge}">${unit.type}</span>
                    </td>
                    <td class="px-4 py-2.5">
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold border ${statusClass}">${unit.status}</span>
                    </td>
                    <td class="px-4 py-2.5 text-slate-500 font-normal max-w-xs truncate text-xs">${unit.description || '-'}</td>
                    <td class="px-4 py-2.5 text-right">
                        <div class="flex items-center justify-end gap-1.5">
                            <button onclick="openEditModal(${unit.id})" class="p-1.5 text-slate-400 hover:text-slate-800 hover:bg-slate-100 rounded-lg transition-all text-[13px]" title="Edit">
                                <i class="fa-solid fa-pen"></i>
                            </button>
                            <button onclick="deleteUnit(${unit.id}, '${unit.name}')" class="p-1.5 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-all text-[13px]" title="Hapus">
                                <i class="fa-solid fa-trash-can"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `;
        }).join('');
    }

    // Modal helpers
    const modal = document.getElementById('unit-modal');
    const form = document.getElementById('unit-form');

    function openAddModal() {
        form.reset();
        document.getElementById('unit-id').value = '';
        document.getElementById('modal-title').textContent = 'Tambah Unit PlayStation';
        
        // Enable status selections for new units
        document.getElementById('unit-status').value = 'Tersedia';
        document.getElementById('unit-status').removeAttribute('disabled');

        modal.classList.remove('hidden');
    }

    function openEditModal(id) {
        const unit = allUnits.find(u => u.id === id);
        if (!unit) return;

        form.reset();
        document.getElementById('unit-id').value = unit.id;
        document.getElementById('modal-title').textContent = 'Edit Unit PlayStation';
        
        document.getElementById('unit-name').value = unit.name;
        document.getElementById('unit-type').value = unit.type;
        document.getElementById('unit-status').value = unit.status;
        document.getElementById('unit-description').value = unit.description || '';

        // If currently in active status, lock status options
        const statusSelect = document.getElementById('unit-status');
        if (['Bermain', 'Disewa'].includes(unit.status)) {
            // Keep enabled only current status, disable others
            Array.from(statusSelect.options).forEach(opt => {
                if (opt.value === unit.status) {
                    opt.removeAttribute('disabled');
                } else {
                    opt.setAttribute('disabled', 'true');
                }
            });
        } else {
            // Re-enable Tersedia and Maintenance, block active states
            Array.from(statusSelect.options).forEach(opt => {
                if (['Tersedia', 'Maintenance'].includes(opt.value)) {
                    opt.removeAttribute('disabled');
                } else {
                    opt.setAttribute('disabled', 'true');
                }
            });
        }

        modal.classList.remove('hidden');
    }

    function closeModal() {
        modal.classList.add('hidden');
        form.reset();
    }

    // Submit Form
    async function submitForm(e) {
        e.preventDefault();
        
        const id = document.getElementById('unit-id').value;
        const formData = new FormData(form);
        const data = {};
        formData.forEach((value, key) => data[key] = value);

        const url = id ? `/admin/api/units/${id}` : '/admin/api/units';
        const method = id ? 'PUT' : 'POST';

        try {
            const res = await ajaxRequest(url, method, data);
            if (res.success) {
                closeModal();
                showToast('success', res.message);
                loadUnits();
            }
        } catch (err) {
            Swal.fire('Validasi Gagal', err.data?.message || 'Gagal menyimpan unit PlayStation.', 'error');
        }
    }

    // Delete Unit
    async function deleteUnit(id, name) {
        Swal.fire({
            title: 'Hapus Unit?',
            text: `Apakah Anda yakin ingin menghapus unit "${name}"? Aksi ini tidak dapat dibatalkan.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e11d48',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal'
        }).then(async (result) => {
            if (result.isConfirmed) {
                try {
                    const res = await ajaxRequest(`/admin/api/units/${id}`, 'DELETE');
                    if (res.success) {
                        showToast('success', res.message);
                        loadUnits();
                    }
                } catch (err) {
                    Swal.fire('Gagal Menghapus', err.data?.message || 'Unit tidak dapat dihapus.', 'error');
                }
            }
        });
    }

    // Initial Load
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', loadUnits);
    } else {
        loadUnits();
    }
</script>
@endsection
