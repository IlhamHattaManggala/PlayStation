@extends('layouts.app')

@section('title', 'Kelola Tarif Layanan')
@section('page_title', 'Kelola Tarif Layanan')

@section('content')

<!-- Header Action Card -->
<div class="bg-white border border-slate-100 rounded-2xl p-4 flex flex-col md:flex-row md:items-center justify-between gap-3 shadow-sm">
    <div>
        <h3 class="text-base font-bold text-slate-900">Daftar Tarif Layanan</h3>
        <p class="text-[11px] text-slate-500 font-medium">Atur tarif rental PlayStation per jam (di tempat) atau per hari (sewa bawa pulang).</p>
    </div>
    <button onclick="openAddModal()" class="py-2 px-3 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-xl text-xs flex items-center gap-2 shadow-sm transition-all duration-200 active:scale-[0.98]">
        <i class="fa-solid fa-plus text-[11px]"></i> Tambah Tarif Baru
    </button>
</div>

<!-- Table Card -->
<div class="bg-white border border-slate-100 rounded-2xl overflow-hidden shadow-sm">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="border-b border-slate-100 bg-slate-50/50 text-[10px] font-bold uppercase tracking-wider text-slate-400">
                    <th class="px-4 py-2.5">Jenis Layanan</th>
                    <th class="px-4 py-2.5">Tipe PS</th>
                    <th class="px-4 py-2.5">Tarif / Harga</th>
                    <th class="px-4 py-2.5">Keterangan</th>
                    <th class="px-4 py-2.5 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody id="rates-table-body" class="divide-y divide-slate-100 text-sm font-medium text-slate-700">
                <!-- Data populated dynamically via JS -->
                <tr>
                    <td colspan="5" class="text-center py-8 text-slate-400">
                        <i class="fa-solid fa-spinner animate-spin text-2xl mb-2 block mx-auto"></i>
                        Memuat data tarif...
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Add/Edit Rate -->
<div id="rate-modal" class="fixed inset-0 bg-slate-950/50 flex items-center justify-center z-50 p-4 hidden">
    <div class="bg-white border border-slate-100 rounded-2xl w-full max-w-md shadow-2xl p-6 space-y-5 transition-all duration-300">
        
        <!-- Header -->
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <div>
                <h3 class="text-lg font-bold text-slate-900" id="modal-title">Tambah Tarif Layanan</h3>
                <p class="text-xs text-slate-400 font-medium">Lengkapi rincian tarif layanan baru.</p>
            </div>
            <button onclick="closeModal()" class="text-slate-400 hover:text-slate-600 p-2 hover:bg-slate-50 rounded-xl transition-all">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <!-- Form -->
        <form id="rate-form" onsubmit="submitForm(event)" class="space-y-4">
            <input type="hidden" name="id" id="rate-id">
            
            <div class="space-y-1.5">
                <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">Jenis Layanan</label>
                <select name="service_type" id="rate-service-type" required class="w-full px-3 py-2.5 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:border-slate-900 focus:ring-1 focus:ring-slate-900 outline-none text-sm font-semibold transition-all">
                    <option value="Di Tempat">Di Tempat (Per Jam)</option>
                    <option value="Sewa PS">Sewa PS (Bawa Pulang Per Hari)</option>
                    <option value="Sewa Setengah Hari">Sewa Setengah Hari (Bawa Pulang)</option>
                </select>
            </div>

            <div class="space-y-1.5">
                <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">Tipe PlayStation</label>
                <select name="playstation_type" id="rate-playstation-type" required class="w-full px-3 py-2.5 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:border-slate-900 focus:ring-1 focus:ring-slate-900 outline-none text-sm font-semibold transition-all">
                    <option value="PS3">PS3</option>
                    <option value="PS4">PS4</option>
                    <option value="PS5">PS5</option>
                </select>
            </div>

            <div class="space-y-1.5">
                <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">Harga (Rupiah)</label>
                <div class="relative flex items-center">
                    <span class="absolute left-4 text-sm font-bold text-slate-400">Rp</span>
                    <input type="number" name="price" id="rate-price" required min="0" step="500" placeholder="Contoh: 10000" class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 placeholder:text-slate-400 focus:bg-white focus:border-slate-900 focus:ring-1 focus:ring-slate-900 outline-none text-sm font-semibold transition-all">
                </div>
            </div>

            <div class="space-y-1.5">
                <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">Deskripsi Singkat</label>
                <textarea name="description" id="rate-description" rows="2" placeholder="Keterangan mengenai paket tarif..." class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 placeholder:text-slate-400 focus:bg-white focus:border-slate-900 focus:ring-1 focus:ring-slate-900 outline-none text-sm font-medium transition-all resize-none"></textarea>
            </div>

            <!-- Buttons -->
            <div class="flex items-center justify-end gap-3 pt-2">
                <button type="button" onclick="closeModal()" class="py-2.5 px-4 border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-bold rounded-xl text-xs active:scale-[0.98] transition-all">
                    Batal
                </button>
                <button type="submit" class="py-2.5 px-5 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-xl text-xs shadow-md active:scale-[0.98] transition-all">
                    Simpan Tarif
                </button>
            </div>
        </form>

    </div>
</div>

@endsection

