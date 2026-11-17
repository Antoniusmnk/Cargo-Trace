<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-950">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Disruption & Exception Control Center — CargoTrace</title>

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
<body class="min-h-full bg-slate-950 text-slate-100 flex flex-col relative selection:bg-rose-500 selection:text-white">

    <!-- Background Accents -->
    <div class="fixed inset-0 cargo-grid-pattern pointer-events-none"></div>
    <div class="fixed top-0 left-1/2 -translate-x-1/2 w-[900px] h-[400px] bg-gradient-to-b from-rose-600/10 via-amber-500/5 to-transparent blur-3xl pointer-events-none"></div>

    <!-- Top Navigation -->
    <nav class="relative z-20 border-b border-slate-800/80 bg-slate-900/80 backdrop-blur-md">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <!-- Brand & Navigation Links -->
                <div class="flex items-center gap-6">
                    <a href="{{ url('/') }}" class="flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-lg bg-gradient-to-tr from-rose-600 to-amber-500 flex items-center justify-center shadow-md shadow-rose-500/20">
                            <svg class="w-5 h-5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <span class="text-lg font-extrabold tracking-tight text-white">Cargo<span class="text-rose-400">Trace</span></span>
                    </a>

                    <!-- Nav Links -->
                    <div class="hidden md:flex items-center gap-2 text-xs font-semibold">
                        @php
                            $dashboardRoute = Auth::user()->isAdmin() ? route('admin.dashboard') : route('operator.dashboard');
                            $shipmentsRoute = Auth::user()->isAdmin() ? route('admin.shipments') : route('operator.shipments');
                            $carriersRoute  = Auth::user()->isAdmin() ? route('admin.carriers') : route('operator.carriers');
                            $locationsRoute = Auth::user()->isAdmin() ? route('admin.locations') : route('operator.locations');
                        @endphp
                        <a href="{{ $dashboardRoute }}" 
                           class="px-3 py-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800 transition">
                            &larr; Dashboard
                        </a>
                        <a href="{{ $shipmentsRoute }}" 
                           class="px-3 py-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800 transition">
                            Daftar Shipments
                        </a>
                        <a href="{{ $carriersRoute }}" 
                           class="px-3 py-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800 transition">
                            Master Carriers
                        </a>
                        <a href="{{ $locationsRoute }}" 
                           class="px-3 py-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800 transition">
                            Master Locations
                        </a>
                        <span class="px-3 py-1.5 rounded-lg bg-rose-500/10 text-rose-300 border border-rose-500/30">
                            Exception Center
                        </span>
                    </div>
                </div>

                <!-- User Profile & Logout -->
                <div class="flex items-center gap-4">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold uppercase tracking-wider bg-rose-500/10 text-rose-300 border border-rose-500/30">
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
        
        <!-- Flash Alert -->
        @if (session('success'))
            <div class="mb-6 p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-300 flex items-center gap-3">
                <svg class="w-5 h-5 text-emerald-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="text-xs font-semibold">{{ session('success') }}</span>
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-6 p-4 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-300 text-xs">
                <ul class="list-disc pl-5 space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Header Card & Metrics -->
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6 mb-8">
            <div>
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-rose-500 animate-pulse"></span>
                    <span class="text-xs font-bold uppercase tracking-wider text-rose-400">Exception & Incident Management &bull; Milestone P11.4</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-white mt-1">Disruption & Exception Control Center</h1>
                <p class="text-xs text-slate-400 mt-1 max-w-2xl">
                    Pusat kendali operasional untuk memantau gangguan logistik, keterlambatan pelayaran, hambatan kepabeanan, serta penyesuaian jadwal ETA deterministik secara global.
                </p>
            </div>

            <!-- KPI Metric Badges -->
            <div class="flex items-center gap-3 shrink-0">
                <div class="px-4 py-3 rounded-2xl bg-slate-900/90 border border-slate-800 shadow-lg min-w-[120px]">
                    <span class="text-[10px] uppercase font-bold text-slate-400 block tracking-wider">Total Kasus</span>
                    <span class="text-xl font-extrabold text-white font-mono mt-0.5 block">{{ $totalCount }}</span>
                </div>
                <div class="px-4 py-3 rounded-2xl bg-rose-950/40 border border-rose-800/50 shadow-lg min-w-[120px]">
                    <span class="text-[10px] uppercase font-bold text-rose-300 block tracking-wider">Kendala Aktif</span>
                    <span class="text-xl font-extrabold text-rose-400 font-mono mt-0.5 block">{{ $openCount }}</span>
                </div>
                <div class="px-4 py-3 rounded-2xl bg-emerald-950/40 border border-emerald-800/50 shadow-lg min-w-[120px]">
                    <span class="text-[10px] uppercase font-bold text-emerald-300 block tracking-wider">Terselesaikan</span>
                    <span class="text-xl font-extrabold text-emerald-400 font-mono mt-0.5 block">{{ $resolvedCount }}</span>
                </div>
            </div>
        </div>

        <!-- Filter Controls -->
        <div class="rounded-2xl bg-slate-900/90 border border-slate-800 p-4 mb-6 shadow-xl backdrop-blur-md">
            <form method="GET" action="{{ route('exceptions.index') }}" class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <!-- Status Filter Tabs -->
                <div class="flex items-center gap-1.5 flex-wrap">
                    @php
                        $currentStatus = request('status', 'ALL');
                    @endphp
                    @foreach (['ALL' => 'Semua Kasus', 'OPEN' => 'OPEN', 'INVESTIGATING' => 'INVESTIGATING', 'RESOLVED' => 'RESOLVED'] as $stKey => $stLabel)
                        <a href="{{ route('exceptions.index', array_merge(request()->query(), ['status' => $stKey])) }}"
                           class="px-3 py-1.5 rounded-xl text-xs font-bold transition {{ $currentStatus === $stKey ? 'bg-rose-600 text-white shadow-md shadow-rose-600/30' : 'bg-slate-950/70 text-slate-400 hover:text-white border border-slate-800' }}">
                            {{ $stLabel }}
                        </a>
                    @endforeach
                </div>

                <!-- Type Filter Dropdown & Reset -->
                <div class="flex items-center gap-2">
                    <select name="exception_type" onchange="this.form.submit()"
                            class="bg-slate-950 border border-slate-800 rounded-xl px-3 py-1.5 text-xs text-slate-300 focus:outline-none focus:border-rose-500 transition">
                        <option value="">-- Semua Jenis Kendala --</option>
                        @foreach ([
                            'CUSTOMS_HOLD' => 'Customs Hold (Kepabeanan)',
                            'WEATHER_DELAY' => 'Weather Delay (Cuaca Buruk)',
                            'PORT_CONGESTION' => 'Port Congestion (Kepadatan Pelabuhan)',
                            'DAMAGED' => 'Damaged (Kerusakan Muatan)',
                            'MISROUTED' => 'Misrouted (Salah Rute)',
                            'DOCUMENTATION_ISSUE' => 'Documentation Issue (Dokumen)',
                        ] as $typeKey => $typeLabel)
                            <option value="{{ $typeKey }}" {{ request('exception_type') === $typeKey ? 'selected' : '' }}>
                                {{ $typeLabel }}
                            </option>
                        @endforeach
                    </select>

                    @if(request('status') || request('exception_type'))
                        <a href="{{ route('exceptions.index') }}" 
                           class="px-3 py-1.5 rounded-xl text-xs font-semibold text-slate-400 hover:text-white bg-slate-950 border border-slate-800 hover:border-slate-700 transition">
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Table Container -->
        <div class="rounded-2xl bg-slate-900/90 border border-slate-800 overflow-hidden shadow-xl backdrop-blur-md">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-300">
                    <thead class="bg-slate-950/60 text-slate-400 font-semibold border-b border-slate-800 uppercase tracking-wider text-[11px]">
                        <tr>
                            <th scope="col" class="py-3.5 px-4">No. Resi Kargo</th>
                            <th scope="col" class="py-3.5 px-4">Jenis Kendala</th>
                            <th scope="col" class="py-3.5 px-4">Severity</th>
                            <th scope="col" class="py-3.5 px-4">Dampak ETA</th>
                            <th scope="col" class="py-3.5 px-4">Status</th>
                            <th scope="col" class="py-3.5 px-4">Terdeteksi & Pelapor</th>
                            <th scope="col" class="py-3.5 px-4">Deskripsi & Solusi</th>
                            <th scope="col" class="py-3.5 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/80 font-normal">
                        @forelse ($exceptions as $exc)
                            @php
                                $severityClass = match($exc->severity) {
                                    'HIGH' => 'bg-rose-500/10 text-rose-400 border-rose-500/30',
                                    'MEDIUM' => 'bg-amber-500/10 text-amber-400 border-amber-500/30',
                                    default => 'bg-sky-500/10 text-sky-400 border-sky-500/30',
                                };

                                $statusClass = match($exc->status) {
                                    'RESOLVED' => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/30',
                                    'INVESTIGATING' => 'bg-amber-500/10 text-amber-400 border-amber-500/30',
                                    default => 'bg-rose-500/10 text-rose-400 border-rose-500/30',
                                };
                            @endphp
                            <tr class="hover:bg-slate-800/40 transition">
                                <!-- Shipment Number -->
                                <td class="py-3.5 px-4 whitespace-nowrap">
                                    <a href="{{ route('shipments.tracking.show', $exc->shipment) }}" 
                                       class="font-mono font-bold text-sky-400 hover:text-sky-300 hover:underline flex items-center gap-1.5">
                                        <svg class="w-3.5 h-3.5 text-sky-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                        </svg>
                                        <span>{{ $exc->shipment->shipment_number }}</span>
                                    </a>
                                    <div class="text-[10px] text-slate-500 mt-0.5">
                                        {{ $exc->shipment->carrier?->name ?? 'Carrier N/A' }}
                                    </div>
                                </td>

                                <!-- Exception Type -->
                                <td class="py-3.5 px-4 whitespace-nowrap">
                                    <span class="inline-block px-2 py-0.5 rounded font-mono font-bold text-[11px] bg-slate-950 text-slate-200 border border-slate-800">
                                        {{ $exc->exception_type }}
                                    </span>
                                </td>

                                <!-- Severity Badge -->
                                <td class="py-3.5 px-4 whitespace-nowrap">
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider border {{ $severityClass }}">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $exc->severity === 'HIGH' ? 'bg-rose-400 animate-ping' : ($exc->severity === 'MEDIUM' ? 'bg-amber-400' : 'bg-sky-400') }}"></span>
                                        {{ $exc->severity }}
                                    </span>
                                </td>

                                <!-- Impact Hours -->
                                <td class="py-3.5 px-4 whitespace-nowrap">
                                    <span class="font-mono font-semibold {{ $exc->impact_hours > 0 ? 'text-amber-400' : 'text-slate-400' }}">
                                        +{{ $exc->impact_hours }} jam
                                    </span>
                                </td>

                                <!-- Status Badge -->
                                <td class="py-3.5 px-4 whitespace-nowrap">
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider border {{ $statusClass }}">
                                        {{ $exc->status }}
                                    </span>
                                </td>

                                <!-- Detected At & Reporter -->
                                <td class="py-3.5 px-4 whitespace-nowrap">
                                    <div class="text-slate-200 font-medium">
                                        {{ \Carbon\Carbon::parse($exc->detected_at)->format('d M Y, H:i') }}
                                    </div>
                                    <div class="text-[10px] text-slate-400 mt-0.5">
                                        Oleh: {{ $exc->reporter?->name ?? 'System' }}
                                    </div>
                                    @if($exc->resolved_at)
                                        <div class="text-[10px] text-emerald-400 mt-0.5">
                                            Selesai: {{ \Carbon\Carbon::parse($exc->resolved_at)->format('d M Y, H:i') }}
                                        </div>
                                    @endif
                                </td>

                                <!-- Description & Resolution -->
                                <td class="py-3.5 px-4 max-w-xs">
                                    <div class="text-xs text-slate-300 leading-snug line-clamp-2">
                                        {{ $exc->description }}
                                    </div>
                                    @if ($exc->resolution_notes)
                                        <div class="mt-1 pt-1 border-t border-slate-800 text-[11px] text-emerald-400 leading-snug">
                                            <span class="font-semibold text-slate-400">Solusi:</span> {{ $exc->resolution_notes }}
                                        </div>
                                    @endif
                                </td>

                                <!-- Actions -->
                                <td class="py-3.5 px-4 whitespace-nowrap text-right">
                                    @if ($exc->status !== 'RESOLVED')
                                        <button type="button" 
                                                onclick="openResolveModal('{{ $exc->id }}', '{{ $exc->shipment->shipment_number }}', '{{ $exc->exception_type }}')"
                                                class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-semibold text-emerald-400 bg-emerald-500/10 hover:bg-emerald-500/20 border border-emerald-500/30 transition cursor-pointer">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                            </svg>
                                            <span>Selesaikan</span>
                                        </button>
                                    @else
                                        <span class="inline-flex items-center gap-1 text-[11px] text-emerald-400 font-semibold">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                            </svg>
                                            Selesai
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-12 px-4 text-center text-slate-400">
                                    <div class="max-w-xs mx-auto">
                                        <svg class="w-10 h-10 text-slate-600 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <p class="font-medium text-slate-300">Tidak ada kendala yang tercatat.</p>
                                        <p class="text-xs text-slate-500 mt-1">Seluruh pengiriman kargo beroperasi normal dan tepat waktu.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if ($exceptions->hasPages())
                <div class="p-4 border-t border-slate-800 bg-slate-950/40">
                    {{ $exceptions->links() }}
                </div>
            @endif
        </div>

    </main>

    <!-- Modal Penyelesaian Kendala (Resolve Modal) -->
    <div id="resolveModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm hidden">
        <div class="relative w-full max-w-lg rounded-2xl bg-slate-900 border border-slate-800 p-6 shadow-2xl">
            <div class="flex items-center justify-between pb-4 border-b border-slate-800">
                <div class="flex items-center gap-2">
                    <div class="w-7 h-7 rounded-lg bg-emerald-500/10 border border-emerald-500/30 flex items-center justify-center text-emerald-400">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    <h3 class="text-sm font-bold text-white">Selesaikan Kendala Kargo</h3>
                </div>
                <button type="button" onclick="closeResolveModal()" class="text-slate-400 hover:text-white transition">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form id="resolveForm" method="POST" action="" class="mt-4 space-y-4">
                @csrf
                <div class="text-xs text-slate-300">
                    Menyelesaikan kendala <strong id="modalExcType" class="text-rose-400"></strong> pada kargo <strong id="modalResi" class="text-sky-400 font-mono"></strong>.
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">
                        Catatan Penyelesaian & Solusi <span class="text-rose-400">*</span>
                    </label>
                    <textarea name="resolution_notes" id="resolution_notes" rows="4" required
                              placeholder="Tuliskan tindakan korektif atau hasil verifikasi yang menyelesaikan kendala ini..."
                              class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-xs text-white placeholder-slate-600 focus:outline-none focus:border-emerald-500 transition"></textarea>
                </div>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <button type="button" onclick="closeResolveModal()"
                            class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-400 hover:text-white bg-slate-800 transition">
                        Batal
                    </button>
                    <button type="submit"
                            class="px-4 py-2 rounded-xl text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-500 shadow-md shadow-emerald-600/30 transition flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <span>Konfirmasi Selesai</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Footer -->
    <footer class="relative z-10 border-t border-slate-900 bg-slate-950/80 py-4 mt-auto">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-2 text-xs text-slate-500">
            <p>&copy; {{ date('Y') }} CargoTrace Global Logistics. Seluruh hak cipta dilindungi.</p>
            <div class="flex items-center gap-4 text-slate-400">
                <span>Milestone P11.4: Disruption & Exception Control Center</span>
            </div>
        </div>
    </footer>

    <script>
        function openResolveModal(id, resi, excType) {
            const form = document.getElementById('resolveForm');
            form.action = `/exceptions/${id}/resolve`;
            document.getElementById('modalResi').textContent = resi;
            document.getElementById('modalExcType').textContent = excType;
            document.getElementById('resolveModal').classList.remove('hidden');
        }

        function closeResolveModal() {
            document.getElementById('resolveModal').classList.add('hidden');
        }
    </script>
</body>
</html>