<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - {{ $settings->app_name ?? 'Rental PlayStation' }}</title>
    @if($settings && $settings->favicon)
        <link rel="icon" type="image/*" href="{{ $settings->favicon_url }}">
    @else
        <link rel="icon" type="image/png" href="https://cdn-icons-png.flaticon.com/512/869/869045.png">
    @endif
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
    </style>
</head>
<body class="bg-slate-50 min-h-screen flex items-center justify-center p-4 selection:bg-slate-900 selection:text-white">

    <div class="w-full max-w-md bg-white rounded-2xl border border-slate-100 shadow-xl shadow-slate-100/50 p-8 space-y-6 transition-all duration-300 hover:shadow-2xl hover:shadow-slate-200/50">
        
        <!-- Header -->
        <div class="text-center space-y-2">
            @if($settings && $settings->logo)
                <img src="{{ $settings->logo_url }}" alt="Logo" class="h-16 mx-auto object-contain mb-4">
            @else
                <div class="w-16 h-16 mx-auto bg-slate-900 text-white rounded-2xl flex items-center justify-center font-bold text-2xl shadow-lg mb-4">
                    PS
                </div>
            @endif
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">
                {{ $settings->app_name ?? 'Rental PlayStation' }}
            </h1>
            <p class="text-sm text-slate-500 font-medium">
                {{ $settings->description ?? 'Kelola rental PlayStation Anda dengan mudah.' }}
            </p>
        </div>

        @if($errors->any())
            <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded-xl text-sm space-y-1" role="alert">
                @foreach($errors->all() as $error)
                    <p class="font-medium">{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <!-- Form -->
        <form action="{{ route('login') }}" method="POST" class="space-y-5">
            @csrf
            
            <div class="space-y-1.5">
                <label for="email" class="text-sm font-semibold text-slate-700">Email Address</label>
                <div class="relative">
                    <input 
                        type="email" 
                        name="email" 
                        id="email" 
                        required 
                        value="{{ old('email') }}" 
                        placeholder="nama@email.com" 
                        class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50 text-slate-900 placeholder:text-slate-400 focus:bg-white focus:border-slate-900 focus:ring-1 focus:ring-slate-900 outline-none transition-all duration-200 text-sm font-medium"
                    >
                </div>
            </div>

            <div class="space-y-1.5">
                <div class="flex justify-between items-center">
                    <label for="password" class="text-sm font-semibold text-slate-700">Password</label>
                </div>
                <div class="relative">
                    <input 
                        type="password" 
                        name="password" 
                        id="password" 
                        required 
                        placeholder="••••••••" 
                        class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50 text-slate-900 placeholder:text-slate-400 focus:bg-white focus:border-slate-900 focus:ring-1 focus:ring-slate-900 outline-none transition-all duration-200 text-sm font-medium"
                    >
                </div>
            </div>

            <div class="flex items-center justify-between pt-1">
                <label class="relative flex items-center cursor-pointer select-none">
                    <input type="checkbox" name="remember" class="sr-only peer">
                    <div class="w-9 h-5 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-slate-900"></div>
                    <span class="ml-3 text-xs font-semibold text-slate-600">Ingat Saya</span>
                </label>
            </div>

            <button 
                type="submit" 
                class="w-full py-3 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-sm font-semibold shadow-lg shadow-slate-900/10 hover:shadow-xl hover:shadow-slate-900/20 active:scale-[0.98] transition-all duration-200"
            >
                Masuk ke Dashboard
            </button>
        </form>

        <!-- Footer Info -->
        <div class="text-center text-xs text-slate-400 font-medium pt-2 border-t border-slate-100">
            &copy; {{ date('Y') }} {{ $settings->app_name ?? 'Rental PlayStation' }}. All rights reserved.
        </div>

    </div>

</body>
</html>
