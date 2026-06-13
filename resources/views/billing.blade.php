<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Billing Board - {{ \App\Models\AppSetting::first()->app_name ?? 'Rental PlayStation' }}</title>
    
    @php
        $settings = \App\Models\AppSetting::first();
    @endphp

    @if($settings && $settings->favicon)
        <link rel="icon" type="image/*" href="{{ $settings->favicon_url }}">
    @else
        <link rel="icon" type="image/png" href="https://cdn-icons-png.flaticon.com/512/869/869045.png">
    @endif

    <!-- Google Fonts & Tailwind -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- SweetAlert2 CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- FontAwesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Dexie.js IndexedDB Wrapper -->
    <script src="https://cdn.jsdelivr.net/npm/dexie@3/dist/dexie.min.js"></script>

    <!-- PWA manifest & theme -->
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#0f172a">

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        /* Compact SweetAlert2 Sizing (Excluding Toasts) */
        .swal2-popup:not(.swal2-toast) {
            width: 22rem !important;
            padding: 1.25rem !important;
            border-radius: 1.25rem !important;
        }
        .swal2-popup:not(.swal2-toast) .swal2-title {
            font-size: 1.1rem !important;
            font-weight: 800 !important;
            color: #0f172a !important;
        }
        .swal2-popup:not(.swal2-toast) .swal2-html-container {
            font-size: 0.75rem !important;
            margin: 0.5rem 0 0 0 !important;
            color: #475569 !important;
            font-weight: 500 !important;
            line-height: 1.4 !important;
        }
        .swal2-popup:not(.swal2-toast) .swal2-icon {
            transform: scale(0.65) !important;
            transform-origin: center !important;
            margin: -0.5rem auto 0.25rem auto !important;
        }
        .swal2-popup:not(.swal2-toast) .swal2-actions {
            margin: 1rem auto 0 auto !important;
            gap: 0.5rem !important;
        }
        .swal2-popup:not(.swal2-toast) .swal2-styled {
            margin: 0 !important;
            padding: 0.5rem 1.25rem !important;
            font-size: 0.75rem !important;
            font-weight: 700 !important;
            border-radius: 0.75rem !important;
        }
    </style>
