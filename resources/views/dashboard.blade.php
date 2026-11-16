<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-950">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard — CargoTrace</title>

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
<body class="min-h-full bg-slate-950 text-slate-100 flex flex-col relative selection:bg-sky-500 selection:text-white">

    <!-- Background Accents -->
    <div class="fixed inset-0 cargo-grid-pattern pointer-events-none"></div>
    <div class="fixed top-0 left-1/2 -translate-x-1/2 w-[900px] h-[400px] bg-gradient-to-b from-sky-600/10 via-cyan-500/5 to-transparent blur-3xl pointer-events-none"></div>

    <!-- Navigation Bar -->
    <nav class="relative z-20 border-b border-slate-800/80 bg-slate-900/80 backdrop-blur-md">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <!-- Brand -->
                <div class="flex items-center gap-3">
                    <a href="{{ url('/') }}" class="flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-lg bg-gradient-to-tr from-sky-600 to-cyan-500 flex items-center justify-center shadow-md shadow-sky-500/20">
                            <svg class="w-5 h-5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M2 21c.6.5 1.2 1 2.5 1 2.5 0 2.5-2 5-2 2.5 0 2.5 2 5 2 2.5 0 2.5-2 5-2 1.3 0 1.9.5 2.5 1" />
                                <path d="M19.38 20A11.6 11.6 0 0 0 21 14l-9-4-9 4c0 2.9.94 5.34 2.81 7.15" />
                                <path d="M12 10V4" />
                                <path d="m8 7 4-3 4 3" />
                                <rect x="7" y="11" width="4" height="3" rx="0.5" />
                                <rect x="13" y="11" width="4" height="3" rx="0.5" />
                            </svg>
                        </div>
                        <span class="text-lg font-extrabold tracking-tight text-white">Cargo<span class="text-sky-400">Trace</span></span>
                    </a>
                    <span class="hidden md:inline-block text-xs font-semibold px-2 py-0.5 rounded-full bg-slate-800 text-slate-400 border border-slate-700">
                        Portal Operasional
                    </span>
                </div>

                <!-- User Profile & Logout -->
                <div class="flex items-center gap-4">
                    <div class="hidden sm:flex flex-col text-right">
                        <span class="text-sm font-bold text-white">{{ Auth::user()->name }}</span>
                        <span class="text-xs text-slate-400">{{ Auth::user()->email }}</span>
                    </div>

                    <!-- Role Badge -->
                    @php
                        $role = Auth::user()->role;
                        $roleClasses = match($role) {
                            'admin' => 'bg-purple-500/10 text-purple-300 border-purple-500/30',
                            'operator' => 'bg-amber-500/10 text-amber-300 border-amber-500/30',
                            default => 'bg-sky-500/10 text-sky-300 border-sky-500/30',
                        };
                    @endphp
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider border {{ $roleClasses }}">
                        <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                        {{ $role }}
                    </span>

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
    <main class="relative z-10 flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-10">
        
        <!-- Welcome Banner -->
        <div class="rounded-2xl bg-gradient-to-r from-slate-900 via-slate-900 to-sky-950/40 border border-slate-800 p-6 sm:p-8 shadow-xl relative overflow-hidden">
            <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div>
                    <div class="inline-flex items-center gap-2 text-xs font-semibold text-sky-400 bg-sky-500/10 border border-sky-500/20 px-3 py-1 rounded-full mb-3">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                        Status Akun: {{ strtoupper(Auth::user()->status) }}
                    </div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">
                        Selamat Datang, {{ Auth::user()->name }}! 👋
                    </h1>
                    <p class="mt-2 text-sm text-slate-400 max-w-2xl leading-relaxed">
                        Anda telah berhasil masuk ke sistem CargoTrace. Sebagai pengguna dengan peran 
                        <span class="font-semibold text-sky-300 uppercase">{{ Auth::user()->role }}</span>, 
                        Anda memiliki hak akses sesuai batas otorisasi platform.
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <div class="bg-slate-950/60 border border-slate-800 rounded-xl p-4 text-center min-w-[130px]">
                        <span class="block text-xs uppercase tracking-wider text-slate-500 font-semibold">User ID</span>
                        <span class="block text-xl font-extrabold text-white mt-0.5">#{{ Auth::user()->id }}</span>
                    </div>
                    <div class="bg-slate-950/60 border border-slate-800 rounded-xl p-4 text-center min-w-[130px]">
                        <span class="block text-xs uppercase tracking-wider text-slate-500 font-semibold">Role Sistem</span>
                        <span class="block text-base font-extrabold text-sky-400 mt-1 uppercase">{{ Auth::user()->role }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Capability Cards Grid -->
        <div class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-6">
            
            <!-- Panel 1: Shipments -->
            <div class="p-6 rounded-2xl bg-slate-900/60 border border-slate-800 backdrop-blur-sm hover:border-slate-700 transition">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-10 h-10 rounded-xl bg-sky-500/10 text-sky-400 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                    </div>
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 bg-slate-800 px-2 py-0.5 rounded">Milestone P9</span>
                </div>
                <h3 class="text-base font-bold text-white">Manajemen Pengiriman (Shipments)</h3>
                <p class="mt-2 text-xs text-slate-400 leading-relaxed">
                    Modul siklus hidup pengiriman, pengelolaan rute, data carrier, dan item kontainer kargo.
                </p>
                <div class="mt-4 pt-4 border-t border-slate-800/80 flex items-center justify-between text-xs text-slate-500">
                    <span>Baseline Auth: Aktif</span>
                    <span class="text-sky-400 font-medium">Siap Diintegrasikan &rarr;</span>
                </div>
            </div>

            <!-- Panel 2: Tracking & Timeline -->
            <div class="p-6 rounded-2xl bg-slate-900/60 border border-slate-800 backdrop-blur-sm hover:border-slate-700 transition">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-10 h-10 rounded-xl bg-cyan-500/10 text-cyan-400 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 bg-slate-800 px-2 py-0.5 rounded">Milestone P10</span>
                </div>
                <h3 class="text-base font-bold text-white">Tracking & Event Timeline</h3>
                <p class="mt-2 text-xs text-slate-400 leading-relaxed">
                    Pencatatan riwayat kronologis pergerakan kapal & kontainer serta validasi transisi status.
                </p>
                <div class="mt-4 pt-4 border-t border-slate-800/80 flex items-center justify-between text-xs text-slate-500">
                    <span>Validasi Server-Side</span>
                    <span class="text-cyan-400 font-medium">Siap Diintegrasikan &rarr;</span>
                </div>
            </div>

            <!-- Panel 3: Delay & Exceptions -->
            <div class="p-6 rounded-2xl bg-slate-900/60 border border-slate-800 backdrop-blur-sm hover:border-slate-700 transition">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-10 h-10 rounded-xl bg-amber-500/10 text-amber-400 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 bg-slate-800 px-2 py-0.5 rounded">Milestone P11</span>
                </div>
                <h3 class="text-base font-bold text-white">Kalkulasi ETA & Exception Rule</h3>
                <p class="mt-2 text-xs text-slate-400 leading-relaxed">
                    Deteksi otomatis keterlambatan kedatangan (delay) dan penanganan insiden pelabuhan / bea cukai.
                </p>
                <div class="mt-4 pt-4 border-t border-slate-800/80 flex items-center justify-between text-xs text-slate-500">
                    <span>Engine Rule Configurable</span>
                    <span class="text-amber-400 font-medium">Siap Diintegrasikan &rarr;</span>
                </div>
            </div>

        </div>

    </main>

    <!-- Footer -->
    <footer class="relative z-10 border-t border-slate-800/80 py-6 text-center text-xs text-slate-500">
        &copy; {{ date('Y') }} CargoTrace Platform. Web Programming 2 — UAS Project.
    </footer>

</body>
</html>
