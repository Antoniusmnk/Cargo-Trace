<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-950">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Customer Portal — CargoTrace</title>

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
<body class="min-h-full bg-slate-950 text-slate-100 flex flex-col relative selection:bg-emerald-500 selection:text-white">

    <!-- Background Accents -->
    <div class="fixed inset-0 cargo-grid-pattern pointer-events-none"></div>
    <div class="fixed top-0 left-1/2 -translate-x-1/2 w-[900px] h-[400px] bg-gradient-to-b from-emerald-600/10 via-sky-500/5 to-transparent blur-3xl pointer-events-none"></div>

    <!-- Top Navigation -->
    <nav class="relative z-20 border-b border-slate-800/80 bg-slate-900/80 backdrop-blur-md">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <!-- Brand -->
                <div class="flex items-center gap-3">
                    <a href="{{ url('/') }}" class="flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-lg bg-gradient-to-tr from-emerald-600 to-sky-500 flex items-center justify-center shadow-md shadow-emerald-500/20">
                            <svg class="w-5 h-5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M2 21c.6.5 1.2 1 2.5 1 2.5 0 2.5-2 5-2 2.5 0 2.5 2 5 2 2.5 0 2.5-2 5-2 1.3 0 1.9.5 2.5 1" />
                                <path d="M19.38 20A11.6 11.6 0 0 0 21 14l-9-4-9 4c0 2.9.94 5.34 2.81 7.15" />
                                <path d="M12 10V4" />
                                <path d="m8 7 4-3 4 3" />
                                <rect x="7" y="11" width="4" height="3" rx="0.5" />
                                <rect x="13" y="11" width="4" height="3" rx="0.5" />
                            </svg>
                        </div>
                        <span class="text-lg font-extrabold tracking-tight text-white">Cargo<span class="text-emerald-400">Trace</span></span>
                    </a>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold uppercase tracking-wider bg-emerald-500/10 text-emerald-300 border border-emerald-500/30">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                        Customer Portal
                    </span>

                    <div class="hidden md:flex items-center gap-2 ml-2 text-xs font-semibold">
                        <a href="{{ route('customer.shipments') }}" class="px-3 py-1.5 rounded-lg text-emerald-300 bg-emerald-500/10 hover:bg-emerald-500/20 border border-emerald-500/20 transition">
                            Daftar Kargo Saya &rarr;
                        </a>
                    </div>
                </div>

                <!-- User actions -->
                <div class="flex items-center gap-4">
                    <div class="hidden sm:flex flex-col text-right">
                        <span class="text-sm font-bold text-white">{{ Auth::user()->name }}</span>
                        <span class="text-xs text-emerald-300">{{ Auth::user()->email }}</span>
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
        <div class="rounded-2xl bg-gradient-to-r from-emerald-950/40 via-slate-900 to-sky-950/30 border border-emerald-900/40 p-6 sm:p-8 shadow-xl">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div>
                    <div class="inline-flex items-center gap-2 text-xs font-bold text-emerald-400 bg-emerald-500/10 border border-emerald-500/20 px-3 py-1 rounded-full mb-3">
                        <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                        ROLE: CARGO CUSTOMER / CLIENT
                    </div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">
                        Client Shipment Portal
                    </h1>
                    <p class="mt-2 text-sm text-slate-300 max-w-3xl leading-relaxed">
                        Portal pelacakan mandiri kargo dan kontainer pengiriman Anda. Pantau timeline milestone secara real-time, estimasi kedatangan (ETA), serta rute pelayaran kapal pada peta interaktif.
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <div class="bg-slate-900/80 border border-emerald-800/40 rounded-xl p-4 text-center min-w-[140px]">
                        <span class="block text-xs uppercase tracking-wider text-slate-400 font-semibold">Status Klien</span>
                        <span class="block text-base font-extrabold text-emerald-400 mt-1">VERIFIED</span>
                    </div>
                    <div class="bg-slate-900/80 border border-emerald-800/40 rounded-xl p-4 text-center min-w-[140px]">
                        <span class="block text-xs uppercase tracking-wider text-slate-400 font-semibold">Cakupan Data</span>
                        <span class="block text-base font-extrabold text-white mt-1">OWN CARGO</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Metric & Feature Panels -->
        <div class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-6">
            
            <!-- Card 1: My Active Cargo -->
            <div class="p-6 rounded-2xl bg-slate-900/70 border border-slate-800 backdrop-blur-sm">
                <div class="w-10 h-10 rounded-xl bg-emerald-500/10 text-emerald-400 flex items-center justify-center mb-4">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                    </svg>
                </div>
                <h3 class="text-base font-bold text-white">Kargo & Kontainer Saya</h3>
                <p class="mt-2 text-xs text-slate-400 leading-relaxed">
                    Daftar pengiriman milik akun Anda dengan isolasi data yang aman (hanya dapat melihat shipment milik sendiri).
                </p>
                <div class="mt-4 pt-4 border-t border-slate-800 flex items-center justify-between text-xs">
                    <span class="text-emerald-300 font-medium">Owner-Scoped Active</span>
                    <a href="{{ route('customer.shipments') }}" class="text-emerald-400 hover:text-emerald-300 font-semibold transition">
                        Buka Daftar Kargo &rarr;
                    </a>
                </div>
            </div>

            <!-- Card 2: Interactive Timeline -->
            <div class="p-6 rounded-2xl bg-slate-900/70 border border-slate-800 backdrop-blur-sm">
                <div class="w-10 h-10 rounded-xl bg-sky-500/10 text-sky-400 flex items-center justify-center mb-4">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                    </svg>
                </div>
                <h3 class="text-base font-bold text-white">Timeline & Peta Rute</h3>
                <p class="mt-2 text-xs text-slate-400 leading-relaxed">
                    Visualisasi perjalanan kargo dari pelabuhan asal (Origin) ke pelabuhan tujuan (Destination) via Leaflet.js.
                </p>
                <div class="mt-4 pt-4 border-t border-slate-800 text-xs text-slate-500 font-medium">
                    Timeline & Leaflet Ready (P10 & P12)
                </div>
            </div>

            <!-- Card 3: ETA & Delivery Status -->
            <div class="p-6 rounded-2xl bg-slate-900/70 border border-slate-800 backdrop-blur-sm">
                <div class="w-10 h-10 rounded-xl bg-cyan-500/10 text-cyan-400 flex items-center justify-center mb-4">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h3 class="text-base font-bold text-white">Estimasi Kedatangan (ETA)</h3>
                <p class="mt-2 text-xs text-slate-400 leading-relaxed">
                    Informasi akurat perkiraan waktu tiba di pelabuhan tujuan beserta notifikasi jika terjadi penundaan jadwal kapal.
                </p>
                <div class="mt-4 pt-4 border-t border-slate-800 text-xs text-slate-500 font-medium">
                    Calculated Delay Engine (P11)
                </div>
            </div>

        </div>

        <!-- Tracking Quick Search Box Placeholder -->
        <div class="mt-8 p-6 rounded-2xl bg-slate-900/50 border border-slate-800">
            <h2 class="text-base font-bold text-white">Lacak Pengiriman Cepat</h2>
            <p class="mt-1 text-xs text-slate-400">Masukkan nomor resi pengiriman atau nomor kontainer untuk melihat status terkini secara langsung.</p>
            
            <div class="mt-4 max-w-xl flex gap-2">
                <input type="text" 
                       placeholder="Contoh: CT-2026-98765 atau MSCU9876543" 
                       disabled 
                       class="flex-1 bg-slate-950/70 border border-slate-800 rounded-xl px-4 py-2.5 text-xs text-slate-400 placeholder-slate-600 cursor-not-allowed">
                <button type="button" 
                        disabled 
                        class="px-5 py-2.5 rounded-xl bg-emerald-600/50 text-white font-semibold text-xs cursor-not-allowed">
                    Lacak Kargo
                </button>
            </div>
            <p class="mt-2 text-[11px] text-slate-500">Fitur pencarian publik & klien akan aktif pada implementasi Milestone P9 & P10.</p>
        </div>

    </main>

    <!-- Footer -->
    <footer class="relative z-10 border-t border-slate-800/80 py-6 text-center text-xs text-slate-500">
        &copy; {{ date('Y') }} CargoTrace Platform &bull; Customer Tracking Portal
    </footer>

</body>
</html>
