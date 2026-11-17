<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-950">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Shipments & Cargo Tracking — CargoTrace</title>

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
    <div class="fixed top-0 left-1/2 -translate-x-1/2 w-[900px] h-[400px] bg-gradient-to-b from-sky-600/10 via-indigo-500/5 to-transparent blur-3xl pointer-events-none"></div>

    <!-- Top Navigation -->
    <nav class="relative z-20 border-b border-slate-800/80 bg-slate-900/80 backdrop-blur-md">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <!-- Brand & Navigation Links -->
                <div class="flex items-center gap-6">
                    <a href="{{ url('/') }}" class="flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-lg bg-gradient-to-tr from-sky-600 to-indigo-500 flex items-center justify-center shadow-md shadow-sky-500/20">
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

                    <!-- Nav Links -->
                    <div class="hidden md:flex items-center gap-2 text-xs font-semibold">
                        @php
                            $dashboardRoute = match(Auth::user()->role) {
                                'admin' => route('admin.dashboard'),
                                'operator' => route('operator.dashboard'),
                                default => route('customer.dashboard'),
                            };
                        @endphp
                        <a href="{{ $dashboardRoute }}" 
                           class="px-3 py-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800 transition">
                            &larr; Kembali ke Dashboard
                        </a>
                        <span class="px-3 py-1.5 rounded-lg bg-sky-500/10 text-sky-300 border border-sky-500/30">
                            Daftar Shipments
                        </span>
                        @if(Auth::user()->isAdmin() || Auth::user()->isOperator())
                            <a href="{{ Auth::user()->isAdmin() ? route('admin.carriers') : route('operator.carriers') }}" 
                               class="px-3 py-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800 transition">
                                Master Carriers
                            </a>
                            <a href="{{ Auth::user()->isAdmin() ? route('admin.locations') : route('operator.locations') }}" 
                               class="px-3 py-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800 transition">
                                Master Locations
                            </a>
                        @endif
                    </div>
                </div>

                <!-- User Profile & Logout -->
                <div class="flex items-center gap-4">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold uppercase tracking-wider bg-sky-500/10 text-sky-300 border border-sky-500/30">
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
                <span class="text-xs font-bold uppercase tracking-wider text-sky-400">Cargo Logistics Tracking &bull; Milestone P9.4</span>
                <h1 class="text-2xl font-extrabold text-white mt-1">Daftar Pengiriman Kargo (Shipments)</h1>
                <p class="text-xs text-slate-400 mt-1">
                    @if(Auth::user()->isCustomer())
                        Monitoring real-time status seluruh pengiriman dan kontainer kargo milik Anda.
                    @else
                        Manajemen dan visibilitas menyeluruh seluruh pergerakan kargo global dalam sistem.
                    @endif
                </p>
            </div>
            <div class="flex items-center gap-3">
                <span class="text-xs font-semibold px-3 py-1 rounded-full bg-slate-900 border border-slate-800 text-slate-300">
                    Total: {{ $shipments->total() }} Pengiriman
                </span>
            </div>
        </div>

        <!-- Table Card -->
        <div class="rounded-2xl bg-slate-900/80 border border-slate-800 overflow-hidden shadow-xl">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-300">
                    <thead class="bg-slate-950/80 border-b border-slate-800 text-slate-400 uppercase tracking-wider text-[11px] font-semibold">
                        <tr>
                            <th scope="col" class="py-3.5 px-4">No. Resi (Shipment #)</th>
                            @if(!Auth::user()->isCustomer())
                                <th scope="col" class="py-3.5 px-4">Customer</th>
                            @endif
                            <th scope="col" class="py-3.5 px-4">Carrier</th>
                            <th scope="col" class="py-3.5 px-4">Rute Pelayaran</th>
                            <th scope="col" class="py-3.5 px-4">Tipe & Muatan</th>
                            <th scope="col" class="py-3.5 px-4">Waktu & ETA</th>
                            <th scope="col" class="py-3.5 px-4">Status</th>
                            <th scope="col" class="py-3.5 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/80 font-normal">
                        @forelse ($shipments as $shipment)
                            @php
                                $statusBadgeClass = match($shipment->current_status) {
                                    'CREATED' => 'bg-slate-500/10 text-slate-300 border-slate-500/30',
                                    'BOOKED' => 'bg-sky-500/10 text-sky-300 border-sky-500/30',
                                    'PICKED_UP' => 'bg-indigo-500/10 text-indigo-300 border-indigo-500/30',
                                    'IN_TRANSIT' => 'bg-amber-500/10 text-amber-300 border-amber-500/30',
                                    'CUSTOMS_HOLD' => 'bg-rose-500/10 text-rose-300 border-rose-500/30',
                                    'DELIVERED' => 'bg-emerald-500/10 text-emerald-300 border-emerald-500/30',
                                    default => 'bg-red-500/10 text-red-300 border-red-500/30',
                                };
                            @endphp
                            <tr class="hover:bg-slate-800/40 transition">
                                <td class="py-3.5 px-4 whitespace-nowrap">
                                    <div class="flex items-center gap-2">
                                        <div class="w-2 h-2 rounded-full bg-sky-400"></div>
                                        <span class="font-mono font-bold text-white text-xs tracking-wider">{{ $shipment->shipment_number }}</span>
                                    </div>
                                    <span class="text-[10px] text-slate-500 ml-4 block">{{ $shipment->items->count() }} Item Manifest</span>
                                </td>
                                @if(!Auth::user()->isCustomer())
                                    <td class="py-3.5 px-4 whitespace-nowrap">
                                        <div class="font-semibold text-slate-200">{{ $shipment->customer?->name ?? 'N/A' }}</div>
                                        <div class="text-[10px] text-slate-400">{{ $shipment->customer?->email ?? '-' }}</div>
                                    </td>
                                @endif
                                <td class="py-3.5 px-4 whitespace-nowrap">
                                    <div class="font-semibold text-slate-200">{{ $shipment->carrier?->name ?? '-' }}</div>
                                    <span class="inline-block text-[10px] font-mono font-bold text-sky-400 bg-sky-950/60 px-1.5 py-0.5 rounded border border-sky-800/40">
                                        {{ $shipment->carrier?->code ?? '-' }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 whitespace-nowrap">
                                    <div class="flex items-center gap-1.5 text-xs">
                                        <span class="font-semibold text-slate-200">{{ $shipment->origin?->city ?? 'Origin' }}</span>
                                        <span class="text-slate-500">&rarr;</span>
                                        <span class="font-semibold text-slate-200">{{ $shipment->destination?->city ?? 'Dest' }}</span>
                                    </div>
                                    <div class="text-[10px] text-slate-400">
                                        {{ $shipment->origin?->code }} ({{ $shipment->origin?->country }}) &bull; {{ $shipment->destination?->code }} ({{ $shipment->destination?->country }})
                                    </div>
                                </td>
                                <td class="py-3.5 px-4 whitespace-nowrap">
                                    <div class="font-medium text-slate-200">{{ $shipment->cargo_type }}</div>
                                    <div class="text-[10px] text-slate-400">
                                        {{ number_format($shipment->total_weight_kg, 2) }} kg &bull; {{ number_format($shipment->total_volume_cbm, 2) }} cbm
                                    </div>
                                </td>
                                <td class="py-3.5 px-4 whitespace-nowrap">
                                    <div class="text-slate-300">
                                        <span class="text-slate-500">Berangkat:</span> {{ \Carbon\Carbon::parse($shipment->expected_departure)->format('d M Y') }}
                                    </div>
                                    <div class="text-slate-300">
                                        <span class="text-slate-500">ETA:</span> 
                                        <span class="{{ $shipment->is_delayed ? 'text-amber-400 font-semibold' : 'text-slate-200' }}">
                                            {{ \Carbon\Carbon::parse($shipment->estimated_arrival ?? $shipment->expected_arrival)->format('d M Y') }}
                                        </span>
                                    </div>
                                    @if($shipment->is_delayed)
                                        <span class="inline-flex items-center gap-1 text-[10px] font-bold text-amber-400 mt-0.5">
                                            <svg class="w-3 h-3 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                            </svg>
                                            Terlambat (Delayed)
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 whitespace-nowrap">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider border {{ $statusBadgeClass }}">
                                        {{ $shipment->current_status }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 whitespace-nowrap text-right">
                                    <a href="{{ route('shipments.tracking.show', $shipment) }}" 
                                       class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold text-sky-400 bg-sky-500/10 hover:bg-sky-500/20 border border-sky-500/30 transition">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <span>Pelacakan / Timeline</span>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ Auth::user()->isCustomer() ? 7 : 8 }}" class="py-12 px-4 text-center text-slate-400">
                                    <div class="max-w-xs mx-auto">
                                        <svg class="w-8 h-8 text-slate-600 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                        </svg>
                                        <p class="font-medium text-slate-300">Belum ada data pengiriman kargo.</p>
                                        <p class="text-xs text-slate-500 mt-1">Data shipment akan tampil otomatis saat manifes dibuat.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($shipments->hasPages())
                <div class="p-4 border-t border-slate-800 bg-slate-950/60">
                    {{ $shipments->links() }}
                </div>
            @endif
        </div>
    </main>

    <!-- Footer -->
    <footer class="relative z-10 border-t border-slate-900 bg-slate-950/80 py-4 mt-auto">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-2 text-xs text-slate-500">
            <p>&copy; {{ date('Y') }} CargoTrace Global Logistics. Seluruh hak cipta dilindungi.</p>
            <div class="flex items-center gap-4 text-slate-400">
                <span>Milestone P9.4: Shipments CRUD & Monitoring</span>
            </div>
        </div>
    </footer>

</body>
</html>
