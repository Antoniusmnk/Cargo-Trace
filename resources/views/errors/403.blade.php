<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-950">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>403 - Akses Dilarang | {{ config('app.name', 'CargoTrace') }}</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Styles & Scripts -->
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <script src="https://cdn.tailwindcss.com"></script>
        <script>
            tailwind.config = {
                theme: {
                    extend: {
                        fontFamily: {
                            sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                        }
                    }
                }
            }
        </script>
    @endif

    <style>
        body { font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif; }
        .cargo-grid-pattern {
            background-size: 40px 40px;
            background-image: 
                linear-gradient(to right, rgba(255, 255, 255, 0.03) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(255, 255, 255, 0.03) 1px, transparent 1px);
        }
    </style>
</head>
<body class="min-h-full bg-slate-950 text-slate-100 flex flex-col justify-center items-center px-4 sm:px-6 lg:px-8 relative overflow-x-hidden selection:bg-rose-500 selection:text-white">

    <!-- Background Accents -->
    <div class="fixed inset-0 cargo-grid-pattern pointer-events-none"></div>
    <div class="fixed top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[400px] bg-rose-600/10 blur-3xl rounded-full pointer-events-none"></div>

    <div class="max-w-md w-full text-center relative z-10">
        <!-- Shield Lock Warning Icon -->
        <div class="mx-auto w-20 h-20 rounded-2xl bg-rose-500/10 border border-rose-500/30 flex items-center justify-center shadow-2xl shadow-rose-900/30 mb-6">
            <svg class="w-10 h-10 text-rose-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
            </svg>
        </div>

        <!-- Error Code & Title -->
        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider text-rose-400 bg-rose-500/10 border border-rose-500/20 mb-4">
            <span class="w-1.5 h-1.5 rounded-full bg-rose-500 animate-ping"></span>
            HTTP 403 Forbidden
        </span>

        <h1 class="text-3xl sm:text-4xl font-extrabold text-white tracking-tight">
            403 - Akses Dilarang
        </h1>

        <p class="mt-3 text-base text-slate-300 font-medium">
            Wewenang akun Anda tidak mencukupi untuk membuka resource ini.
        </p>

        @if (!empty($exception?->getMessage()))
            <div class="mt-4 p-3 rounded-xl bg-slate-900/90 border border-slate-800 text-xs text-rose-300 font-mono">
                {{ $exception->getMessage() }}
            </div>
        @endif

        <p class="mt-3 text-xs text-slate-500">
            Akses area ini dibatasi berdasarkan otorisasi peran (RBAC) CargoTrace.
        </p>

        <!-- Back to Dashboard Button -->
        <div class="mt-8 flex flex-col sm:flex-row items-center justify-center gap-3">
            <a href="{{ route('dashboard') }}" 
               class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl text-sm font-bold text-white bg-gradient-to-r from-sky-600 to-sky-500 hover:from-sky-500 hover:to-sky-400 shadow-lg shadow-sky-600/25 transition-all">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
                <span>Kembali ke Dashboard Saya</span>
            </a>
            
            <a href="{{ url('/') }}" 
               class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl text-sm font-semibold text-slate-300 bg-slate-900 hover:bg-slate-800 border border-slate-800 transition-all">
                <span>Ke Beranda</span>
            </a>
        </div>

        <div class="mt-12 text-xs text-slate-600">
            &copy; {{ date('Y') }} CargoTrace Platform. All rights reserved.
        </div>
    </div>

</body>
</html>
