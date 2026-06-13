<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - {{ \App\Models\AppSetting::first()->app_name ?? 'Rental PlayStation' }}</title>
    
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
        .sidebar-active {
            background-color: #f1f5f9;
            color: #0f172a;
            font-weight: 600;
            border-right: 3px solid #0f172a;
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
    @yield('styles')
</head>
<body class="bg-slate-50 text-slate-900 min-h-screen flex selection:bg-slate-950 selection:text-white">

    <!-- Sidebar -->
    <aside class="w-56 md:w-[72px] md:hover:w-56 group bg-white border-r border-slate-100 flex flex-col fixed h-full z-20 transition-all duration-300 md:translate-x-0 -translate-x-full" id="sidebar">
        <!-- Logo Header -->
        <div class="h-20 flex items-center px-[20px] border-b border-slate-100 gap-4 overflow-hidden">
            @if($settings && $settings->logo)
                <img src="{{ $settings->logo_url }}" alt="Logo" class="w-8 h-8 object-contain flex-shrink-0">
            @else
                <div class="w-8 h-8 bg-slate-900 text-white rounded-lg flex items-center justify-center font-bold text-base flex-shrink-0">
                    PS
                </div>
            @endif
            <span class="font-bold text-lg text-slate-800 tracking-tight leading-none whitespace-nowrap opacity-100 md:opacity-0 md:group-hover:opacity-100 transition-opacity duration-300">
                {{ $settings->app_name ?? 'Rental PlayStation' }}
            </span>
        </div>

        <!-- Navigation Links -->
        <nav class="flex-1 px-3 py-6 space-y-1.5 overflow-y-auto">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-4 px-[14px] py-3 text-sm font-medium text-slate-500 rounded-xl hover:bg-slate-50 hover:text-slate-900 transition-all duration-200 {{ Route::is('dashboard') ? 'sidebar-active' : '' }} overflow-hidden">
                <i class="fa-solid fa-chart-pie w-5 text-center text-base flex-shrink-0"></i>
                <span class="opacity-100 md:opacity-0 md:group-hover:opacity-100 transition-opacity duration-300 whitespace-nowrap">
                    Dashboard
                </span>
            </a>
            <a href="{{ route('billing') }}" target="_blank" class="flex items-center gap-4 px-[14px] py-3 text-sm font-medium text-slate-500 rounded-xl hover:bg-slate-50 hover:text-slate-900 transition-all duration-200 {{ Route::is('billing') ? 'sidebar-active' : '' }} overflow-hidden">
                <i class="fa-solid fa-desktop w-5 text-center text-base flex-shrink-0"></i>
                <span class="opacity-100 md:opacity-0 md:group-hover:opacity-100 transition-opacity duration-300 whitespace-nowrap">
                    Billing
                </span>
            </a>
            <a href="{{ route('units.index') }}" class="flex items-center gap-4 px-[14px] py-3 text-sm font-medium text-slate-500 rounded-xl hover:bg-slate-50 hover:text-slate-900 transition-all duration-200 {{ Route::is('units.index') ? 'sidebar-active' : '' }} overflow-hidden">
                <i class="fa-solid fa-gamepad w-5 text-center text-base flex-shrink-0"></i>
                <span class="opacity-100 md:opacity-0 md:group-hover:opacity-100 transition-opacity duration-300 whitespace-nowrap">
                    Kelola Unit PS
                </span>
            </a>
            <a href="{{ route('rates.index') }}" class="flex items-center gap-4 px-[14px] py-3 text-sm font-medium text-slate-500 rounded-xl hover:bg-slate-50 hover:text-slate-900 transition-all duration-200 {{ Route::is('rates.index') ? 'sidebar-active' : '' }} overflow-hidden">
                <i class="fa-solid fa-tags w-5 text-center text-base flex-shrink-0"></i>
                <span class="opacity-100 md:opacity-0 md:group-hover:opacity-100 transition-opacity duration-300 whitespace-nowrap">
                    Kelola Tarif
                </span>
            </a>
            <a href="{{ route('products.index') }}" class="flex items-center gap-4 px-[14px] py-3 text-sm font-medium text-slate-500 rounded-xl hover:bg-slate-50 hover:text-slate-900 transition-all duration-200 {{ Route::is('products.index') ? 'sidebar-active' : '' }} overflow-hidden">
                <i class="fa-solid fa-bowl-food w-5 text-center text-base flex-shrink-0"></i>
                <span class="opacity-100 md:opacity-0 md:group-hover:opacity-100 transition-opacity duration-300 whitespace-nowrap">
                    Kelola Produk / F&B
                </span>
            </a>
            <a href="{{ route('transactions.index') }}" class="flex items-center gap-4 px-[14px] py-3 text-sm font-medium text-slate-500 rounded-xl hover:bg-slate-50 hover:text-slate-900 transition-all duration-200 {{ Route::is('transactions.index') ? 'sidebar-active' : '' }} overflow-hidden">
                <i class="fa-solid fa-clock-rotate-left w-5 text-center text-base flex-shrink-0"></i>
                <span class="opacity-100 md:opacity-0 md:group-hover:opacity-100 transition-opacity duration-300 whitespace-nowrap">
                    Riwayat Transaksi
                </span>
            </a>
            <a href="{{ route('reports.index') }}" class="flex items-center gap-4 px-[14px] py-3 text-sm font-medium text-slate-500 rounded-xl hover:bg-slate-50 hover:text-slate-900 transition-all duration-200 {{ Route::is('reports.index') ? 'sidebar-active' : '' }} overflow-hidden">
                <i class="fa-solid fa-wallet w-5 text-center text-base flex-shrink-0"></i>
                <span class="opacity-100 md:opacity-0 md:group-hover:opacity-100 transition-opacity duration-300 whitespace-nowrap">
                    Laporan Pendapatan
                </span>
            </a>
            <a href="{{ route('settings.index') }}" class="flex items-center gap-4 px-[14px] py-3 text-sm font-medium text-slate-500 rounded-xl hover:bg-slate-50 hover:text-slate-900 transition-all duration-200 {{ Route::is('settings.index') ? 'sidebar-active' : '' }} overflow-hidden">
                <i class="fa-solid fa-gears w-5 text-center text-base flex-shrink-0"></i>
                <span class="opacity-100 md:opacity-0 md:group-hover:opacity-100 transition-opacity duration-300 whitespace-nowrap">
                    Pengaturan
                </span>
            </a>
        </nav>

        <!-- Sidebar Footer -->
        <div class="p-4 border-t border-slate-100 bg-slate-50/50 overflow-hidden">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 bg-slate-200 text-slate-700 rounded-full flex items-center justify-center font-bold text-sm flex-shrink-0">
                    {{ substr(Auth::user()->name ?? 'A', 0, 1) }}
                </div>
                <div class="flex-1 min-w-0 opacity-100 md:opacity-0 md:group-hover:opacity-100 transition-opacity duration-300 whitespace-nowrap">
                    <p class="text-xs font-semibold text-slate-800 truncate">{{ Auth::user()->name ?? 'Admin Kasir' }}</p>
                    <p class="text-[10px] font-medium text-slate-400 truncate">{{ Auth::user()->email ?? 'admin@gmail.com' }}</p>
                </div>
                <form action="{{ route('logout') }}" method="POST" id="logout-form" onsubmit="confirmLogout(event)" class="opacity-100 md:opacity-0 md:group-hover:opacity-100 transition-opacity duration-300 flex-shrink-0">
                    @csrf
                    <button type="submit" class="p-2 text-slate-400 hover:text-red-600 rounded-lg hover:bg-slate-100 transition-all duration-200" title="Keluar">
                        <i class="fa-solid fa-right-from-bracket"></i>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <!-- Sidebar Overlay for Mobile -->
    <div class="fixed inset-0 bg-slate-950/40 z-10 hidden" id="sidebar-overlay"></div>

    <!-- Main Content Area -->
    <div class="flex-1 md:pl-[72px] flex flex-col min-h-screen">
        <!-- Navbar -->
        <header class="h-16 md:h-20 bg-white border-b border-slate-100 px-4 md:px-6 flex items-center justify-between sticky top-0 z-10">
            <div class="flex items-center gap-2 md:gap-4 min-w-0">
                <button class="md:hidden p-1.5 text-slate-500 hover:bg-slate-50 rounded-xl transition-all duration-200 flex-shrink-0" id="sidebar-toggle">
                    <i class="fa-solid fa-bars text-lg"></i>
                </button>
                <h2 class="text-sm sm:text-base md:text-xl font-bold text-slate-950 tracking-tight truncate">@yield('page_title', 'Dashboard')</h2>
            </div>
            
            <div class="flex items-center gap-2 md:gap-3 flex-shrink-0">
                <!-- Offline/Online status badge -->
                <span id="connection-badge" class="text-[10px] sm:text-xs md:text-sm font-bold px-2 py-1 sm:px-3 sm:py-1.5 rounded-xl flex items-center gap-1.5 transition-colors duration-300">
                    <span id="connection-dot" class="w-1.5 h-1.5 sm:w-2 sm:h-2 rounded-full"></span>
                    <span id="connection-text">Checking...</span>
                </span>

                <span class="text-[10px] sm:text-xs md:text-sm font-bold bg-slate-100 text-slate-700 px-2 py-1 sm:px-3 sm:py-1.5 rounded-xl flex items-center gap-1.5">
                    <span class="w-1.5 h-1.5 sm:w-2 sm:h-2 bg-emerald-500 rounded-full animate-pulse"></span>
                    <span class="hidden sm:inline">Shift Active:</span>
                    <span class="inline sm:hidden">Kasir:</span>
                    {{ Auth::user()->name }}
                </span>
            </div>
        </header>

        <!-- Main Body -->
        <main class="flex-1 p-6 md:p-8 max-w-7xl w-full mx-auto space-y-6">
            @yield('content')
        </main>
    </div>

    <!-- AJAX Core Helpers, PWA, and Sync Logic -->
    <script>
        // Set CSRF token in fetch headers by default
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

        // Sidebar Toggle for Mobile
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebar-overlay');
        const toggle = document.getElementById('sidebar-toggle');

        function toggleSidebar() {
            sidebar.classList.toggle('-translate-x-full');
            overlay.classList.toggle('hidden');
        }

        if(toggle) toggle.addEventListener('click', toggleSidebar);
        if(overlay) overlay.addEventListener('click', toggleSidebar);

        // SweetAlert Mixins
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

        function confirmLogout(event) {
            event.preventDefault();
            Swal.fire({
                title: 'Keluar Aplikasi?',
                text: 'Apakah Anda yakin ingin mengakhiri sesi kasir Anda?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Ya, Keluar',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('logout-form').submit();
                }
            });
        }

        // --- PWA Service Worker Registration ---
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js')
                    .then(reg => console.log('Service Worker registered successfully.', reg.scope))
                    .catch(err => console.log('Service Worker registration failed:', err));
            });
        }

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
                        badge.className = "text-[10px] sm:text-xs md:text-sm font-bold bg-blue-50 text-blue-700 px-2 py-1 sm:px-3 sm:py-1.5 rounded-xl flex items-center gap-1.5 border border-blue-100";
                        dot.className = "w-1.5 h-1.5 sm:w-2 sm:h-2 bg-blue-500 rounded-full animate-pulse";
                        text.textContent = `Syncing (${count})...`;
                        triggerSync();
                    } else {
                        badge.className = "text-[10px] sm:text-xs md:text-sm font-bold bg-emerald-50 text-emerald-700 px-2 py-1 sm:px-3 sm:py-1.5 rounded-xl flex items-center gap-1.5 border border-emerald-100";
                        dot.className = "w-1.5 h-1.5 sm:w-2 sm:h-2 bg-emerald-500 rounded-full";
                        text.textContent = "Online";
                    }
                }).catch(err => {
                    console.error("Dexie sync queue count failed:", err);
                    badge.className = "text-[10px] sm:text-xs md:text-sm font-bold bg-emerald-50 text-emerald-700 px-2 py-1 sm:px-3 sm:py-1.5 rounded-xl flex items-center gap-1.5 border border-emerald-100";
                    dot.className = "w-1.5 h-1.5 sm:w-2 sm:h-2 bg-emerald-500 rounded-full";
                    text.textContent = "Online";
                });
            } else {
                db.sync_queue.count().then(count => {
                    badge.className = "text-[10px] sm:text-xs md:text-sm font-bold bg-amber-50 text-amber-700 px-2 py-1 sm:px-3 sm:py-1.5 rounded-xl flex items-center gap-1.5 border border-amber-100";
                    dot.className = "w-1.5 h-1.5 sm:w-2 sm:h-2 bg-amber-500 rounded-full animate-ping";
                    text.textContent = count > 0 ? `Offline (${count} pending)` : "Offline";
                }).catch(err => {
                    console.error("Dexie sync queue count failed:", err);
                    badge.className = "text-[10px] sm:text-xs md:text-sm font-bold bg-amber-50 text-amber-700 px-2 py-1 sm:px-3 sm:py-1.5 rounded-xl flex items-center gap-1.5 border border-amber-100";
                    dot.className = "w-1.5 h-1.5 sm:w-2 sm:h-2 bg-amber-500 rounded-full animate-ping";
                    text.textContent = "Offline";
                });
            }
        }

        let isSyncing = false;
        async function triggerSync() {
            if (isSyncing || !navigator.onLine) return;
            
            try {
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
                            // Break sync loop on logic failure to preserve serial ordering
                            break;
                        }
                    } catch (err) {
                        console.error("Network sync fetch failed:", err);
                        break; 
                    }
                }
            } catch (err) {
                console.error("Dexie queue query failed:", err);
            }

            isSyncing = false;
            updateConnectionStatus();
            
            // Re-fetch dashboard metrics once fully synced
            if (typeof refreshDashboard === 'function') {
                refreshDashboard();
            }
        }

        window.addEventListener('online', () => {
            updateConnectionStatus();
            triggerSync();
        });
        window.addEventListener('offline', updateConnectionStatus);
        
        // Run checks on load using readyState check to avoid race conditions
        function initApp() {
            updateConnectionStatus();
            triggerSync();
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initApp);
        } else {
            initApp();
        }
    </script>
    @yield('scripts')
</body>
</html>