@section('scripts')
<script>
    let allRates = [];

    // Load Data
    async function loadRates() {
        try {
            const res = await ajaxRequest('/admin/api/rates');
            if (res.success) {
                allRates = res.data;
                renderTable(allRates);
            }
        } catch (err) {
            showToast('error', 'Gagal memuat data tarif.');
        }
    }

    // Render Table
    function renderTable(rates) {
        const tbody = document.getElementById('rates-table-body');
        if (rates.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="5" class="text-center py-8 text-slate-400 font-medium">
                        Belum ada tarif layanan yang terdaftar.
                    </td>
                </tr>
            `;
            return;
        }

        tbody.innerHTML = rates.map(rate => {
            const serviceBadge = rate.service_type === 'Di Tempat' ? 
                                 'bg-amber-50 text-amber-700 border-amber-100' : 
                                 rate.service_type === 'Sewa PS' ?
                                 'bg-blue-50 text-blue-700 border-blue-100' :
                                 'bg-indigo-50 text-indigo-700 border-indigo-100';
            
            const serviceIcon = rate.service_type === 'Di Tempat' ? 
                                 '<i class="fa-solid fa-tv mr-1.5"></i>' : 
                                 rate.service_type === 'Sewa PS' ?
                                 '<i class="fa-solid fa-truck-ramp-box mr-1.5"></i>' :
                                 '<i class="fa-solid fa-business-time mr-1.5"></i>';

            const typeBadge = rate.playstation_type === 'PS5' ? 'bg-indigo-50 text-indigo-700 border-indigo-100' :
                              rate.playstation_type === 'PS4' ? 'bg-violet-50 text-violet-700 border-violet-100' :
                              'bg-slate-50 text-slate-600 border-slate-200';

            const priceFormatted = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(rate.price);
            const priceSuffix = rate.service_type === 'Di Tempat' ? ' / jam' : 
                                rate.service_type === 'Sewa PS' ? ' / hari' : ' / setengah hari';

            return `
                <tr class="hover:bg-slate-50/50 transition-colors duration-150">
                    <td class="px-4 py-2.5">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold border ${serviceBadge}">
                            ${serviceIcon} ${rate.service_type}
                        </span>
                    </td>
                    <td class="px-4 py-2.5">
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold border ${typeBadge}">${rate.playstation_type}</span>
                    </td>
                    <td class="px-4 py-2.5 font-extrabold text-slate-800 text-xs">${priceFormatted}<span class="text-slate-400 font-medium text-[10px]">${priceSuffix}</span></td>
                    <td class="px-4 py-2.5 text-slate-500 font-normal max-w-xs truncate text-xs">${rate.description || '-'}</td>
                    <td class="px-4 py-2.5 text-right">
                        <div class="flex items-center justify-end gap-1.5">
                            <button onclick="openEditModal(${rate.id})" class="p-1.5 text-slate-400 hover:text-slate-800 hover:bg-slate-100 rounded-lg transition-all text-[13px]" title="Edit">
                                <i class="fa-solid fa-pen"></i>
                            </button>
                            <button onclick="deleteRate(${rate.id}, '${rate.service_type} - ${rate.playstation_type}')" class="p-1.5 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-all text-[13px]" title="Hapus">
                                <i class="fa-solid fa-trash-can"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `;
        }).join('');
    }

    // Modal helpers
    const modal = document.getElementById('rate-modal');
    const form = document.getElementById('rate-form');

    function openAddModal() {
        form.reset();
        document.getElementById('rate-id').value = '';
        document.getElementById('modal-title').textContent = 'Tambah Tarif Layanan';
        
        // Enable choices
        document.getElementById('rate-service-type').removeAttribute('disabled');
        document.getElementById('rate-playstation-type').removeAttribute('disabled');

        modal.classList.remove('hidden');
    }

    function openEditModal(id) {
        const rate = allRates.find(r => r.id === id);
        if (!rate) return;

        form.reset();
        document.getElementById('rate-id').value = rate.id;
        document.getElementById('modal-title').textContent = 'Edit Tarif Layanan';
        
        document.getElementById('rate-service-type').value = rate.service_type;
        document.getElementById('rate-playstation-type').value = rate.playstation_type;
        document.getElementById('rate-price').value = Math.round(rate.price);
        document.getElementById('rate-description').value = rate.description || '';

        // Block changing types during edit to avoid uniqueness mismatch issues, keep interface simple
        // Instead they can just edit the price or delete and re-create.
        // But let's allow editing, validation will handle it. We can keep them enabled.
        modal.classList.remove('hidden');
    }

    function closeModal() {
        modal.classList.add('hidden');
        form.reset();
    }

    // Submit Form
    async function submitForm(e) {
        e.preventDefault();
        
        const id = document.getElementById('rate-id').value;
        const formData = new FormData(form);
        const data = {};
        formData.forEach((value, key) => data[key] = value);

        const url = id ? `/admin/api/rates/${id}` : '/admin/api/rates';
        const method = id ? 'PUT' : 'POST';

        try {
            const res = await ajaxRequest(url, method, data);
            if (res.success) {
                closeModal();
                showToast('success', res.message);
                loadRates();
            }
        } catch (err) {
            Swal.fire('Validasi Gagal', err.data?.message || 'Gagal menyimpan tarif layanan.', 'error');
        }
    }

    // Delete Rate
    async function deleteRate(id, identifier) {
        Swal.fire({
            title: 'Hapus Tarif?',
            text: `Apakah Anda yakin ingin menghapus paket tarif "${identifier}"?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e11d48',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal'
        }).then(async (result) => {
            if (result.isConfirmed) {
                try {
                    const res = await ajaxRequest(`/admin/api/rates/${id}`, 'DELETE');
                    if (res.success) {
                        showToast('success', res.message);
                        loadRates();
                    }
                } catch (err) {
                    Swal.fire('Gagal Menghapus', err.data?.message || 'Tarif tidak dapat dihapus.', 'error');
                }
            }
        });
    }

    // Initial Load
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', loadRates);
    } else {
        loadRates();
    }
</script>
@endsection
