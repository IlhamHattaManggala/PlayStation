@extends('layouts.app')

@section('title', 'Kelola Produk & F&B')
@section('page_title', 'Kelola Produk & F&B')

@section('content')

<!-- Header Actions -->
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Inventaris F&B</p>
        <h3 class="text-lg font-bold text-slate-900">Daftar Makanan, Minuman & Jajanan</h3>
    </div>
    <button onclick="openAddModal()" class="py-2 px-4 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-xl text-xs flex items-center justify-center gap-1.5 shadow-md active:scale-[0.98] transition-all">
        <i class="fa-solid fa-plus"></i> Tambah Produk Baru
    </button>
</div>

<!-- Controls: Search & Category Filter -->
<div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
    <!-- Search Input -->
    <div class="relative sm:col-span-2">
        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
            <i class="fa-solid fa-magnifying-glass text-xs"></i>
        </span>
        <input type="text" id="search-input" oninput="applyFilters()" placeholder="Cari nama produk..." class="w-full pl-9 pr-4 py-2 rounded-xl border border-slate-200 bg-white placeholder:text-slate-400 focus:border-slate-900 focus:ring-1 focus:ring-slate-900 outline-none text-xs font-medium transition-all">
    </div>

    <!-- Category Filter -->
    <div>
        <select id="category-filter" onchange="applyFilters()" class="w-full px-3 pr-8 py-2 rounded-xl border border-slate-200 bg-white focus:border-slate-900 focus:ring-1 focus:ring-slate-900 outline-none text-xs font-semibold text-slate-700 transition-all">
            <option value="all">Semua Kategori</option>
            <option value="Makanan">Makanan</option>
            <option value="Minuman">Minuman</option>
            <option value="Jajanan">Jajanan</option>
        </select>
    </div>
</div>

<!-- Table Card -->
<div class="bg-white border border-slate-100 rounded-2xl overflow-hidden shadow-sm">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="border-b border-slate-100 bg-slate-50/50 text-[10px] font-bold uppercase tracking-wider text-slate-400">
                    <th class="px-5 py-3.5">Nama Produk</th>
                    <th class="px-5 py-3.5">Kategori</th>
                    <th class="px-5 py-3.5">Harga</th>
                    <th class="px-5 py-3.5">Stok</th>
                    <th class="px-5 py-3.5 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody id="products-table-body" class="divide-y divide-slate-100 text-sm font-medium text-slate-700">
                <!-- Loading State -->
                <tr>
                    <td colspan="5" class="text-center py-12 text-slate-400">
                        <i class="fa-solid fa-spinner animate-spin text-3xl mb-3 block mx-auto text-slate-900"></i>
                        Memuat data produk...
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- Add & Edit Modal -->
<div id="product-modal" class="fixed inset-0 bg-slate-950/50 flex items-center justify-center z-50 p-4 hidden">
    <div class="bg-white border border-slate-100 rounded-2xl w-full max-w-md shadow-2xl p-5 space-y-4 transition-all duration-300">
        
        <!-- Header -->
        <div class="flex items-center justify-between border-b border-slate-100 pb-2.5">
            <div>
                <h3 class="text-base font-bold text-slate-900" id="modal-title">Tambah Produk Baru</h3>
                <p class="text-[11px] text-slate-400 font-medium">Input informasi produk dengan lengkap.</p>
            </div>
            <button onclick="closeModal()" class="text-slate-400 hover:text-slate-600 p-1.5 hover:bg-slate-50 rounded-xl transition-all">
                <i class="fa-solid fa-xmark text-base"></i>
            </button>
        </div>

        <!-- Form -->
        <form id="product-form" onsubmit="submitProduct(event)" class="space-y-4">
            <input type="hidden" id="product-id">

            <div class="space-y-1">
                <label for="product-name" class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Nama Produk</label>
                <input type="text" id="product-name" required placeholder="Contoh: Indomie Rebus Special" class="w-full px-3 py-2 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:border-slate-900 focus:ring-1 focus:ring-slate-900 outline-none text-xs font-semibold transition-all">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div class="space-y-1">
                    <label for="product-category" class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Kategori</label>
                    <select id="product-category" required class="w-full px-3 py-2 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:border-slate-900 focus:ring-1 focus:ring-slate-900 outline-none text-xs font-semibold text-slate-700 transition-all">
                        <option value="Makanan">Makanan</option>
                        <option value="Minuman">Minuman</option>
                        <option value="Jajanan">Jajanan</option>
                    </select>
                </div>
                <div class="space-y-1">
                    <label for="product-price" class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Harga (Rp)</label>
                    <input type="number" id="product-price" min="0" required placeholder="8000" class="w-full px-3 py-2 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:border-slate-900 focus:ring-1 focus:ring-slate-900 outline-none text-xs font-semibold transition-all">
                </div>
            </div>

            <div class="space-y-1">
                <label for="product-stock" class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Stok Produk</label>
                <input type="number" id="product-stock" min="0" required placeholder="50" class="w-full px-3 py-2 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:border-slate-900 focus:ring-1 focus:ring-slate-900 outline-none text-xs font-semibold transition-all">
            </div>

            <!-- Footer Actions -->
            <div class="flex items-center justify-end gap-2.5 pt-1.5 border-t border-slate-100">
                <button type="button" onclick="closeModal()" class="py-2 px-4 border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-bold rounded-xl text-xs active:scale-[0.98] transition-all">
                    Batal
                </button>
                <button type="submit" class="py-2 px-5 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-xl text-xs shadow-md active:scale-[0.98] transition-all">
                    Simpan Produk
                </button>
            </div>
        </form>
    </div>
