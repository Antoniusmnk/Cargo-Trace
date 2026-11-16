<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-950">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Master Data Carriers — CargoTrace</title>

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
                <!-- Brand & Navigation Links -->
                <div class="flex items-center gap-6">
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

                    <!-- Nav Links -->
                    <div class="hidden md:flex items-center gap-2 text-xs font-semibold">
                        <a href="{{ Auth::user()->isAdmin() ? route('admin.dashboard') : route('operator.dashboard') }}" 
                           class="px-3 py-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800 transition">
                            &larr; Kembali ke Dashboard
                        </a>
                        <a href="{{ Auth::user()->isAdmin() ? route('admin.shipments') : route('operator.shipments') }}" 
                           class="px-3 py-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800 transition">
                            Data Shipments
                        </a>
                        <span class="px-3 py-1.5 rounded-lg bg-purple-500/10 text-purple-300 border border-purple-500/30">
                            Master Carriers
                        </span>
                        <a href="{{ Auth::user()->isAdmin() ? route('admin.locations') : route('operator.locations') }}" 
                           class="px-3 py-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800 transition">
                            Master Locations
                        </a>
                    </div>
                </div>

                <!-- User Profile & Logout -->
                <div class="flex items-center gap-4">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold uppercase tracking-wider bg-purple-500/10 text-purple-300 border border-purple-500/30">
                        {{ Auth::user()->role }}
                    </span>

                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit" 
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold text-rose-300 bg-rose-500/10 hover:bg-rose-500/20 border border-rose-500/20 transition cursor-pointer">
                            <span>Keluar</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="relative z-10 flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8">
        
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
            <div>
                <span class="text-xs font-bold uppercase tracking-wider text-purple-400">Master Data Engine &bull; Milestone P9.1</span>
                <h1 class="text-2xl font-extrabold text-white mt-1">Daftar Operator Pelayaran (Carriers)</h1>
                <p class="text-xs text-slate-400 mt-1">Operator logistik laut & udara yang terintegrasi pada jaringan pelacakan kargo.</p>
            </div>
            <div class="flex items-center gap-2">
                <span class="text-xs font-semibold px-3 py-1 rounded-full bg-slate-900 border border-slate-800 text-slate-300">
                    Total: {{ count($carriers) }} Operator
                </span>
            </div>
        </div>

        <!-- Table Card -->
        <div class="rounded-2xl bg-slate-900/80 border border-slate-800 overflow-hidden shadow-xl">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-300">
                    <thead class="bg-slate-950/80 border-b border-slate-800 text-slate-400 uppercase tracking-wider text-[11px] font-semibold">
                        <tr>
                            <th scope="col" class="py-3.5 px-4">Kode</th>
                            <th scope="col" class="py-3.5 px-4">Nama Operator</th>
                            <th scope="col" class="py-3.5 px-4">Email Kontak</th>
                            <th scope="col" class="py-3.5 px-4">Nomor Telepon</th>
                            <th scope="col" class="py-3.5 px-4">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/80 font-normal">
                        @forelse($carriers as $carrier)
                            <tr class="hover:bg-slate-800/40 transition">
                                <td class="py-3 px-4 font-mono font-bold text-white">{{ $carrier->code }}</td>
                                <td class="py-3 px-4 font-semibold text-slate-200">{{ $carrier->name }}</td>
                                <td class="py-3 px-4 text-slate-400">{{ $carrier->contact_email ?? '-' }}</td>
                                <td class="py-3 px-4 text-slate-400">{{ $carrier->phone ?? '-' }}</td>
                                <td class="py-3 px-4">
                                    @if($carrier->status === 'active')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                                            ACTIVE
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-slate-700/50 text-slate-400 border border-slate-700">
                                            INACTIVE
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-8 text-center text-slate-500">
                                    Belum ada data carrier. Jalankan `php artisan db:seed` untuk memuat data awal.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </main>

    <!-- Footer -->
    <footer class="relative z-10 border-t border-slate-800/80 py-6 text-center text-xs text-slate-500">
        &copy; {{ date('Y') }} CargoTrace Platform &bull; Master Data Management
    </footer>

</body>
</html>