</head>
<body class="bg-slate-50 text-slate-900 min-h-screen flex flex-col selection:bg-slate-950 selection:text-white">

    <!-- Standalone Billing Board Header -->
    <header class="py-4 px-6 md:px-8 bg-white border-b border-slate-100 flex items-center justify-between sticky top-0 z-10 shadow-sm">
        <a href="{{ route('dashboard') }}" class="py-2 px-3.5 bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold rounded-xl text-xs flex items-center gap-1.5 transition-all duration-200 active:scale-[0.98]">
            <i class="fa-solid fa-arrow-left"></i> Kembali
        </a>
        
        <!-- Center/Right clock and Auto-update status -->
        <div class="flex items-center gap-3">
            <!-- Offline/Online status badge -->
            <span id="connection-badge" class="text-[10px] sm:text-xs font-bold px-2 py-1 sm:px-3 sm:py-1.5 rounded-xl flex items-center gap-1.5 border border-slate-100 transition-colors duration-300">
                <span id="connection-dot" class="w-1.5 h-1.5 sm:w-2 sm:h-2 rounded-full"></span>
                <span id="connection-text">Checking...</span>
            </span>

            <div class="text-[10px] font-bold text-slate-400 flex items-center gap-1.5 bg-slate-50 border border-slate-100 px-2.5 py-1 rounded-full">
                <span class="w-1.5 h-1.5 bg-red-500 rounded-full animate-ping"></span>
                Auto Update
            </div>
            <div class="text-sm font-black text-white bg-slate-900 px-3.5 py-1.5 rounded-xl font-mono tracking-wider shadow-sm" id="running-clock">
                00:00:00
            </div>
        </div>
    </header>

    <!-- Main Fullscreen Body -->
    <main class="flex-1 p-6 md:p-8 max-w-7xl w-full mx-auto space-y-6">

        <!-- PlayStation Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-6 gap-3" id="units-grid">
            @foreach($units as $unit)
            <div class="bg-white border border-slate-100 rounded-2xl flex flex-col justify-between overflow-hidden shadow-sm transition-all duration-200 hover:border-slate-200 hover:shadow-md" id="unit-card-{{ $unit->id }}">
                
                <!-- Header -->
                <div class="p-3 border-b border-slate-50 space-y-2">
                    <div class="flex items-start justify-between">
                        <div class="space-y-0.5">
                            <h4 class="font-bold text-slate-800 text-sm leading-tight">{{ $unit->name }}</h4>
                            <span class="inline-flex items-center text-[9px] font-bold px-1.5 py-0.5 rounded-full uppercase tracking-wider
                                {{ $unit->type === 'PS5' ? 'bg-indigo-50 text-indigo-700 border border-indigo-100' : ($unit->type === 'PS4' ? 'bg-violet-50 text-violet-700 border border-violet-100' : 'bg-slate-100 text-slate-700 border border-slate-200') }}">
                                {{ $unit->type }}
                            </span>
                        </div>

                        <!-- Status Badge -->
                        <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded-full text-[9px] font-bold uppercase tracking-wider status-badge-{{ $unit->id }}
                            {{ $unit->status === 'Tersedia' ? 'bg-emerald-50 text-emerald-700 border border-emerald-100' : 
                               ($unit->status === 'Bermain' ? 'bg-amber-50 text-amber-700 border border-amber-100 animate-pulse' : 
                               ($unit->status === 'Disewa' ? 'bg-blue-50 text-blue-700 border border-blue-100' : 'bg-rose-50 text-rose-700 border border-rose-100')) }}">
                            <span class="w-1.5 h-1.5 rounded-full 
                                {{ $unit->status === 'Tersedia' ? 'bg-emerald-500' : 
                                   ($unit->status === 'Bermain' ? 'bg-amber-500' : 
                                   ($unit->status === 'Disewa' ? 'bg-blue-500' : 'bg-rose-500')) }}"></span>
                            {{ $unit->status }}
                        </span>
                    </div>

                    <!-- Unit Info & Details -->
                    <div class="space-y-2 pt-0.5" id="unit-info-body-{{ $unit->id }}">
                        @if($unit->status === 'Tersedia')
                            <p class="text-[11px] text-slate-500 font-medium">Ready to play. Hubungkan stik dan silakan pilih paket.</p>
                        @elseif($unit->status === 'Bermain')
                            @php
                                $activeTrx = $unit->onsitePlayTransactions->first();
                            @endphp
                            @if($activeTrx)
                                <div class="bg-amber-50/40 border border-amber-100/50 rounded-xl p-2.5 space-y-1 text-slate-700 timer-container" 
                                     data-unit-id="{{ $unit->id }}" 
                                     data-start-time="{{ $activeTrx->started_at->toIso8601String() }}" 
                                     data-hourly-rate="{{ $activeTrx->hourly_rate }}">
                                    <div class="flex justify-between items-center text-[9px]">
                                        <span class="font-semibold text-slate-400 uppercase tracking-wider">Mulai Main</span>
                                        <span class="font-bold text-slate-700">{{ $activeTrx->started_at->format('H:i') }}</span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-[9px] font-semibold text-slate-400 uppercase tracking-wider">Waktu Berjalan</span>
                                        <span class="font-extrabold text-slate-700 text-xs tracking-wide active-live-timer">00:00:00</span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-[9px] font-semibold text-slate-400 uppercase tracking-wider">Durasi</span>
                                        <span class="font-bold text-amber-700 text-xs active-duration">-</span>
                                    </div>
                                    <div class="flex justify-between items-center pt-1 border-t border-amber-100/30">
                                        <span class="text-[9px] font-semibold text-slate-400 uppercase tracking-wider">Est. Biaya PS</span>
                                        <span class="font-black text-slate-800 text-xs active-cost">Rp 0</span>
                                    </div>
                                </div>

                                @if($activeTrx->orders->count() > 0)
                                <div class="bg-slate-50 border border-slate-100 rounded-xl p-2.5 space-y-1 text-slate-700 mt-2">
                                    <div class="text-[9px] font-bold text-slate-400 uppercase tracking-wider border-b border-slate-100 pb-1 flex justify-between">
                                        <span>Pesanan F&B</span>
                                        <span class="text-slate-500">Rp {{ number_format($activeTrx->orders->sum('total_price'), 0, ',', '.') }}</span>
                                    </div>
                                    <div class="max-h-20 overflow-y-auto space-y-1 divide-y divide-slate-100/50 pr-1">
                                        @foreach($activeTrx->orders as $order)
                                            <div class="flex justify-between items-center text-[10px] pt-1">
                                                <span class="font-medium text-slate-600 truncate max-w-[90px]">{{ $order->quantity }}x {{ $order->product->name ?? 'Produk Terhapus' }}</span>
                                                <span class="font-bold text-slate-700">Rp {{ number_format($order->total_price, 0, ',', '.') }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                                @endif
                            @endif
                        @elseif($unit->status === 'Disewa')
                            @php
                                $activeRental = $unit->rentalTransactions->first();
                            @endphp
                            @if($activeRental)
                                <div class="bg-blue-50/40 border border-blue-100/50 rounded-xl p-2.5 space-y-1 text-slate-700">
                                    <div class="flex justify-between items-center text-[9px]">
                                        <span class="font-semibold text-slate-400 uppercase tracking-wider">Penyewa</span>
                                        <span class="font-bold text-slate-800 truncate max-w-[80px]">{{ $activeRental->renter_name }}</span>
                                    </div>
                                    <div class="flex justify-between items-center text-[9px]">
                                        <span class="font-semibold text-slate-400 uppercase tracking-wider">No HP</span>
                                        <span class="font-bold text-slate-700 text-[10px]">{{ $activeRental->phone }}</span>
                                    </div>
                                    <div class="flex justify-between items-center text-[9px]">
                                        <span class="font-semibold text-slate-400 uppercase tracking-wider">Tgl Kembali</span>
                                        <span class="font-bold text-blue-700 text-[10px]">{{ $activeRental->rental_end_date->format('d M Y') }}</span>
                                    </div>
                                    <div class="flex justify-between items-center pt-1 border-t border-blue-100/30">
                                        <span class="text-[9px] font-semibold text-slate-400 uppercase tracking-wider">Biaya</span>
                                        <span class="font-bold text-slate-800 text-xs">Rp {{ number_format($activeRental->total_price, 0, ',', '.') }}</span>
                                    </div>
                                </div>
                            @endif
                        @else
                            <p class="text-[11px] text-slate-500 font-medium italic bg-rose-50/30 border border-rose-100/40 p-2 rounded-xl">
                                {{ $unit->description ?? 'Unit sedang dalam pemeliharaan berkala.' }}
                            </p>
                        @endif
                    </div>
                </div>

                <!-- Card Action Footer -->
                <div class="p-3 bg-slate-50/50 border-t border-slate-50 flex items-center justify-end gap-1.5" id="unit-actions-{{ $unit->id }}">
                    @if($unit->status === 'Tersedia')
                        <button onclick="startPlay({{ $unit->id }})" class="flex-1 py-1.5 px-2 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-xl text-[10px] flex items-center justify-center gap-1 shadow-sm active:scale-[0.98] transition-all duration-200">
                            <i class="fa-solid fa-play"></i> Mulai Main
                        </button>
                        <button onclick="openRentalModal({{ $unit->id }}, '{{ $unit->name }}', '{{ $unit->type }}')" class="py-1.5 px-2 border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-bold rounded-xl text-[10px] flex items-center justify-center gap-1 active:scale-[0.98] transition-all duration-200">
                            <i class="fa-solid fa-hand-holding-hand"></i> Sewa
                        </button>
                    @elseif($unit->status === 'Bermain')
                        <button onclick="openOrderModal({{ $unit->id }}, '{{ $unit->name }}')" class="py-1.5 px-2 border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-bold rounded-xl text-[10px] flex items-center justify-center gap-1 active:scale-[0.98] transition-all duration-200">
                            <i class="fa-solid fa-bowl-food"></i> + Order
                        </button>
                        <button onclick="endPlay({{ $unit->id }})" class="flex-1 py-1.5 px-2.5 bg-amber-600 hover:bg-amber-700 text-white font-black rounded-xl text-[10px] flex items-center justify-center gap-1 shadow-sm active:scale-[0.98] transition-all duration-200">
                            <i class="fa-solid fa-circle-stop"></i> Selesai
                        </button>
                    @elseif($unit->status === 'Disewa')
                        <button onclick="returnRental({{ $unit->id }})" class="flex-1 py-1.5 px-2.5 bg-blue-600 hover:bg-blue-700 text-white font-black rounded-xl text-[10px] flex items-center justify-center gap-1 shadow-sm active:scale-[0.98] transition-all duration-200">
                            <i class="fa-solid fa-rotate-left"></i> Dikembalikan
                        </button>
                    @else
                        <span class="text-[10px] text-slate-400 font-bold italic py-1">Tidak ada aksi</span>
                    @endif
                </div>

            </div>
            @endforeach
        </div>
    </main>

    <!-- Modal Sewa PS -->
    <div id="rental-modal" class="fixed inset-0 bg-slate-950/50 flex items-center justify-center z-50 p-4 hidden">
        <div class="bg-white border border-slate-100 rounded-2xl w-full max-w-md shadow-2xl p-5 space-y-4 transition-all duration-300">
            
            <!-- Header -->
            <div class="flex items-center justify-between border-b border-slate-100 pb-2.5">
                <div>
                    <h3 class="text-base font-bold text-slate-900">Sewa PlayStation (Bawa Pulang)</h3>
                    <p class="text-[11px] text-slate-400 font-medium">Daftarkan transaksi sewa baru.</p>
                </div>
                <button onclick="closeRentalModal()" class="text-slate-400 hover:text-slate-600 p-1.5 hover:bg-slate-50 rounded-xl transition-all">
                    <i class="fa-solid fa-xmark text-base"></i>
                </button>
            </div>

            <!-- Form -->
            <form id="rental-form" onsubmit="submitRental(event)" class="space-y-3">
                <input type="hidden" name="playstation_unit_id" id="rental-unit-id">
                
                <div class="grid grid-cols-2 gap-3">
                    <div class="space-y-1">
                        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Unit Terpilih</label>
                        <input type="text" id="rental-unit-name" readonly class="w-full px-3 py-2 rounded-xl border border-slate-200 bg-slate-50 font-bold text-xs text-slate-700 outline-none">
                    </div>
                    <div class="space-y-1">
                        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Jenis PS</label>
                        <input type="text" id="rental-unit-type" readonly class="w-full px-3 py-2 rounded-xl border border-slate-200 bg-slate-50 font-bold text-xs text-slate-700 outline-none">
                    </div>
                </div>

                <div class="space-y-1">
                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Nama Penyewa</label>
                    <input type="text" name="renter_name" required placeholder="Contoh: Budi Santoso" class="w-full px-3 py-2 rounded-xl border border-slate-200 bg-slate-50 placeholder:text-slate-400 focus:bg-white focus:border-slate-900 focus:ring-1 focus:ring-slate-900 outline-none text-xs font-semibold transition-all">
                </div>

                <div class="space-y-1">
                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Nomor HP</label>
                    <input type="text" name="phone" required placeholder="Contoh: 0812XXXXXXXX" class="w-full px-3 py-2 rounded-xl border border-slate-200 bg-slate-50 placeholder:text-slate-400 focus:bg-white focus:border-slate-900 focus:ring-1 focus:ring-slate-900 outline-none text-xs font-semibold transition-all">
                </div>

                <div class="space-y-1">
                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Jaminan (KTP / Kartu Pelajar)</label>
                    <input type="file" name="identity_card" accept="image/*" required class="w-full px-3 py-1.5 rounded-xl border border-slate-200 bg-slate-50 text-slate-500 file:mr-3 file:py-1 file:px-2 file:rounded-lg file:border-0 file:text-[10px] file:font-semibold file:bg-slate-900 file:text-white hover:file:bg-slate-800 text-[10px] transition-all">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div class="space-y-1">
                        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Tanggal Mulai</label>
                        <input type="date" name="rental_start_date" id="rental-start-date" required onchange="calculateRentalCost()" class="w-full px-3 py-2 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:border-slate-900 focus:ring-1 focus:ring-slate-900 outline-none text-xs font-semibold transition-all">
                    </div>
                    <div class="space-y-1">
                        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Durasi</label>
                        <select name="rental_days" id="rental-days" required onchange="calculateRentalCost()" class="w-full px-3 py-2 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:border-slate-900 focus:ring-1 focus:ring-slate-900 outline-none text-xs font-semibold text-slate-700 transition-all">
                            <option value="0.5">0.5 Hari (Setengah Hari)</option>
                            <option value="1.0" selected>1 Hari</option>
                            <option value="1.5">1.5 Hari</option>
                            <option value="2.0">2 Hari</option>
                            <option value="2.5">2.5 Hari</option>
                            <option value="3.0">3 Hari</option>
                            <option value="4.0">4 Hari</option>
                            <option value="5.0">5 Hari</option>
                            <option value="6.0">6 Hari</option>
                            <option value="7.0">7 Hari (1 Minggu)</option>
                            <option value="10.0">10 Hari</option>
                            <option value="14.0">14 Hari (2 Minggu)</option>
                            <option value="30.0">30 Hari (1 Bulan)</option>
                        </select>
                    </div>
                </div>

                <!-- Include TV Checkbox -->
                <div class="flex items-center gap-2.5 p-2.5 bg-slate-50/50 rounded-xl border border-slate-100 flex-row">
                    <input type="checkbox" name="include_tv" id="rental-include-tv" onchange="calculateRentalCost()" class="w-4 h-4 text-slate-900 border-slate-300 rounded focus:ring-slate-900 focus:ring-1 outline-none transition-all cursor-pointer">
                    <label for="rental-include-tv" class="text-xs font-bold text-slate-600 select-none cursor-pointer">
                        Termasuk Sewa TV (Gratis / Tanpa biaya tambahan)
                    </label>
                </div>

                <!-- Dynamic Cost Summary -->
                <div class="bg-slate-50 rounded-xl p-3 border border-slate-100 flex items-center justify-between">
                    <div>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Durasi & Tarif</p>
                        <h4 class="text-xs font-bold text-slate-800" id="rental-duration-display">0 hari</h4>
                    </div>
                    <div class="text-right">
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Total Pembayaran</p>
                        <h3 class="text-base font-black text-indigo-600" id="rental-cost-display">Rp 0</h3>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="flex items-center justify-end gap-2.5 pt-1.5">
                    <button type="button" onclick="closeRentalModal()" class="py-2 px-3.5 border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-bold rounded-xl text-xs active:scale-[0.98] transition-all">
                        Batal
                    </button>
                    <button type="submit" class="py-2 px-4.5 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-xl text-xs shadow-md active:scale-[0.98] transition-all">
                        Konfirmasi Sewa
                    </button>
                </div>
            </form>

        </div>
    </div>

    <!-- Modal Tambah Order F&B -->
    <div id="order-modal" class="fixed inset-0 bg-slate-950/50 flex items-center justify-center z-50 p-4 hidden">
        <div class="bg-white border border-slate-100 rounded-2xl w-full max-w-md shadow-2xl p-5 space-y-4 transition-all duration-300">
            
            <!-- Header -->
            <div class="flex items-center justify-between border-b border-slate-100 pb-2.5">
                <div>
                    <h3 class="text-base font-bold text-slate-900">Tambah Pesanan F&B</h3>
                    <p class="text-[11px] text-slate-400 font-medium" id="order-unit-subtitle">Unit PlayStation</p>
                </div>
                <button onclick="closeOrderModal()" class="text-slate-400 hover:text-slate-600 p-1.5 hover:bg-slate-50 rounded-xl transition-all">
                    <i class="fa-solid fa-xmark text-base"></i>
                </button>
            </div>

            <!-- Form -->
            <form id="order-form" onsubmit="submitOrder(event)" class="space-y-3">
                <input type="hidden" name="playstation_unit_id" id="order-unit-id">
                
                <div class="space-y-1">
                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Pilih Produk</label>
                    <select name="product_id" id="order-product-id" required onchange="calculateOrderCost()" class="w-full px-3 py-2 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:border-slate-900 focus:ring-1 focus:ring-slate-900 outline-none text-xs font-semibold text-slate-700 transition-all">
                        <!-- Loaded dynamically -->
                    </select>
                </div>

                <div class="space-y-1">
                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Jumlah / Quantity</label>
                    <div class="flex items-center gap-2">
                        <button type="button" onclick="adjustQty(-1)" class="w-8 h-8 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-black text-xs flex items-center justify-center transition-all">-</button>
                        <input type="number" name="quantity" id="order-qty" value="1" min="1" required oninput="calculateOrderCost()" class="w-20 text-center py-1.5 rounded-xl border border-slate-200 bg-slate-50 font-bold text-xs text-slate-700 outline-none">
                        <button type="button" onclick="adjustQty(1)" class="w-8 h-8 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-black text-xs flex items-center justify-center transition-all">+</button>
                    </div>
                </div>

                <!-- Dynamic Cost Summary -->
                <div class="bg-slate-50 rounded-xl p-3 border border-slate-100 flex items-center justify-between">
                    <div>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Harga Satuan</p>
                        <h4 class="text-xs font-bold text-slate-800" id="order-unit-price-display">Rp 0</h4>
                    </div>
                    <div class="text-right">
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Subtotal</p>
                        <h3 class="text-base font-black text-emerald-600" id="order-subtotal-display">Rp 0</h3>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="flex items-center justify-end gap-2.5 pt-1.5">
                    <button type="button" onclick="closeOrderModal()" class="py-2 px-3.5 border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-bold rounded-xl text-xs active:scale-[0.98] transition-all">
                        Batal
                    </button>
                    <button type="submit" class="py-2 px-4.5 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-xl text-xs shadow-md active:scale-[0.98] transition-all">
                        Tambah Pesanan
                    </button>
                </div>
            </form>

        </div>
    </div>

    <!-- AJAX Core Helpers -->
    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        async function ajaxRequest(url, method = 'GET', data = null, isMultipart = false) {
            const options = {
                method: method,
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                }
            };

            if (data) {
                if (isMultipart) {
                    options.body = data;
                } else {
                    options.headers['Content-Type'] = 'application/json';
                    options.body = JSON.stringify(data);
                }
            }

            try {
                const response = await fetch(url, options);
                const result = await response.json();
                
                if (!response.ok) {
                    throw { status: response.status, data: result };
                }
                
                return result;
            } catch (error) {
                console.error("AJAX Error:", error);
                throw error;
            }
        }

        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer)
                toast.addEventListener('mouseleave', Swal.resumeTimer)
            }
        });

        function showToast(icon, message) {
            Toast.fire({
                icon: icon,
                title: message
            });
        }
    </script>

    <!-- Page Specific Script -->
    <script>
        // Rates data for dynamic cost preview in rental modal
        const dailyRates = {};
        const halfDayRates = {};
        const hourlyRates = {};
        @php
            $ratesList = \App\Models\Rate::where('service_type', 'Sewa PS')->get();
            foreach($ratesList as $r) {
                echo "dailyRates['{$r->playstation_type}'] = {$r->price};\n";
            }
            $halfDayList = \App\Models\Rate::where('service_type', 'Sewa Setengah Hari')->get();
            foreach($halfDayList as $r) {
                echo "halfDayRates['{$r->playstation_type}'] = {$r->price};\n";
            }
            $hourlyList = \App\Models\Rate::where('service_type', 'Di Tempat')->get();
            foreach($hourlyList as $r) {
                echo "hourlyRates['{$r->playstation_type}'] = {$r->price};\n";
            }
        @endphp

        // --- Onsite Play Billing Timer ---
        function updateTimers() {
            const containers = document.querySelectorAll('.timer-container');
            containers.forEach(container => {
                const unitId = container.getAttribute('data-unit-id');
                const startTimeStr = container.getAttribute('data-start-time');
                const hourlyRate = parseFloat(container.getAttribute('data-hourly-rate'));

                const start = new Date(startTimeStr);
                const now = new Date();
                const diffMs = now - start;

                if (diffMs < 0) return; // safeguard

                // 1. Perhitungan Live Timer (Jam:Menit:Detik) Berjalan
                const diffSecTotal = Math.max(0, Math.floor(diffMs / 1000));
                const liveHrs = Math.floor(diffSecTotal / 3600);
                const liveMins = Math.floor((diffSecTotal % 3600) / 60);
                const liveSecs = diffSecTotal % 60;

                const pad = (n) => String(n).padStart(2, '0');
                const liveTimerText = `${pad(liveHrs)}:${pad(liveMins)}:${pad(liveSecs)}`;

                // 2. Perhitungan Durasi untuk Billing (Menit bulat)
                const diffMin = Math.max(1, Math.floor(diffMs / 60000));
                const hrs = Math.floor(diffMin / 60);
                const mins = diffMin % 60;

                let durationText = "";
                if (hrs > 0) {
                    durationText += `${hrs} jam `;
                }
                durationText += `${mins} menit`;

                // Estimasi Biaya
                const cost = (diffMin / 60) * hourlyRate;
                const roundedCost = Math.round(cost);

                // Update DOM
                const liveTimerEl = container.querySelector('.active-live-timer');
                const durationEl = container.querySelector('.active-duration');
                const costEl = container.querySelector('.active-cost');

                if(liveTimerEl) liveTimerEl.textContent = liveTimerText;
                if(durationEl) durationEl.textContent = durationText;
                if(costEl) costEl.textContent = 'Rp ' + roundedCost.toLocaleString('id-ID');
            });
        }

        // --- Real-time Running Clock ---
        function updateRunningClock() {
            const clockEl = document.getElementById('running-clock');
            if (clockEl) {
                const now = new Date();
                const pad = (n) => String(n).padStart(2, '0');
                clockEl.textContent = `${pad(now.getHours())}:${pad(now.getMinutes())}:${pad(now.getSeconds())}`;
            }
        }
        setInterval(updateRunningClock, 1000);

        // --- Dexie.js local database setup ---
        const db = new Dexie("PlayStationRentalDB");
        db.version(1).stores({
            sync_queue: '++id, action, url, method, data, timestamp, isMultipart'
        });

        // --- Connection Status monitoring & Synchronization ---
        function updateConnectionStatus() {
            const badge = document.getElementById('connection-badge');
            const dot = document.getElementById('connection-dot');
            const text = document.getElementById('connection-text');
            
            if (!badge || !dot || !text) return;

            if (navigator.onLine) {
                db.sync_queue.count().then(count => {
                    if (count > 0) {
                        badge.className = "text-[10px] sm:text-xs font-bold bg-blue-50 text-blue-700 px-2.5 py-1.5 rounded-xl flex items-center gap-1.5 border border-blue-100";
                        dot.className = "w-1.5 h-1.5 sm:w-2 sm:h-2 bg-blue-500 rounded-full animate-pulse";
                        text.textContent = `Syncing (${count})...`;
                        triggerSync();
                    } else {
                        badge.className = "text-[10px] sm:text-xs font-bold bg-emerald-50 text-emerald-700 px-2.5 py-1.5 rounded-xl flex items-center gap-1.5 border border-emerald-100";
                        dot.className = "w-1.5 h-1.5 sm:w-2 sm:h-2 bg-emerald-500 rounded-full";
                        text.textContent = "Online";
                    }
                });
            } else {
                db.sync_queue.count().then(count => {
                    badge.className = "text-[10px] sm:text-xs font-bold bg-amber-50 text-amber-700 px-2.5 py-1.5 rounded-xl flex items-center gap-1.5 border border-amber-100";
                    dot.className = "w-1.5 h-1.5 sm:w-2 sm:h-2 bg-amber-500 rounded-full animate-ping";
                    text.textContent = count > 0 ? `Offline (${count} pending)` : "Offline";
                });
            }
        }

        let isSyncing = false;
        async function triggerSync() {
            if (isSyncing || !navigator.onLine) return;
            
            const queue = await db.sync_queue.orderBy('id').toArray();
            if (queue.length === 0) {
                updateConnectionStatus();
                return;
            }

            isSyncing = true;
            updateConnectionStatus();

            for (const item of queue) {
                try {
                    let options = {
                        method: item.method,
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        }
                    };

                    if (item.data) {
                        if (item.isMultipart) {
                            // Reconstruct FormData
                            const formData = new FormData();
                            for (const key in item.data) {
                                const val = item.data[key];
                                if (val instanceof Blob) {
                                    const filename = val.name || 'file';
                                    formData.append(key, val, filename);
                                } else {
                                    formData.append(key, val);
                                }
                            }
                            options.body = formData;
                        } else {
                            options.headers['Content-Type'] = 'application/json';
                            options.body = JSON.stringify(item.data);
                        }
                    }

                    const response = await fetch(item.url, options);
                    if (response.ok) {
                        await db.sync_queue.delete(item.id);
                    } else {
                        console.error("Sync failed for item:", item, await response.json());
                        break; 
                    }
                } catch (err) {
                    console.error("Network sync fetch failed:", err);
                    break; 
                }
            }

            isSyncing = false;
            updateConnectionStatus();
            refreshDashboard();
        }

        window.addEventListener('online', () => {
            updateConnectionStatus();
            triggerSync();
        });
        window.addEventListener('offline', updateConnectionStatus);

        // Run timers every 1 second (1000ms) for ticking effect
        setInterval(updateTimers, 1000);
        
        // Initial run
        document.addEventListener('DOMContentLoaded', () => {
            updateRunningClock();
            updateConnectionStatus();
            
            // Fetch and cache products if online
            if (navigator.onLine) {
                fetch('/admin/api/products')
                    .then(res => res.json())
                    .then(res => {
                        if (res.success) {
                            localStorage.setItem('ps_cached_products', JSON.stringify(res.data));
                        }
                    })
                    .catch(err => console.error("Gagal memuat produk:", err));
            }

            // Initialize local storage cache with Blade values if empty
            if (!localStorage.getItem('ps_cached_billing_units')) {
                const initialUnits = @json($units);
                const formattedInitial = initialUnits.map(unit => {
                    const activeOnsite = (unit.onsite_play_transactions && unit.onsite_play_transactions[0]) || (unit.onsitePlayTransactions && unit.onsitePlayTransactions[0]);
                    const activeRental = (unit.rental_transactions && unit.rental_transactions[0]) || (unit.rentalTransactions && unit.rentalTransactions[0]);
                    
                    let formattedOnsite = null;
                    if (activeOnsite) {
                        formattedOnsite = {
                            id: activeOnsite.id,
                            started_at: new Date(activeOnsite.started_at).toISOString(),
                            started_at_formatted: new Date(activeOnsite.started_at).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }),
                            hourly_rate: activeOnsite.hourly_rate,
                            orders: activeOnsite.orders ? activeOnsite.orders.map(order => {
                                return {
                                    id: order.id,
                                    product_name: order.product ? order.product.name : 'Produk Terhapus',
                                    quantity: order.quantity,
                                    total_price: order.total_price,
                                    total_price_formatted: 'Rp ' + order.total_price.toLocaleString('id-ID')
                                };
                            }) : []
                        };
                    }
                    
                    let formattedRental = null;
                    if (activeRental) {
                        formattedRental = {
                            id: activeRental.id,
                            renter_name: activeRental.renter_name,
                            phone: activeRental.phone,
                            include_tv: activeRental.include_tv,
                            rental_end_date_formatted: new Date(activeRental.rental_end_date).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }),
                            total_price_formatted: 'Rp ' + activeRental.total_price.toLocaleString('id-ID')
                        };
                    }
                    
                    return {
                        id: unit.id,
                        name: unit.name,
                        type: unit.type,
                        status: unit.status,
                        description: unit.description,
                        active_onsite: formattedOnsite,
                        active_rental: formattedRental
                    };
                });
                localStorage.setItem('ps_cached_billing_units', JSON.stringify(formattedInitial));
            }
            
            // Initial drawing
            const cached = localStorage.getItem('ps_cached_billing_units');
            if (cached) {
                updateUnitsUI(JSON.parse(cached));
            }
            
            refreshDashboard();
            triggerSync();
        });

        // --- DOM Update Helper ---
        function updateUnitsUI(units) {
            units.forEach(unit => {
                const card = document.getElementById(`unit-card-${unit.id}`);
                if (!card) return;

                // 1. Update Status Badge
                const badge = card.querySelector(`.status-badge-${unit.id}`);
                if (badge) {
                    badge.className = `inline-flex items-center gap-1 px-1.5 py-0.5 rounded-full text-[9px] font-bold uppercase tracking-wider status-badge-${unit.id}`;
                    let badgeColor = '';
                    let dotColor = '';
                    if (unit.status === 'Tersedia') {
                        badgeColor = 'bg-emerald-50 text-emerald-700 border border-emerald-100';
                        dotColor = 'bg-emerald-500';
                    } else if (unit.status === 'Bermain') {
                        badgeColor = 'bg-amber-50 text-amber-700 border border-amber-100 animate-pulse';
                        dotColor = 'bg-amber-500';
                    } else if (unit.status === 'Disewa') {
                        badgeColor = 'bg-blue-50 text-blue-700 border border-blue-100';
                        dotColor = 'bg-blue-500';
                    } else {
                        badgeColor = 'bg-rose-50 text-rose-700 border border-rose-100';
                        dotColor = 'bg-rose-500';
                    }
                    badge.className += ' ' + badgeColor;
                    badge.innerHTML = `<span class="w-1.5 h-1.5 rounded-full ${dotColor}"></span>${unit.status}`;
                }

                // 2. Update Info Body
                const body = document.getElementById(`unit-info-body-${unit.id}`);
                if (body) {
                    if (unit.status === 'Tersedia') {
                        body.innerHTML = `<p class="text-[11px] text-slate-500 font-medium">Ready to play. Hubungkan stik dan silakan pilih paket.</p>`;
                    } else if (unit.status === 'Bermain' && unit.active_onsite) {
                        let ordersHtml = '';
                        if (unit.active_onsite.orders && unit.active_onsite.orders.length > 0) {
                            const totalOrdersPrice = unit.active_onsite.orders.reduce((sum, o) => sum + parseFloat(o.total_price), 0);
                            ordersHtml = `
                                <div class="bg-slate-50 border border-slate-100 rounded-xl p-2.5 space-y-1 text-slate-700 mt-2">
                                    <div class="text-[9px] font-bold text-slate-400 uppercase tracking-wider border-b border-slate-100 pb-1 flex justify-between">
                                        <span>Pesanan F&B</span>
                                        <span class="text-slate-500">Rp ${totalOrdersPrice.toLocaleString('id-ID')}</span>
                                    </div>
                                    <div class="max-h-20 overflow-y-auto space-y-1 divide-y divide-slate-100/50 pr-1">
                                        ${unit.active_onsite.orders.map(order => `
                                            <div class="flex justify-between items-center text-[10px] pt-1">
                                                <span class="font-medium text-slate-600 truncate max-w-[90px]">${order.quantity}x ${order.product_name}</span>
                                                <span class="font-bold text-slate-700">Rp ${parseFloat(order.total_price).toLocaleString('id-ID')}</span>
                                            </div>
                                        `).join('')}
                                    </div>
                                </div>
                            `;
                        }

                        body.innerHTML = `
                            <div class="bg-amber-50/40 border border-amber-100/50 rounded-xl p-2.5 space-y-1 text-slate-700 timer-container" 
                                 data-unit-id="${unit.id}" 
                                 data-start-time="${unit.active_onsite.started_at}" 
                                 data-hourly-rate="${unit.active_onsite.hourly_rate}">
                                <div class="flex justify-between items-center text-[9px]">
                                    <span class="font-semibold text-slate-400 uppercase tracking-wider">Mulai Main</span>
                                    <span class="font-bold text-slate-700">${unit.active_onsite.started_at_formatted || new Date(unit.active_onsite.started_at).toLocaleTimeString('id-ID', {hour: '2-digit', minute:'2-digit'})}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-[9px] font-semibold text-slate-400 uppercase tracking-wider">Waktu Berjalan</span>
                                    <span class="font-extrabold text-slate-700 text-xs tracking-wide active-live-timer">00:00:00</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-[9px] font-semibold text-slate-400 uppercase tracking-wider">Durasi</span>
                                    <span class="font-bold text-amber-700 text-xs active-duration">-</span>
                                </div>
                                <div class="flex justify-between items-center pt-1 border-t border-amber-100/30">
                                    <span class="text-[9px] font-semibold text-slate-400 uppercase tracking-wider">Est. Biaya PS</span>
                                    <span class="font-black text-slate-800 text-xs active-cost">Rp 0</span>
                                </div>
                            </div>
                            ${ordersHtml}
                        `;
                    } else if (unit.status === 'Disewa' && unit.active_rental) {
                        const tvBadge = unit.active_rental.include_tv ? `
                                <div class="flex justify-between items-center text-[9px]">
                                    <span class="font-semibold text-slate-400 uppercase tracking-wider">Layanan</span>
                                    <span class="font-bold text-indigo-700 text-[10px] flex items-center gap-1"><i class="fa-solid fa-tv text-[8px]"></i> + TV</span>
                                </div>` : '';
                        body.innerHTML = `
                            <div class="bg-blue-50/40 border border-blue-100/50 rounded-xl p-2.5 space-y-1 text-slate-700">
                                <div class="flex justify-between items-center text-[9px]">
                                    <span class="font-semibold text-slate-400 uppercase tracking-wider">Penyewa</span>
                                    <span class="font-bold text-slate-800 truncate max-w-[80px]">${unit.active_rental.renter_name}</span>
                                </div>
                                <div class="flex justify-between items-center text-[9px]">
                                    <span class="font-semibold text-slate-400 uppercase tracking-wider">No HP</span>
                                    <span class="font-bold text-slate-700 text-[10px]">${unit.active_rental.phone}</span>
                                </div>
                                <div class="flex justify-between items-center text-[9px]">
                                    <span class="font-semibold text-slate-400 uppercase tracking-wider">Tgl Kembali</span>
                                    <span class="font-bold text-blue-700 text-[10px]">${unit.active_rental.rental_end_date_formatted}</span>
                                </div>
                                ${tvBadge}
                                <div class="flex justify-between items-center pt-1 border-t border-blue-100/30">
                                    <span class="text-[9px] font-semibold text-slate-400 uppercase tracking-wider">Biaya</span>
                                    <span class="font-bold text-slate-800 text-xs">${unit.active_rental.total_price_formatted}</span>
                                </div>
                            </div>
                        `;
                    } else {
                        body.innerHTML = `
                            <p class="text-[11px] text-slate-500 font-medium italic bg-rose-50/30 border border-rose-100/40 p-2 rounded-xl">
                                ${unit.description || 'Unit sedang dalam pemeliharaan berkala.'}
                            </p>
                        `;
                    }
                }

                // 3. Update Actions Footer
                const footer = document.getElementById(`unit-actions-${unit.id}`);
                if (footer) {
                    footer.className = "p-3 bg-slate-50/50 border-t border-slate-50 flex items-center justify-end gap-1.5";
                    if (unit.status === 'Tersedia') {
                        footer.innerHTML = `
                            <button onclick="startPlay(${unit.id})" class="flex-1 py-1.5 px-2 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-xl text-[10px] flex items-center justify-center gap-1 shadow-sm active:scale-[0.98] transition-all duration-200">
                                <i class="fa-solid fa-play"></i> Mulai Main
                            </button>
                            <button onclick="openRentalModal(${unit.id}, '${unit.name}', '${unit.type}')" class="py-1.5 px-2 border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-bold rounded-xl text-[10px] flex items-center justify-center gap-1 active:scale-[0.98] transition-all duration-200">
                                <i class="fa-solid fa-hand-holding-hand"></i> Sewa
                            </button>
                        `;
                    } else if (unit.status === 'Bermain') {
                        footer.innerHTML = `
                            <button onclick="openOrderModal(${unit.id}, '${unit.name}')" class="py-1.5 px-2 border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-bold rounded-xl text-[10px] flex items-center justify-center gap-1 active:scale-[0.98] transition-all duration-200">
                                <i class="fa-solid fa-bowl-food"></i> + Order
                            </button>
                            <button onclick="endPlay(${unit.id})" class="flex-1 py-1.5 px-2.5 bg-amber-600 hover:bg-amber-700 text-white font-black rounded-xl text-[10px] flex items-center justify-center gap-1 shadow-sm active:scale-[0.98] transition-all duration-200">
                                <i class="fa-solid fa-circle-stop"></i> Selesai
                            </button>
                        `;
                    } else if (unit.status === 'Disewa') {
                        footer.innerHTML = `
                            <button onclick="returnRental(${unit.id})" class="flex-1 py-1.5 px-2.5 bg-blue-600 hover:bg-blue-700 text-white font-black rounded-xl text-[10px] flex items-center justify-center gap-1 shadow-sm active:scale-[0.98] transition-all duration-200">
                                <i class="fa-solid fa-rotate-left"></i> Dikembalikan
                            </button>
                        `;
                    } else {
                        footer.innerHTML = `<span class="text-[10px] text-slate-400 font-bold italic py-1">Tidak ada aksi</span>`;
                    }
                }
            });
            updateTimers();
        }

        // --- AJAX Dashboard Updates ---
        async function refreshDashboard() {
            try {
                if (navigator.onLine) {
                    const res = await ajaxRequest("{{ route('dashboard.metrics') }}");
                    if (res.success) {
                        localStorage.setItem('ps_cached_billing_units', JSON.stringify(res.data.units));
                        updateUnitsUI(res.data.units);
                    }
                } else {
                    const cached = localStorage.getItem('ps_cached_billing_units');
                    if (cached) {
                        updateUnitsUI(JSON.parse(cached));
                    }
                }
            } catch (err) {
                console.error("Gagal memperbarui dashboard:", err);
                const cached = localStorage.getItem('ps_cached_billing_units');
                if (cached) {
                    updateUnitsUI(JSON.parse(cached));
                }
            }
        }

        // Set auto polling update board every 5 seconds (5000ms)
        setInterval(() => {
            if (navigator.onLine) {
                refreshDashboard();
            }
        }, 5000);

        // --- Quick Actions Trigger Methods ---
        async function startPlay(unitId) {
            Swal.fire({
                title: 'Mulai Billing?',
                text: 'Mulai menghitung waktu main untuk unit ini sekarang.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#0f172a',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Ya, Mulai',
                cancelButtonText: 'Batal'
            }).then(async (result) => {
                if (result.isConfirmed) {
                    if (!navigator.onLine) {
                        const cached = localStorage.getItem('ps_cached_billing_units');
                        if (cached) {
                            const units = JSON.parse(cached);
                            const unitIndex = units.findIndex(u => u.id === unitId);
                            if (unitIndex !== -1) {
                                const unit = units[unitIndex];
                                if (unit.status !== 'Tersedia') {
                                    Swal.fire('Error', `Unit ${unit.name} sedang tidak tersedia (Status: ${unit.status}).`, 'error');
                                    return;
                                }
                                
                                const startedAt = new Date();
                                const startedAtISO = startedAt.toISOString();
                                
                                unit.status = 'Bermain';
                                unit.active_onsite = {
                                    id: 'temp-' + Date.now(),
                                    started_at: startedAtISO,
                                    started_at_formatted: String(startedAt.getHours()).padStart(2, '0') + ':' + String(startedAt.getMinutes()).padStart(2, '0'),
                                    hourly_rate: hourlyRates[unit.type] || 0
                                };
                                
                                localStorage.setItem('ps_cached_billing_units', JSON.stringify(units));
                                updateUnitsUI(units);
                                
                                await db.sync_queue.add({
                                    action: 'startPlay',
                                    url: "{{ route('dashboard.start-play') }}",
                                    method: 'POST',
                                    data: {
                                        playstation_unit_id: unitId,
                                        started_at: startedAtISO
                                    },
                                    timestamp: Date.now(),
                                    isMultipart: false
                                });
                                
                                showToast('success', `Billing untuk unit ${unit.name} berhasil dimulai (Offline).`);
                                updateConnectionStatus();
                            }
                        }
                        return;
                    }

                    try {
                        const res = await ajaxRequest("{{ route('dashboard.start-play') }}", 'POST', { playstation_unit_id: unitId });
                        if (res.success) {
                            showToast('success', res.message);
                            refreshDashboard();
                        }
                    } catch (err) {
                        Swal.fire('Error', err.data?.message || 'Gagal memulai billing.', 'error');
                    }
                }
            });
        }

        async function endPlay(unitId) {
            Swal.fire({
                title: 'Selesaikan Billing?',
                text: 'Waktu main akan dihentikan dan total biaya akan dihitung.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d97706',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Ya, Selesaikan',
                cancelButtonText: 'Batal'
            }).then(async (result) => {
                if (result.isConfirmed) {
                    if (!navigator.onLine) {
                        const cached = localStorage.getItem('ps_cached_billing_units');
                        if (cached) {
                            const units = JSON.parse(cached);
                            const unitIndex = units.findIndex(u => u.id === unitId);
                            if (unitIndex !== -1) {
                                const unit = units[unitIndex];
                                if (unit.status !== 'Bermain') {
                                    Swal.fire('Error', `Unit ${unit.name} sedang tidak dalam status 'Bermain'.`, 'error');
                                    return;
                                }
                                
                                const startedAt = new Date(unit.active_onsite.started_at);
                                const endedAt = new Date();
                                const endedAtISO = endedAt.toISOString();
                                
                                let durationMinutes = Math.max(1, Math.floor((endedAt - startedAt) / 60000));
                                const hourlyRate = parseFloat(unit.active_onsite.hourly_rate);
                                const playPrice = Math.round((durationMinutes / 60) * hourlyRate);
                                
                                let ordersSum = 0;
                                if (unit.active_onsite.orders) {
                                    ordersSum = unit.active_onsite.orders.reduce((sum, o) => sum + parseFloat(o.total_price), 0);
                                }
                                const totalPrice = playPrice + ordersSum;
                                
                                const hours = Math.floor(durationMinutes / 60);
                                const minutes = durationMinutes % 60;
                                let durationText = "";
                                if (hours > 0) durationText += `${hours} jam `;
                                durationText += `${minutes} menit`;
                                
                                unit.status = 'Tersedia';
                                unit.active_onsite = null;
                                
                                localStorage.setItem('ps_cached_billing_units', JSON.stringify(units));
                                updateUnitsUI(units);
                                
                                await db.sync_queue.add({
                                    action: 'endPlay',
                                    url: "{{ route('dashboard.end-play') }}",
                                    method: 'POST',
                                    data: {
                                        playstation_unit_id: unitId,
                                        ended_at: endedAtISO
                                    },
                                    timestamp: Date.now(),
                                    isMultipart: false
                                });
                                
                                Swal.fire({
                                    title: 'Billing Selesai (Offline)',
                                    html: `
                                        <div class="text-left space-y-1.5 p-2.5 bg-slate-50 rounded-xl border border-slate-100 text-[11px]">
                                            <div class="flex justify-between border-b border-slate-200/50 pb-1">
                                                <span class="text-slate-400 font-semibold uppercase text-[9px] tracking-wide">Unit PS</span>
                                                <span class="font-bold text-slate-800 text-[11px]">${unit.name}</span>
                                            </div>
                                            <div class="flex justify-between border-b border-slate-200/50 pb-1">
                                                <span class="text-slate-400 font-semibold uppercase text-[9px] tracking-wide">Waktu Mulai</span>
                                                <span class="font-medium text-slate-700 text-[11px]">${startedAt.toLocaleString('id-ID', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' })}</span>
                                            </div>
                                            <div class="flex justify-between border-b border-slate-200/50 pb-1">
                                                <span class="text-slate-400 font-semibold uppercase text-[9px] tracking-wide">Waktu Selesai</span>
                                                <span class="font-medium text-slate-700 text-[11px]">${endedAt.toLocaleString('id-ID', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' })}</span>
                                            </div>
                                            <div class="flex justify-between border-b border-slate-200/50 pb-1">
                                                <span class="text-slate-400 font-semibold uppercase text-[9px] tracking-wide">Total Durasi</span>
                                                <span class="font-extrabold text-amber-700 text-[11px]">${durationText}</span>
                                            </div>
                                            <div class="flex justify-between border-b border-slate-200/50 pb-1">
                                                <span class="text-slate-400 font-semibold uppercase text-[9px] tracking-wide">Tarif / Jam</span>
                                                <span class="font-medium text-slate-700 text-[11px]">Rp ${hourlyRate.toLocaleString('id-ID')}</span>
                                            </div>
                                            <div class="flex justify-between border-b border-slate-200/50 pb-1">
                                                <span class="text-slate-400 font-semibold uppercase text-[9px] tracking-wide">Biaya PS</span>
                                                <span class="font-medium text-slate-700 text-[11px]">Rp ${playPrice.toLocaleString('id-ID')}</span>
                                            </div>
                                            <div class="flex justify-between border-b border-slate-200/50 pb-1">
                                                <span class="text-slate-400 font-semibold uppercase text-[9px] tracking-wide">Total F&B</span>
                                                <span class="font-medium text-slate-700 text-[11px]">Rp ${ordersSum.toLocaleString('id-ID')}</span>
                                            </div>
                                            <div class="flex justify-between pt-1">
                                                <span class="text-slate-500 font-bold uppercase text-[10px] tracking-wide">Total Bayar</span>
                                                <span class="font-black text-emerald-600 text-sm">Rp ${totalPrice.toLocaleString('id-ID')}</span>
                                            </div>
                                        </div>
                                    `,
                                    icon: 'success',
                                    confirmButtonColor: '#0f172a',
                                    confirmButtonText: 'Tutup'
                                });
                                updateConnectionStatus();
                            }
                        }
                        return;
                    }

                    try {
                        const res = await ajaxRequest("{{ route('dashboard.end-play') }}", 'POST', { playstation_unit_id: unitId });
                        if (res.success) {
                            Swal.fire({
                                title: 'Billing Selesai',
                                html: `
                                    <div class="text-left space-y-1.5 p-2.5 bg-slate-50 rounded-xl border border-slate-100 text-[11px]">
                                        <div class="flex justify-between border-b border-slate-200/50 pb-1">
                                            <span class="text-slate-400 font-semibold uppercase text-[9px] tracking-wide">Unit PS</span>
                                            <span class="font-bold text-slate-800 text-[11px]">${res.data.unit_name}</span>
                                        </div>
                                        <div class="flex justify-between border-b border-slate-200/50 pb-1">
                                            <span class="text-slate-400 font-semibold uppercase text-[9px] tracking-wide">Waktu Mulai</span>
                                            <span class="font-medium text-slate-700 text-[11px]">${res.data.started_at}</span>
                                        </div>
                                        <div class="flex justify-between border-b border-slate-200/50 pb-1">
                                            <span class="text-slate-400 font-semibold uppercase text-[9px] tracking-wide">Waktu Selesai</span>
                                            <span class="font-medium text-slate-700 text-[11px]">${res.data.ended_at}</span>
                                        </div>
                                        <div class="flex justify-between border-b border-slate-200/50 pb-1">
                                            <span class="text-slate-400 font-semibold uppercase text-[9px] tracking-wide">Total Durasi</span>
                                            <span class="font-extrabold text-amber-700 text-[11px]">${res.data.duration_text}</span>
                                        </div>
                                        <div class="flex justify-between border-b border-slate-200/50 pb-1">
                                            <span class="text-slate-400 font-semibold uppercase text-[9px] tracking-wide">Tarif / Jam</span>
                                            <span class="font-medium text-slate-700 text-[11px]">${res.data.hourly_rate}</span>
                                        </div>
                                        <div class="flex justify-between border-b border-slate-200/50 pb-1">
                                            <span class="text-slate-400 font-semibold uppercase text-[9px] tracking-wide">Biaya PS</span>
                                            <span class="font-medium text-slate-700 text-[11px]">${res.data.play_price}</span>
                                        </div>
                                        <div class="flex justify-between border-b border-slate-200/50 pb-1">
                                            <span class="text-slate-400 font-semibold uppercase text-[9px] tracking-wide">Total F&B</span>
                                            <span class="font-medium text-slate-700 text-[11px]">${res.data.orders_price}</span>
                                        </div>
                                        <div class="flex justify-between pt-1">
                                            <span class="text-slate-500 font-bold uppercase text-[10px] tracking-wide">Total Bayar</span>
                                            <span class="font-black text-emerald-600 text-sm">${res.data.total_price}</span>
                                        </div>
                                    </div>
                                `,
                                icon: 'success',
                                confirmButtonColor: '#0f172a',
                                confirmButtonText: 'Tutup'
                            }).then(() => {
                                refreshDashboard();
                            });
                        }
                    } catch (err) {
                        Swal.fire('Error', err.data?.message || 'Gagal menyelesaikan billing.', 'error');
                    }
                }
            });
        }

        async function returnRental(unitId) {
            Swal.fire({
                title: 'Kembalikan PlayStation?',
                text: 'Tandai sewa unit ini telah selesai dan unit dikembalikan.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#2563eb',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Ya, Kembalikan',
                cancelButtonText: 'Batal'
            }).then(async (result) => {
                if (result.isConfirmed) {
                    if (!navigator.onLine) {
                        const cached = localStorage.getItem('ps_cached_billing_units');
                        if (cached) {
                            const units = JSON.parse(cached);
                            const unitIndex = units.findIndex(u => u.id === unitId);
                            if (unitIndex !== -1) {
                                const unit = units[unitIndex];
                                if (unit.status !== 'Disewa') {
                                    Swal.fire('Error', `Unit ${unit.name} sedang tidak dalam status 'Disewa'.`, 'error');
                                    return;
                                }
                                
                                unit.status = 'Tersedia';
                                unit.active_rental = null;
                                
                                localStorage.setItem('ps_cached_billing_units', JSON.stringify(units));
                                updateUnitsUI(units);
                                
                                await db.sync_queue.add({
                                    action: 'returnRental',
                                    url: "{{ route('dashboard.return-rental') }}",
                                    method: 'POST',
                                    data: {
                                        playstation_unit_id: unitId
                                    },
                                    timestamp: Date.now(),
                                    isMultipart: false
                                });
                                
                                showToast('success', `Unit ${unit.name} telah berhasil dikembalikan (Offline).`);
                                updateConnectionStatus();
                            }
                        }
                        return;
                    }

                    try {
                        const res = await ajaxRequest("{{ route('dashboard.return-rental') }}", 'POST', { playstation_unit_id: unitId });
                        if (res.success) {
                            showToast('success', res.message);
                            refreshDashboard();
                        }
                    } catch (err) {
                        Swal.fire('Error', err.data?.message || 'Gagal mengembalikan sewa.', 'error');
                    }
                }
            });
        }

        // --- Rental Modal Helper Methods ---
        const rentalModal = document.getElementById('rental-modal');
        
        function openRentalModal(unitId, unitName, unitType) {
            document.getElementById('rental-unit-id').value = unitId;
            document.getElementById('rental-unit-name').value = unitName;
            document.getElementById('rental-unit-type').value = unitType;

            document.getElementById('rental-include-tv').checked = false;
            const today = new Date();
            document.getElementById('rental-start-date').value = today.toISOString().split('T')[0];
            document.getElementById('rental-days').value = '1.0';

            rentalModal.classList.remove('hidden');
            calculateRentalCost();
        }

        function closeRentalModal() {
            rentalModal.classList.add('hidden');
            document.getElementById('rental-form').reset();
        }

        function calculateRentalCost() {
            const type = document.getElementById('rental-unit-type').value;
            const daysVal = parseFloat(document.getElementById('rental-days').value) || 0;

            const dailyRate = dailyRates[type] || 0;
            const halfDayRate = halfDayRates[type] || 0;
            const durationDisplay = document.getElementById('rental-duration-display');
            const costDisplay = document.getElementById('rental-cost-display');

            if (daysVal <= 0) {
                durationDisplay.textContent = "0 hari";
                costDisplay.textContent = "Rp 0";
                return;
            }

            const fullDays = Math.floor(daysVal);
            const hasHalfDay = (daysVal - fullDays) > 0.1;

            const totalCost = (fullDays * dailyRate) + (hasHalfDay ? halfDayRate : 0);

            durationDisplay.textContent = `${daysVal} hari`;
            costDisplay.textContent = `Rp ${totalCost.toLocaleString('id-ID')}`;
        }

        async function submitRental(e) {
            e.preventDefault();
            
            const form = document.getElementById('rental-form');
            const formData = new FormData(form);

            const days = parseFloat(document.getElementById('rental-days').value);
            if (isNaN(days) || days < 0.5) {
                Swal.fire('Validasi Gagal', 'Durasi sewa minimal adalah 0.5 hari.', 'error');
                return;
            }

            if (!navigator.onLine) {
                const unitId = parseInt(document.getElementById('rental-unit-id').value);
                const renterName = form.querySelector('[name="renter_name"]').value;
                const phone = form.querySelector('[name="phone"]').value;
                const fileInput = form.querySelector('[name="identity_card"]');
                const rentalStartDate = document.getElementById('rental-start-date').value;
                const rentalDays = parseFloat(document.getElementById('rental-days').value);
                const includeTv = document.getElementById('rental-include-tv').checked;

                if (!fileInput.files || fileInput.files.length === 0) {
                    Swal.fire('Validasi Gagal', 'File jaminan identitas wajib diunggah.', 'error');
                    return;
                }

                const cached = localStorage.getItem('ps_cached_billing_units');
                if (cached) {
                    const units = JSON.parse(cached);
                    const unitIndex = units.findIndex(u => u.id === unitId);
                    if (unitIndex !== -1) {
                        const unit = units[unitIndex];
                        if (unit.status !== 'Tersedia') {
                            Swal.fire('Error', `Unit ${unit.name} sedang tidak tersedia untuk disewa.`, 'error');
                            return;
                        }

                        const dailyRate = dailyRates[unit.type] || 0;
                        const halfDayRate = halfDayRates[unit.type] || 0;
                        const fullDays = Math.floor(rentalDays);
                        const hasHalfDay = (rentalDays - fullDays) > 0.1;
                        const totalPrice = (fullDays * dailyRate) + (hasHalfDay ? halfDayRate : 0);

                        const start = new Date(rentalStartDate);
                        const durationHours = rentalDays * 24;
                        const end = new Date(start.getTime() + durationHours * 60 * 60 * 1000);

                        unit.status = 'Disewa';
                        unit.active_rental = {
                            id: 'temp-' + Date.now(),
                            renter_name: renterName,
                            phone: phone,
                            include_tv: includeTv,
                            rental_end_date_formatted: end.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }),
                            total_price_formatted: 'Rp ' + totalPrice.toLocaleString('id-ID')
                        };

                        localStorage.setItem('ps_cached_billing_units', JSON.stringify(units));
                        updateUnitsUI(units);

                        const data = {
                            playstation_unit_id: unitId,
                            renter_name: renterName,
                            phone: phone,
                            rental_start_date: rentalStartDate,
                            rental_days: rentalDays,
                            identity_card: fileInput.files[0]
                        };
                        if (includeTv) {
                            data.include_tv = 'on';
                        }

                        await db.sync_queue.add({
                            action: 'storeRental',
                            url: '/admin/api/transactions/rental',
                            method: 'POST',
                            data: data,
                            timestamp: Date.now(),
                            isMultipart: true
                        });

                        closeRentalModal();
                        showToast('success', `Sewa unit ${unit.name} untuk ${renterName} berhasil didaftarkan (Offline).`);
                        updateConnectionStatus();
                    }
                }
                return;
            }

            try {
                const res = await ajaxRequest('/admin/api/transactions/rental', 'POST', formData, true);
                if (res.success) {
                    closeRentalModal();
                    showToast('success', res.message);
                    refreshDashboard();
                }
            } catch (err) {
                Swal.fire('Error', err.data?.message || 'Gagal mendaftarkan sewa.', 'error');
            }
        }

        // --- F&B Order Modal Helper Methods ---
        const orderModal = document.getElementById('order-modal');

        function openOrderModal(unitId, unitName) {
            document.getElementById('order-unit-id').value = unitId;
            document.getElementById('order-unit-subtitle').textContent = `Menambahkan pesanan untuk: ${unitName}`;
            
            const productSelect = document.getElementById('order-product-id');
            const cachedProducts = localStorage.getItem('ps_cached_products');
            let products = [];
            if (cachedProducts) {
                products = JSON.parse(cachedProducts);
            }

            if (products.length === 0) {
                productSelect.innerHTML = `<option value="">Tidak ada produk tersedia</option>`;
                document.getElementById('order-unit-price-display').textContent = 'Rp 0';
                document.getElementById('order-subtotal-display').textContent = 'Rp 0';
                Swal.fire('Info', 'Belum ada produk yang didaftarkan. Kelola produk terlebih dahulu di menu Kelola Produk / F&B.', 'info');
                return;
            }

            productSelect.innerHTML = products.map(p => {
                const stockText = p.stock > 0 ? `Stok: ${p.stock}` : 'Habis';
                const disabled = p.stock <= 0 ? 'disabled' : '';
                return `<option value="${p.id}" data-price="${p.price}" data-stock="${p.stock}" ${disabled}>${p.name} - Rp ${p.price.toLocaleString('id-ID')} (${stockText})</option>`;
            }).join('');

            // Reset quantity to 1
            document.getElementById('order-qty').value = 1;

            orderModal.classList.remove('hidden');
            calculateOrderCost();
        }

        function closeOrderModal() {
            orderModal.classList.add('hidden');
            document.getElementById('order-form').reset();
        }

        function adjustQty(amount) {
            const qtyInput = document.getElementById('order-qty');
            let val = parseInt(qtyInput.value) || 1;
            val += amount;
            if (val < 1) val = 1;
            
            // Check stock limit if selected product has stock info
            const select = document.getElementById('order-product-id');
            const selectedOpt = select.options[select.selectedIndex];
            if (selectedOpt) {
                const stock = parseInt(selectedOpt.getAttribute('data-stock')) || 0;
                if (val > stock && stock > 0) {
                    val = stock;
                    showToast('warning', `Stok terbatas, maksimal ${stock} unit.`);
                }
            }

            qtyInput.value = val;
            calculateOrderCost();
        }

        function calculateOrderCost() {
            const select = document.getElementById('order-product-id');
            const selectedOpt = select.options[select.selectedIndex];
            const qtyInput = document.getElementById('order-qty');
            const qty = parseInt(qtyInput.value) || 1;

            const priceDisplay = document.getElementById('order-unit-price-display');
            const subtotalDisplay = document.getElementById('order-subtotal-display');

            if (!selectedOpt || selectedOpt.value === "") {
                priceDisplay.textContent = "Rp 0";
                subtotalDisplay.textContent = "Rp 0";
                return;
            }

            const price = parseFloat(selectedOpt.getAttribute('data-price')) || 0;
            const subtotal = price * qty;

            priceDisplay.textContent = `Rp ${price.toLocaleString('id-ID')}`;
            subtotalDisplay.textContent = `Rp ${subtotal.toLocaleString('id-ID')}`;
        }

        async function submitOrder(e) {
            e.preventDefault();
            
            const unitId = parseInt(document.getElementById('order-unit-id').value);
            const productId = parseInt(document.getElementById('order-product-id').value);
            const qty = parseInt(document.getElementById('order-qty').value) || 1;

            const select = document.getElementById('order-product-id');
            const selectedOpt = select.options[select.selectedIndex];
            const price = parseFloat(selectedOpt.getAttribute('data-price')) || 0;
            const subtotal = price * qty;

            if (!productId) {
                Swal.fire('Validasi Gagal', 'Silakan pilih produk terlebih dahulu.', 'error');
                return;
            }

            if (!navigator.onLine) {
                const cached = localStorage.getItem('ps_cached_billing_units');
                if (cached) {
                    const units = JSON.parse(cached);
                    const unitIndex = units.findIndex(u => u.id === unitId);
                    if (unitIndex !== -1) {
                        const unit = units[unitIndex];
                        if (unit.status !== 'Bermain') {
                            Swal.fire('Error', `Unit ${unit.name} sedang tidak aktif bermain.`, 'error');
                            return;
                        }

                        if (!unit.active_onsite.orders) {
                            unit.active_onsite.orders = [];
                        }

                        // decrement local stock in cached products
                        const cachedProds = localStorage.getItem('ps_cached_products');
                        let productName = 'Produk Terhapus';
                        if (cachedProds) {
                            const products = JSON.parse(cachedProds);
                            const pIdx = products.findIndex(p => p.id === productId);
                            if (pIdx !== -1) {
                                productName = products[pIdx].name;
                                if (products[pIdx].stock >= qty) {
                                    products[pIdx].stock -= qty;
                                    localStorage.setItem('ps_cached_products', JSON.stringify(products));
                                }
                            }
                        }

                        unit.active_onsite.orders.push({
                            id: 'temp-order-' + Date.now(),
                            product_name: productName,
                            quantity: qty,
                            total_price: subtotal,
                            total_price_formatted: 'Rp ' + subtotal.toLocaleString('id-ID')
                        });

                        localStorage.setItem('ps_cached_billing_units', JSON.stringify(units));
                        updateUnitsUI(units);

                        await db.sync_queue.add({
                            action: 'addOrder',
                            url: "{{ route('dashboard.add-order') }}",
                            method: 'POST',
                            data: {
                                playstation_unit_id: unitId,
                                product_id: productId,
                                quantity: qty
                            },
                            timestamp: Date.now(),
                            isMultipart: false
                        });

                        closeOrderModal();
                        showToast('success', `Pesanan ${qty}x ${productName} berhasil ditambahkan (Offline).`);
                        updateConnectionStatus();
                    }
                }
                return;
            }

            try {
                const res = await ajaxRequest("{{ route('dashboard.add-order') }}", 'POST', {
                    playstation_unit_id: unitId,
                    product_id: productId,
                    quantity: qty
                });
                if (res.success) {
                    closeOrderModal();
                    showToast('success', res.message);
                    refreshDashboard();
                }
            } catch (err) {
                Swal.fire('Error', err.data?.message || 'Gagal menambahkan pesanan.', 'error');
            }
        }
    </script>
</body>
</html>
