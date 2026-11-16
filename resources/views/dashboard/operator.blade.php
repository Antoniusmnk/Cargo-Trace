<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-950">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Operator Workspace — CargoTrace</title>

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
<body class="min-h-full bg-slate-950 text-slate-100 flex flex-col relative selection:bg-cyan-500 selection:text-white">

    <!-- Background Accents -->
    <div class="fixed inset-0 cargo-grid-pattern pointer-events-none"></div>
    <div class="fixed top-0 left-1/2 -translate-x-1/2 w-[900px] h-[400px] bg-gradient-to-b from-cyan-600/10 via-sky-500/5 to-transparent blur-3xl pointer-events-none"></div>

    <!-- Top Navigation -->
    <nav class="relative z-20 border-b border-slate-800/80 bg-slate-900/80 backdrop-blur-md">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <!-- Brand -->
                <div class="flex items-center gap-3">
                    <a href="{{ url('/') }}" class="flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-lg bg-gradient-to-tr from-cyan-600 to-sky-500 flex items-center justify-center shadow-md shadow-cyan-500/20">
                            <svg class="w-5 h-5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M2 21c.6.5 1.2 1 2.5 1 2.5 0 2.5-2 5-2 2.5 0 2.5 2 5 2 2.5 0 2.5-2 5-2 1.3 0 1.9.5 2.5 1" />
                                <path d="M19.38 20A11.6 11.6 0 0 0 21 14l-9-4-9 4c0 2.9.94 5.34 2.81 7.15" />
                                <path d="M12 10V4" />
                                <path d="m8 7 4-3 4 3" />
                                <rect x="7" y="11" width="4" height="3" rx="0.5" />
                                <rect x="13" y="11" width="4" height="3" rx="0.5" />
                            </svg>
                        </div>
                        <span class="text-lg font-extrabold tracking-tight text-white">Cargo<span class="text-cyan-400">Trace</span></span>
                    </a>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold uppercase tracking-wider bg-cyan-500/10 text-cyan-300 border border-cyan-500/30">
                        <span class="w-1.5 h-1.5 rounded-full bg-cyan-400 animate-pulse"></span>
                        Operator Area
                    </span>

                    <div class="hidden lg:flex items-center gap-2 ml-2 text-xs font-semibold">
                        <a href="{{ route('operator.carriers') }}" class="px-3 py-1 rounded-lg text-slate-300 hover:text-white hover:bg-slate-800 transition">
                            Master Carriers
                        </a>
                        <a href="{{ route('operator.locations') }}" class="px-3 py-1 rounded-lg text-slate-300 hover:text-white hover:bg-slate-800 transition">
                            Master Locations
                        </a>
                    </div>
                </div>

                <!-- Navigation & User actions -->
                <div class="flex items-center gap-4">
                    <div class="hidden sm:flex flex-col text-right">
                        <span class="text-sm font-bold text-white">{{ Auth::user()->name }}</span>
                        <span class="text-xs text-cyan-300">{{ Auth::user()->email }}</span>
                    </div>

                    <!-- Logout Button Form -->
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit" 
                                class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl text-xs font-semibold text-rose-300 bg-rose-500/10 hover:bg-rose-500/20 border border-rose-500/20 transition cursor-pointer"
                                title="Keluar dari akun">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                            <span class="hidden sm:inline">Keluar</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="relative z-10 flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8">
        
        <!-- Welcome Hero Banner -->
        <div class="rounded-2xl bg-gradient-to-r from-cyan-950/40 via-slate-900 to-sky-950/30 border border-cyan-900/40 p-6 sm:p-8 shadow-xl">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div>
                    <div class="inline-flex items-center gap-2 text-xs font-bold text-cyan-400 bg-cyan-500/10 border border-cyan-500/20 px-3 py-1 rounded-full mb-3">
                        <span class="w-2 h-2 rounded-full bg-cyan-400"></span>
                        ROLE: LOGISTICS OPERATOR
                    </div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">
                        Logistics Operations Workspace
                    </h1>
                    <p class="mt-2 text-sm text-slate-300 max-w-3xl leading-relaxed">
                        Workspace terpusat untuk memproses shipment kargo, memperbarui status pengiriman, mencatat tracking events pelayaran, serta menangani insiden keterlambatan & exception logistik.
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <div class="bg-slate-900/80 border border-cyan-800/40 rounded-xl p-4 text-center min-w-[140px]">
                        <span class="block text-xs uppercase tracking-wider text-slate-400 font-semibold">Tugas Operasional</span>
                        <span class="block text-base font-extrabold text-cyan-400 mt-1">READY</span>
                    </div>
                    <div class="bg-slate-900/80 border border-cyan-800/40 rounded-xl p-4 text-center min-w-[140px]">
                        <span class="block text-xs uppercase tracking-wider text-slate-400 font-semibold">Otorisasi</span>
                        <span class="block text-base font-extrabold text-white mt-1">DISPATCHER</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Metric & Feature Panels -->
        <div class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-6">
            
            <!-- Card 1: Shipment Lifecycle -->
            <div class="p-6 rounded-2xl bg-slate-900/70 border border-slate-800 backdrop-blur-sm">
                <div class="w-10 h-10 rounded-xl bg-cyan-500/10 text-cyan-400 flex items-center justify-center mb-4">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                </div>
                <h3 class="text-base font-bold text-white">Siklus Hidup Shipment</h3>
                <p class="mt-2 text-xs text-slate-400 leading-relaxed">
                    Pengendalian alur status baku: <span class="font-mono text-cyan-300">CREATED &rarr; BOOKED &rarr; PICKED_UP &rarr; IN_TRANSIT &rarr; DELIVERED</span>.
                </p>
                <div class="mt-4 pt-4 border-t border-slate-800 text-xs text-cyan-300 font-medium">
                    State Machine Validation Baseline (P9)
                </div>
            </div>

            <!-- Card 2: Tracking Event Dispatcher -->
            <div class="p-6 rounded-2xl bg-slate-900/70 border border-slate-800 backdrop-blur-sm">
                <div class="w-10 h-10 rounded-xl bg-sky-500/10 text-sky-400 flex items-center justify-center mb-4">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h3 class="text-base font-bold text-white">Pencatatan Tracking Event</h3>
                <p class="mt-2 text-xs text-slate-400 leading-relaxed">
                    Pencatatan milestone historis kontainer kargo dengan verifikasi urutan waktu (timestamp) dan koordinat GPS pelabuhan.
                </p>
                <div class="mt-4 pt-4 border-t border-slate-800 text-xs text-slate-500 font-medium">
                    Tracking Service Prepared (P10)
                </div>
            </div>

            <!-- Card 3: Exception & Delay Resolution -->
            <div class="p-6 rounded-2xl bg-slate-900/70 border border-slate-800 backdrop-blur-sm">
                <div class="w-10 h-10 rounded-xl bg-amber-500/10 text-amber-400 flex items-center justify-center mb-4">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <h3 class="text-base font-bold text-white">Resolusi Exception & Delay</h3>
                <p class="mt-2 text-xs text-slate-400 leading-relaxed">
                    Investigasi kendala operasional: <span class="font-mono text-amber-300">CUSTOMS_HOLD, PORT_CONGESTION, WEATHER_DELAY</span>.
                </p>
                <div class="mt-4 pt-4 border-t border-slate-800 text-xs text-slate-500 font-medium">
                    Configurable Delay Thresholds (P11)
                </div>
            </div>

        </div>

        <!-- Active Operations Queue Placeholder -->
        <div class="mt-8 p-6 rounded-2xl bg-slate-900/50 border border-slate-800">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-base font-bold text-white">Antrean Operasional Pengiriman Aktif</h2>
                    <p class="mt-1 text-xs text-slate-400">Daftar muatan kontainer dan jadwal keberangkatan kapal yang membutuhkan pembaruan tracking event.</p>
                </div>
                <span class="text-xs font-semibold px-3 py-1 rounded-full bg-slate-800 text-cyan-300 border border-slate-700">
                    Live Feed Ready
                </span>
            </div>
            
            <div class="mt-6 p-8 border border-dashed border-slate-800 rounded-xl text-center text-xs text-slate-500">
                <svg class="w-8 h-8 mx-auto text-slate-600 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
                Belum ada antrean baru. Modul pembuatan shipment akan dikerjakan pada Milestone P9.
            </div>
        </div>

    </main>

    <!-- Footer -->
    <footer class="relative z-10 border-t border-slate-800/80 py-6 text-center text-xs text-slate-500">
        &copy; {{ date('Y') }} CargoTrace Platform &bull; Logistics Operations Center
    </footer>

</body>
</html>
