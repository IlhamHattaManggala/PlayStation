<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Halaman Tidak Ditemukan | PlayStation Rental</title>

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

        .neon-shadow-blue {
            filter: drop-shadow(0 0 15px rgba(59, 130, 246, 0.6));
        }

        .neon-text-pink {
            text-shadow: 0 0 10px rgba(244, 63, 94, 0.8), 0 0 20px rgba(244, 63, 94, 0.4);
        }

        .neon-border-blue {
            box-shadow: 0 0 15px rgba(59, 130, 246, 0.4), inset 0 0 15px rgba(59, 130, 246, 0.2);
        }

        .controller-animate {
            animation: float 4s ease-in-out infinite;
        }

        @keyframes float {

            0%,
            100% {
                transform: translateY(0) rotate(0deg);
            }

            50% {
                transform: translateY(-12px) rotate(-3deg);
            }
        }

        .btn-glow:hover {
            box-shadow: 0 0 25px rgba(99, 102, 241, 0.8);
            transform: scale(1.03);
        }
    </style>
</head>

<body class="bg-[#0b0f19] text-slate-200 min-h-screen flex items-center justify-center overflow-hidden relative">

    <!-- Glowing Background Shapes -->
    <div class="absolute w-[300px] h-[300px] rounded-full bg-blue-600/10 blur-[120px] top-10 left-10"></div>
    <div class="absolute w-[350px] h-[350px] rounded-full bg-rose-600/10 blur-[130px] bottom-10 right-10"></div>
    <div
        class="absolute w-[200px] h-[200px] rounded-full bg-indigo-600/10 blur-[100px] top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2">
    </div>

    <div class="max-w-md w-full px-6 text-center relative z-10">

        <!-- PlayStation Controller Icon / Graphic -->
        <div class="mb-8 relative flex justify-center">
            <div
                class="w-32 h-32 rounded-3xl bg-slate-900/50 border border-blue-500/30 flex items-center justify-center neon-border-blue controller-animate">
                <i class="fa-solid fa-gamepad text-6xl text-blue-500 neon-shadow-blue"></i>
            </div>
            <!-- Floating PlayStation Shapes -->
            <i class="fa-regular fa-circle absolute text-pink-500/40 text-2xl top-0 left-16 animate-bounce"
                style="animation-duration: 3s;"></i>
            <i class="fa-solid fa-xmark absolute text-blue-400/40 text-2xl bottom-4 right-14 animate-pulse"
                style="animation-duration: 2s;"></i>
            <i class="fa-solid fa-triangle-exclamation absolute text-emerald-400/40 text-xl top-4 right-16 animate-bounce"
                style="animation-duration: 4s;"></i>
            <i class="fa-regular fa-square absolute text-amber-400/40 text-2xl bottom-2 left-12 animate-pulse"
                style="animation-duration: 3.5s;"></i>
        </div>

        <!-- Error Code Title -->
        <h1 class="text-9xl font-black tracking-widest text-rose-500 neon-text-pink leading-none">404</h1>

        <!-- Heading -->
        <h2 class="text-2xl font-extrabold text-white mt-4 uppercase tracking-wider">Game Over / Level Not Found</h2>

        <!-- Description -->
        <p class="text-sm text-slate-400 mt-3 leading-relaxed max-w-sm mx-auto">
            Ups! Halaman yang Anda cari telah berpindah dimensi atau tidak pernah ada di server kami. Silakan kembali ke
            lobi utama.
        </p>
    </div>

</body>

</html>