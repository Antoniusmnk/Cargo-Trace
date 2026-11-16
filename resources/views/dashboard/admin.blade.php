<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-950">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Control Center — CargoTrace</title>

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
<body class="min-h-full bg-slate-950 text-slate-100 flex flex-col relative selection:bg-purple-500 selection:text-white">

    <!-- Background Accents -->
    <div class="fixed inset-0 cargo-grid-pattern pointer-events-none"></div>
    <div class="fixed top-0 left-1/2 -translate-x-1/2 w-[900px] h-[400px] bg-gradient-to-b from-purple-600/10 via-indigo-500/5 to-transparent blur-3xl pointer-events-none"></div>

    <!-- Top Navigation -->
    <nav class="relative z-20 border-b border-slate-800/80 bg-slate-900/80 backdrop-blur-md">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <!-- Brand -->
                <div class="flex items-center gap-3">
                    <a href="{{ url('/') }}" class="flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-lg bg-gradient-to-tr from-purple-600 to-indigo-500 flex items-center justify-center shadow-md shadow-purple-500/20">
                            <svg class="w-5 h-5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M2 21c.6.5 1.2 1 2.5 1 2.5 0 2.5-2 5-2 2.5 0 2.5 2 5 2 2.5 0 2.5-2 5-2 1.3 0 1.9.5 2.5 1" />
                                <path d="M19.38 20A11.6 11.6 0 0 0 21 14l-9-4-9 4c0 2.9.94 5.34 2.81 7.15" />
                                <path d="M12 10V4" />
                                <path d="m8 7 4-3 4 3" />
                                <rect x="7" y="11" width="4" height="3" rx="0.5" />
                                <rect x="13" y="11" width="4" height="3" rx="0.5" />
                            </svg>
                        </div>
                        <span class="text-lg font-extrabold tracking-tight text-white">Cargo<span class="text-purple-400">Trace</span></span>
                    </a>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold uppercase tracking-wider bg-purple-500/10 text-purple-300 border border-purple-500/30">
                        <span class="w-1.5 h-1.5 rounded-full bg-purple-400 animate-pulse"></span>
                        Admin Area
                    </span>

                    <div class="hidden lg:flex items-center gap-2 ml-2 text-xs font-semibold">
                        <a href="{{ route('admin.shipments') }}" class="px-3 py-1 rounded-lg text-slate-300 hover:text-white hover:bg-slate-800 transition">
                            Data Shipments
                        </a>
                        <a href="{{ route('admin.carriers') }}" class="px-3 py-1 rounded-lg text-slate-300 hover:text-white hover:bg-slate-800 transition">
                            Master Carriers
                        </a>
                        <a href="{{ route('admin.locations') }}" class="px-3 py-1 rounded-lg text-slate-300 hover:text-white hover:bg-slate-800 transition">
                            Master Locations
                        </a>
                    </div>
                </div>

                <!-- Navigation links & User actions -->
                <div class="flex items-center gap-4">
                    <a href="{{ route('operator.dashboard') }}" class="hidden md:inline-flex items-center gap-1.5 text-xs font-semibold px-3 py-1.5 rounded-lg bg-slate-800 text-slate-300 hover:text-white hover:bg-slate-700 transition">
                        <span>Lihat Workspace Operator &rarr;</span>
                    </a>

                    <div class="hidden sm:flex flex-col text-right">
                        <span class="text-sm font-bold text-white">{{ Auth::user()->name }}</span>
                        <span class="text-xs text-purple-300">{{ Auth::user()->email }}</span>
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
        <div class="rounded-2xl bg-gradient-to-r from-purple-950/40 via-slate-900 to-indigo-950/30 border border-purple-900/40 p-6 sm:p-8 shadow-xl">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div>
                    <div class="inline-flex items-center gap-2 text-xs font-bold text-purple-400 bg-purple-500/10 border border-purple-500/20 px-3 py-1 rounded-full mb-3">
                        <span class="w-2 h-2 rounded-full bg-purple-400"></span>
                        ROLE: ADMINISTRATOR UTAMA
                    </div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">
                        Administrator Control Center
                    </h1>
                    <p class="mt-2 text-sm text-slate-300 max-w-3xl leading-relaxed">
                        Pusat kendali operasional platform CargoTrace. Kelola akun pengguna, konfigurasi sistem, master data carrier logistik, titik lokasi pelabuhan, serta pantau audit log keamanan.
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <div class="bg-slate-900/80 border border-purple-800/40 rounded-xl p-4 text-center min-w-[140px]">
                        <span class="block text-xs uppercase tracking-wider text-slate-400 font-semibold">Status Sesi</span>
                        <span class="block text-base font-extrabold text-emerald-400 mt-1">ACTIVE</span>
                    </div>
                    <div class="bg-slate-900/80 border border-purple-800/40 rounded-xl p-4 text-center min-w-[140px]">
                        <span class="block text-xs uppercase tracking-wider text-slate-400 font-semibold">Tingkat Hak</span>
                        <span class="block text-base font-extrabold text-purple-400 mt-1">FULL ADMIN</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Metric & Feature Panels -->
        <div class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-6">
            
            <!-- Card 1: User & Role Management -->
            <div class="p-6 rounded-2xl bg-slate-900/70 border border-slate-800 backdrop-blur-sm">
                <div class="w-10 h-10 rounded-xl bg-purple-500/10 text-purple-400 flex items-center justify-center mb-4">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                </div>
                <h3 class="text-base font-bold text-white">Manajemen Akun & Role</h3>
                <p class="mt-2 text-xs text-slate-400 leading-relaxed">
                    Pengendalian akun Administrator, Logistics Operator, dan Customer. Pengaturan status akun (Active / Suspended).
                </p>
                <div class="mt-4 pt-4 border-t border-slate-800 text-xs text-purple-300 font-medium">
                    RBAC Active &bull; 3 Akun Demo Terdaftar
                </div>
            </div>

            <!-- Card 2: Carrier & Location Master Data -->
            <div class="p-6 rounded-2xl bg-slate-900/70 border border-slate-800 backdrop-blur-sm">
                <div class="w-10 h-10 rounded-xl bg-indigo-500/10 text-indigo-400 flex items-center justify-center mb-4">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                </div>
                <h3 class="text-base font-bold text-white">Carrier & Location Master</h3>
                <p class="mt-2 text-xs text-slate-400 leading-relaxed">
                    Data master perusahaan pelayaran (Maersk, MSC, Evergreen) serta koordinat pelabuhan asal dan tujuan (Milestone P9).
                </p>
                <div class="mt-4 pt-4 border-t border-slate-800 flex items-center justify-between text-xs">
                    <a href="{{ route('admin.carriers') }}" class="text-indigo-400 hover:text-indigo-300 font-semibold transition">
                        Kelola Carriers &rarr;
                    </a>
                    <a href="{{ route('admin.locations') }}" class="text-indigo-400 hover:text-indigo-300 font-semibold transition">
                        Kelola Locations &rarr;
                    </a>
                </div>
            </div>

            <!-- Card 3: Security & Audit Log -->
            <div class="p-6 rounded-2xl bg-slate-900/70 border border-slate-800 backdrop-blur-sm">
                <div class="w-10 h-10 rounded-xl bg-rose-500/10 text-rose-400 flex items-center justify-center mb-4">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                </div>
                <h3 class="text-base font-bold text-white">Audit Log & Keamanan</h3>
                <p class="mt-2 text-xs text-slate-400 leading-relaxed">
                    Pencatatan jejak audit sistem: aktor, waktu, IP address, dan aksi modifikasi data penting (Milestone P13).
                </p>
                <div class="mt-4 pt-4 border-t border-slate-800 text-xs text-slate-500 font-medium">
                    Security Logging Baseline Ready
                </div>
            </div>

        </div>

        <!-- Global Oversight Summary Placeholder -->
        <div class="mt-8 p-6 rounded-2xl bg-slate-900/50 border border-slate-800">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h2 class="text-base font-bold text-white">Ringkasan Visibilitas Global Platform</h2>
                    <p class="mt-1 text-xs text-slate-400">Administrator memiliki akses pengawasan menyeluruh terhadap seluruh pengiriman lintas operator dan pelanggan.</p>
                </div>
                <a href="{{ route('admin.shipments') }}" class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-xl text-xs font-semibold text-sky-300 bg-sky-500/10 hover:bg-sky-500/20 border border-sky-500/30 transition">
                    <span>Lihat Seluruh Shipments &rarr;</span>
                </a>
            </div>
            
            <div class="mt-6 grid grid-cols-2 sm:grid-cols-4 gap-4 text-center">
                <div class="bg-slate-950/60 p-4 rounded-xl border border-slate-800/80">
                    <span class="text-xs text-slate-500 font-semibold block uppercase">Total Shipments</span>
                    <span class="text-2xl font-extrabold text-white mt-1 block">--</span>
                </div>
                <div class="bg-slate-950/60 p-4 rounded-xl border border-slate-800/80">
                    <span class="text-xs text-slate-500 font-semibold block uppercase">In Transit</span>
                    <span class="text-2xl font-extrabold text-sky-400 mt-1 block">--</span>
                </div>
                <div class="bg-slate-950/60 p-4 rounded-xl border border-slate-800/80">
                    <span class="text-xs text-slate-500 font-semibold block uppercase">Delayed Alerts</span>
                    <span class="text-2xl font-extrabold text-amber-400 mt-1 block">--</span>
                </div>
                <div class="bg-slate-950/60 p-4 rounded-xl border border-slate-800/80">
                    <span class="text-xs text-slate-500 font-semibold block uppercase">Exceptions Open</span>
                    <span class="text-2xl font-extrabold text-rose-400 mt-1 block">--</span>
                </div>
            </div>
        </div>

    </main>

    <!-- Footer -->
    <footer class="relative z-10 border-t border-slate-800/80 py-6 text-center text-xs text-slate-500">
        &copy; {{ date('Y') }} CargoTrace Platform &bull; Administrator Workspace
    </footer>

</body>
</html>
