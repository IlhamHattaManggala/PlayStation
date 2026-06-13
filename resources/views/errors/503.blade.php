<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>503 - Sistem Sedang Pemeliharaan | PlayStation Rental</title>
    
    <!-- Google Fonts & Tailwind CDN -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800;900&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        body {
            font-family: 'Outfit', sans-serif;
        }
        .neon-shadow-amber {
            filter: drop-shadow(0 0 15px rgba(245, 158, 11, 0.6));
        }
        .neon-text-amber {
            text-shadow: 0 0 10px rgba(245, 158, 11, 0.8), 0 0 20px rgba(245, 158, 11, 0.4);
        }
        .neon-border-amber {
            box-shadow: 0 0 15px rgba(245, 158, 11, 0.3), inset 0 0 15px rgba(245, 158, 11, 0.15);
        }
        .pulse-slow {
            animation: pulse-glow 2.5s infinite ease-in-out;
        }
        @keyframes pulse-glow {
            0%, 100% { opacity: 0.5; box-shadow: 0 0 10px rgba(245, 158, 11, 0.2); }
            50% { opacity: 1; box-shadow: 0 0 25px rgba(245, 158, 11, 0.7); }
        }
        .gear-rotate {
            animation: spin-clockwise 10s linear infinite;
        }
        .gear-rotate-counter {
            animation: spin-counter 8s linear infinite;
        }
        @keyframes spin-clockwise {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        @keyframes spin-counter {
            0% { transform: rotate(360deg); }
            100% { transform: rotate(0deg); }
        }
    </style>
</head>
<body class="bg-[#0b0f19] text-slate-200 min-h-screen flex items-center justify-center overflow-hidden relative">

    <!-- Glowing Background Shapes -->
    <div class="absolute w-[300px] h-[300px] rounded-full bg-amber-600/10 blur-[120px] top-10 right-10"></div>
    <div class="absolute w-[350px] h-[350px] rounded-full bg-yellow-600/5 blur-[135px] bottom-10 left-10"></div>

    <div class="max-w-md w-full px-6 text-center relative z-10">
        
        <!-- Rotating Gears / Maintenance Graphics -->
        <div class="mb-8 relative flex justify-center items-center h-28">
            <div class="absolute w-20 h-20 rounded-full bg-slate-900/80 border border-amber-500/30 flex items-center justify-center neon-border-amber gear-rotate z-10">
                <i class="fa-solid fa-gear text-4xl text-amber-500 neon-shadow-amber"></i>
            </div>
            <div class="absolute w-12 h-12 rounded-full bg-slate-900/60 border border-amber-500/20 flex items-center justify-center gear-rotate-counter left-[40%] top-2">
                <i class="fa-solid fa-gears text-xl text-amber-600/60"></i>
            </div>
        </div>

        <!-- System Paused Title -->
        <div class="inline-flex items-center gap-2 px-3 py-1 bg-amber-500/10 border border-amber-500/30 text-amber-500 rounded-full text-xs font-bold uppercase tracking-wider mb-4">
            <span class="w-2 h-2 rounded-full bg-amber-500 pulse-slow"></span>
            System Paused / Maintenance
        </div>

        <h1 class="text-4xl font-extrabold text-white uppercase tracking-wide">Pemeliharaan Sistem</h1>
        
        <!-- Description -->
        <p class="text-sm text-slate-400 mt-4 leading-relaxed max-w-sm mx-auto">
            Kami sedang melakukan pembaruan rutin dan optimalisasi database server untuk memberikan pengalaman rental PlayStation yang lebih responsif dan lancar. 
        </p>

        <!-- Activities Check List -->
        <div class="bg-slate-900/40 border border-slate-800/80 rounded-2xl p-4 mt-6 max-w-xs mx-auto text-left space-y-3">
            <div class="flex items-center gap-3 text-xs text-slate-400">
                <i class="fa-solid fa-circle-check text-emerald-500"></i>
                <span>Cadangan Basis Data (Database Backup)</span>
            </div>
            <div class="flex items-center gap-3 text-xs text-slate-400">
                <i class="fa-solid fa-spinner animate-spin text-amber-500"></i>
                <span class="text-slate-300 font-semibold">Sinkronisasi & Optimalisasi Server</span>
            </div>
            <div class="flex items-center gap-3 text-xs text-slate-400">
                <i class="fa-solid fa-clock text-slate-600"></i>
                <span>Pengecekan Akhir Sistem Operasional</span>
            </div>
        </div>

        <!-- Refresh Button -->
        <div class="mt-8">
            <button onclick="window.location.reload()" class="inline-flex items-center gap-2 px-6 py-2.5 bg-slate-900 hover:bg-slate-800 border border-slate-700 hover:border-slate-600 text-white font-bold rounded-xl text-xs transition-all duration-200 active:scale-[0.98]">
                <i class="fa-solid fa-arrows-rotate"></i> Muat Ulang Halaman
            </button>
        </div>
    </div>

</body>
</html>
