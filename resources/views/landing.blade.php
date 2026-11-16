<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-950">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>CargoTrace — Global Shipment Visibility & Tracking Platform</title>

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
<body class="min-h-full bg-slate-950 text-slate-100 flex flex-col relative overflow-x-hidden selection:bg-sky-500 selection:text-white">

    <!-- Background Accents -->
    <div class="fixed inset-0 cargo-grid-pattern pointer-events-none"></div>
    <div class="fixed top-0 left-1/2 -translate-x-1/2 w-[1000px] h-[500px] bg-gradient-to-b from-sky-600/15 via-cyan-500/5 to-transparent blur-3xl pointer-events-none"></div>

    <!-- Top Navigation -->
    <header class="relative z-20 border-b border-slate-800/80 bg-slate-950/70 backdrop-blur-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-sky-600 to-cyan-500 flex items-center justify-center shadow-lg shadow-sky-500/25">
                    <svg class="w-6 h-6 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M2 21c.6.5 1.2 1 2.5 1 2.5 0 2.5-2 5-2 2.5 0 2.5 2 5 2 2.5 0 2.5-2 5-2 1.3 0 1.9.5 2.5 1" />
                        <path d="M19.38 20A11.6 11.6 0 0 0 21 14l-9-4-9 4c0 2.9.94 5.34 2.81 7.15" />
                        <path d="M12 10V4" />
                        <path d="m8 7 4-3 4 3" />
                        <rect x="7" y="11" width="4" height="3" rx="0.5" />
                        <rect x="13" y="11" width="4" height="3" rx="0.5" />
                    </svg>
                </div>
                <div>
                    <span class="text-xl font-extrabold tracking-tight text-white">Cargo<span class="text-sky-400">Trace</span></span>
                    <span class="hidden sm:inline-block ml-2 text-[10px] font-bold uppercase tracking-wider text-sky-400 bg-sky-500/10 border border-sky-500/20 px-2 py-0.5 rounded-full">Global Visibility</span>
                </div>
            </div>

            <!-- Auth Navigation Links -->
            <div class="flex items-center gap-3">
                @auth
                    <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white bg-slate-800 hover:bg-slate-700 border border-slate-700 rounded-xl transition">
                        <span>Dashboard</span>
                        <svg class="w-4 h-4 text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </a>
                @else
                    <a href="{{ route('login') }}" class="px-4 py-2 text-sm font-semibold text-slate-300 hover:text-white transition">
                        Masuk
                    </a>
                    <a href="{{ route('register') }}" class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-bold text-white bg-gradient-to-r from-sky-600 to-sky-500 hover:from-sky-500 hover:to-sky-400 rounded-xl shadow-lg shadow-sky-600/25 transition">
                        <span>Daftar Akun</span>
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                    </a>
                @endauth
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <main class="relative z-10 flex-1 flex items-center justify-center py-20 px-4 sm:px-6 lg:px-8 text-center">
        <div class="max-w-4xl mx-auto">
            <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-slate-900 border border-slate-800 text-xs font-medium text-slate-300 mb-8 shadow-sm">
                <span class="w-2 h-2 rounded-full bg-sky-400 animate-ping"></span>
                <span>Platform Visibilitas Pengiriman & Kargo Maritim Terpadu</span>
            </div>

            <h1 class="text-4xl sm:text-6xl font-extrabold tracking-tight text-white leading-tight">
                Lacak Pengiriman Global <br>
                <span class="bg-gradient-to-r from-sky-400 via-cyan-300 to-sky-500 bg-clip-text text-transparent">
                    Secara Presisi & Real-Time
                </span>
            </h1>

            <p class="mt-6 text-lg sm:text-xl text-slate-400 max-w-2xl mx-auto leading-relaxed">
                Kelola kontainer, rute kapal, riwayat timeline kejadian logistik, deteksi keterlambatan ETA otomatis, dan manajemen pengecualian dalam satu kontrol terpusat.
            </p>

            <div class="mt-10 flex flex-col sm:flex-row items-center justify-center gap-4">
                @auth
                    <a href="{{ route('dashboard') }}" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-8 py-3.5 text-base font-bold text-white bg-gradient-to-r from-sky-600 to-sky-500 hover:from-sky-500 hover:to-sky-400 rounded-xl shadow-xl shadow-sky-600/30 transition transform hover:-translate-y-0.5">
                        <span>Buka Dashboard Operasional</span>
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                    </a>
                @else
                    <a href="{{ route('register') }}" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-8 py-3.5 text-base font-bold text-white bg-gradient-to-r from-sky-600 to-sky-500 hover:from-sky-500 hover:to-sky-400 rounded-xl shadow-xl shadow-sky-600/30 transition transform hover:-translate-y-0.5">
                        <span>Mulai Pelacakan Sekarang</span>
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                    </a>
                    <a href="{{ route('login') }}" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-8 py-3.5 text-base font-semibold text-slate-300 bg-slate-900/90 hover:bg-slate-800 border border-slate-700/80 rounded-xl transition">
                        <span>Masuk ke Akun Anda</span>
                    </a>
                @endauth
            </div>

            <!-- Highlights Grid -->
            <div class="mt-16 grid grid-cols-1 md:grid-cols-3 gap-6 text-left">
                <div class="p-6 rounded-2xl bg-slate-900/60 border border-slate-800/80 backdrop-blur-sm">
                    <div class="w-10 h-10 rounded-lg bg-sky-500/10 text-sky-400 flex items-center justify-center mb-4">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                    </div>
                    <h3 class="text-base font-bold text-white">Role-Based Access</h3>
                    <p class="mt-2 text-sm text-slate-400">Pemisahan otorisasi ketat antara Administrator, Operator Logistik, dan Customer.</p>
                </div>

                <div class="p-6 rounded-2xl bg-slate-900/60 border border-slate-800/80 backdrop-blur-sm">
                    <div class="w-10 h-10 rounded-lg bg-sky-500/10 text-sky-400 flex items-center justify-center mb-4">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h3 class="text-base font-bold text-white">Timeline & ETA Engine</h3>
                    <p class="mt-2 text-sm text-slate-400">Riwayat status kronologis dengan kalkulasi otomatis keterlambatan dan exception.</p>
                </div>

                <div class="p-6 rounded-2xl bg-slate-900/60 border border-slate-800/80 backdrop-blur-sm">
                    <div class="w-10 h-10 rounded-lg bg-sky-500/10 text-sky-400 flex items-center justify-center mb-4">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                        </svg>
                    </div>
                    <h3 class="text-base font-bold text-white">Interactive Map Ready</h3>
                    <p class="mt-2 text-sm text-slate-400">Integrasi peta Leaflet.js rute pelayaran asal ke tujuan beserta cuaca pelabuhan.</p>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="relative z-10 border-t border-slate-800/80 py-8 text-center text-xs text-slate-500">
        &copy; {{ date('Y') }} CargoTrace (Global Shipment Visibility & Tracking Platform). Built for Web Programming 2 UAS.
    </footer>

</body>
</html>