</div>

@endsection

@section('scripts')
<script>
    let productsList = [];

    // Custom onload using readyState check
    function initProductsPage() {
        loadProducts();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initProductsPage);
    } else {
        initProductsPage();
    }

    // Load Products via AJAX
    async function loadProducts() {
        try {
            const res = await ajaxRequest('/admin/api/products');
            if (res.success) {
                productsList = res.data;
                // Save products locally in cache for offline billing options
                localStorage.setItem('ps_cached_products', JSON.stringify(productsList));
                applyFilters();
            }
        } catch (err) {
            // Fallback to offline cached products
            const cachedProds = localStorage.getItem('ps_cached_products');
            if (cachedProds) {
                productsList = JSON.parse(cachedProds);
                applyFilters();
            } else {
                document.getElementById('products-table-body').innerHTML = `
                    <tr>
                        <td colspan="5" class="text-center py-8 text-rose-500 font-bold">
                            Gagal memuat data produk dan tidak ada cache lokal.
                        </td>
                    </tr>
                `;
            }
        }
    }

    // Render Table
    function renderTable(list) {
        const tbody = document.getElementById('products-table-body');
        if (list.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="5" class="text-center py-8 text-slate-400 font-medium">
                        Tidak ada produk ditemukan.
                    </td>
                </tr>
            `;
            return;
        }

        tbody.innerHTML = list.map(prod => {
            const catBadgeColor = prod.category === 'Makanan' ? 'bg-amber-50 text-amber-700 border-amber-100' :
                                 (prod.category === 'Minuman' ? 'bg-cyan-50 text-cyan-700 border-cyan-100' : 'bg-purple-50 text-purple-700 border-purple-100');
            
            const stockColor = prod.stock === 0 ? 'text-rose-600 font-extrabold' : (prod.stock < 10 ? 'text-amber-600' : 'text-slate-500');

            return `
                <tr class="hover:bg-slate-50/50 transition-colors duration-150 text-xs">
                    <td class="px-5 py-3 font-bold text-slate-900">${prod.name}</td>
                    <td class="px-5 py-3">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[9px] font-bold uppercase tracking-wider border ${catBadgeColor}">
                            ${prod.category}
                        </span>
                    </td>
                    <td class="px-5 py-3 font-bold text-slate-800">Rp ${prod.price.toLocaleString('id-ID')}</td>
                    <td class="px-5 py-3 font-semibold ${stockColor}">${prod.stock} pcs</td>
                    <td class="px-5 py-3 text-right">
                        <div class="flex items-center justify-end gap-1.5">
                            <button onclick="openEditModal(${prod.id}, '${prod.name.replace(/'/g, "\\'")}', '${prod.category}', ${prod.price}, ${prod.stock})" class="p-1 px-2 border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 hover:text-slate-900 font-bold rounded-lg text-[10px] transition-all active:scale-[0.96]" title="Edit">
                                <i class="fa-solid fa-pen-to-square"></i> Edit
                            </button>
                            <button onclick="deleteProduct(${prod.id}, '${prod.name.replace(/'/g, "\\'")}')" class="p-1 px-2 border border-red-100 bg-white hover:bg-red-50 text-red-500 hover:text-red-700 font-bold rounded-lg text-[10px] transition-all active:scale-[0.96]" title="Hapus">
                                <i class="fa-solid fa-trash"></i> Hapus
                            </button>
                        </div>
                    </td>
                </tr>
            `;
        }).join('');
    }

    // Filter local products list
    function applyFilters() {
        const query = document.getElementById('search-input').value.toLowerCase();
        const category = document.getElementById('category-filter').value;

        const filtered = productsList.filter(prod => {
            const matchesQuery = prod.name.toLowerCase().includes(query);
            const matchesCategory = category === 'all' || prod.category === category;
            return matchesQuery && matchesCategory;
        });

        renderTable(filtered);
    }

    // Modal Helpers
    const modal = document.getElementById('product-modal');
    
    function openAddModal() {
        document.getElementById('modal-title').textContent = "Tambah Produk Baru";
        document.getElementById('product-id').value = "";
        document.getElementById('product-form').reset();
        modal.classList.remove('hidden');
    }

    function openEditModal(id, name, category, price, stock) {
        document.getElementById('modal-title').textContent = "Edit Produk";
        document.getElementById('product-id').value = id;
        document.getElementById('product-name').value = name;
        document.getElementById('product-category').value = category;
        document.getElementById('product-price').value = price;
        document.getElementById('product-stock').value = stock;
        modal.classList.remove('hidden');
    }

    function closeModal() {
        modal.classList.add('hidden');
        document.getElementById('product-form').reset();
    }

    // Form Submission
    async function submitProduct(e) {
        e.preventDefault();
        
        const id = document.getElementById('product-id').value;
        const data = {
            name: document.getElementById('product-name').value,
            category: document.getElementById('product-category').value,
            price: parseFloat(document.getElementById('product-price').value) || 0,
            stock: parseInt(document.getElementById('product-stock').value) || 0,
        };

        const isEdit = id !== "";
        const url = isEdit ? `/admin/api/products/${id}` : '/admin/api/products';
        const method = isEdit ? 'PUT' : 'POST';

        try {
            const res = await ajaxRequest(url, method, data);
            if (res.success) {
                closeModal();
                showToast('success', res.message);
                loadProducts();
            }
        } catch (err) {
            Swal.fire('Validasi Gagal', err.data?.message || 'Gagal menyimpan produk.', 'error');
        }
    }

    // Delete Product
    function deleteProduct(id, name) {
        Swal.fire({
            title: 'Hapus Produk?',
            html: `Apakah Anda yakin ingin menghapus produk <b>${name}</b>?<br>Tindakan ini tidak dapat dibatalkan.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal'
        }).then(async (result) => {
            if (result.isConfirmed) {
                try {
                    const res = await ajaxRequest(`/admin/api/products/${id}`, 'DELETE');
                    if (res.success) {
                        showToast('success', res.message);
                        loadProducts();
                    }
                } catch (err) {
                    Swal.fire('Gagal Menghapus', err.data?.message || 'Produk tidak dapat dihapus.', 'error');
                }
            }
        });
    }
</script>
@endsection
