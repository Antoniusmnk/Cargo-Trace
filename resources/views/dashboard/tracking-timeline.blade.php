<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-950">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tracking Timeline #{{ $shipment->shipment_number }} — CargoTrace</title>

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
                            $shipmentsIndexUrl = match(Auth::user()->role) {
                                'admin' => route('admin.shipments'),
                                'operator' => route('operator.shipments'),
                                default => route('customer.shipments'),
                            };
                        @endphp
                        <a href="{{ $shipmentsIndexUrl }}" 
                           class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800 transition">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                            </svg>
                            <span>Kembali ke Daftar Kargo</span>
                        </a>
                        <span class="px-3 py-1.5 rounded-lg bg-sky-500/10 text-sky-300 border border-sky-500/30">
                            Tracking #{{ $shipment->shipment_number }}
                        </span>
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
        
        <!-- Flash Alert Success -->
        @if(session('success'))
            <div class="mb-6 p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-300 flex items-center gap-3">
                <svg class="w-5 h-5 shrink-0 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="text-xs font-semibold">{{ session('success') }}</span>
            </div>
        @endif

        <!-- Header Overview Card -->
        <div class="rounded-2xl bg-slate-900/90 border border-slate-800 p-6 sm:p-8 shadow-xl backdrop-blur-md mb-8">
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6 pb-6 border-b border-slate-800">
                <div>
                    <div class="flex items-center gap-3 flex-wrap">
                        <span class="text-xs font-mono font-bold tracking-widest text-sky-400 bg-sky-950/80 px-2.5 py-1 rounded-md border border-sky-800/60">
                            {{ $shipment->shipment_number }}
                        </span>
                        @php
                            $statusBadgeClass = match($shipment->current_status) {
                                'CREATED' => 'bg-slate-500/20 text-slate-300 border-slate-500/40',
                                'BOOKED' => 'bg-sky-500/20 text-sky-300 border-sky-500/40',
                                'PICKED_UP' => 'bg-indigo-500/20 text-indigo-300 border-indigo-500/40',
                                'IN_TRANSIT' => 'bg-amber-500/20 text-amber-300 border-amber-500/40',
                                'CUSTOMS_HOLD' => 'bg-rose-500/20 text-rose-300 border-rose-500/40',
                                'DELIVERED' => 'bg-emerald-500/20 text-emerald-300 border-emerald-500/40',
                                default => 'bg-red-500/20 text-red-300 border-red-500/40',
                            };
                        @endphp
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-extrabold uppercase tracking-wider border {{ $statusBadgeClass }}">
                            <span class="w-2 h-2 rounded-full {{ $shipment->current_status === 'DELIVERED' ? 'bg-emerald-400' : 'bg-sky-400 animate-pulse' }}"></span>
                            {{ $shipment->current_status }}
                        </span>

                        @if($shipment->is_delayed)
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-500/20 text-amber-300 border border-amber-500/40">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                                DELAYED (Terlambat)
                            </span>
                        @endif
                    </div>

                    <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight mt-3">
                        {{ $shipment->origin?->city }} &rarr; {{ $shipment->destination?->city }}
                    </h1>
                    <p class="text-xs text-slate-400 mt-1">
                        {{ $shipment->origin?->name }} ({{ $shipment->origin?->code }}) menuju {{ $shipment->destination?->name }} ({{ $shipment->destination?->code }})
                    </p>
                </div>

                <!-- Carrier & Customer Badge -->
                <div class="flex items-center gap-3 flex-wrap">
                    <div class="bg-slate-950/80 border border-slate-800 rounded-xl p-3.5 min-w-[140px]">
                        <span class="block text-[11px] uppercase tracking-wider text-slate-500 font-semibold">Carrier / Pelayaran</span>
                        <span class="block text-sm font-bold text-slate-200 mt-0.5">{{ $shipment->carrier?->name }}</span>
                        <span class="inline-block text-[10px] font-mono text-sky-400 bg-sky-950 px-1.5 py-0.5 rounded border border-sky-800/40 mt-1">
                            {{ $shipment->carrier?->code }}
                        </span>
                    </div>

                    <div class="bg-slate-950/80 border border-slate-800 rounded-xl p-3.5 min-w-[140px]">
                        <span class="block text-[11px] uppercase tracking-wider text-slate-500 font-semibold">Estimasi Tiba (ETA)</span>
                        <span class="block text-sm font-bold {{ $shipment->is_delayed ? 'text-amber-400' : 'text-emerald-400' }} mt-0.5">
                            {{ \Carbon\Carbon::parse($shipment->estimated_arrival ?? $shipment->expected_arrival)->format('d M Y') }}
                        </span>
                        <span class="block text-[10px] text-slate-500 mt-1">
                            SLA: {{ \Carbon\Carbon::parse($shipment->expected_arrival)->format('d M Y') }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Metadata Strip -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mt-6 text-xs text-slate-300">
                <div>
                    <span class="text-slate-500 block">Tipe Muatan:</span>
                    <span class="font-semibold text-slate-200">{{ $shipment->cargo_type }}</span>
                </div>
                <div>
                    <span class="text-slate-500 block">Total Berat / Volume:</span>
                    <span class="font-semibold text-slate-200">{{ number_format($shipment->total_weight_kg, 2) }} kg &bull; {{ number_format($shipment->total_volume_cbm, 2) }} cbm</span>
                </div>
                <div>
                    <span class="text-slate-500 block">Keberangkatan:</span>
                    <span class="font-semibold text-slate-200">{{ \Carbon\Carbon::parse($shipment->expected_departure)->format('d M Y') }}</span>
                </div>
                <div>
                    <span class="text-slate-500 block">Customer:</span>
                    <span class="font-semibold text-slate-200">{{ $shipment->customer?->name }}</span>
                </div>
            </div>
        </div>

        <!-- Horizontal Stepper Progress Bar -->
        @php
            $steps = [
                ['key' => 'CREATED', 'label' => 'Created', 'desc' => 'Manifes Terdaftar'],
                ['key' => 'BOOKED', 'label' => 'Booked', 'desc' => 'Slot Terkonfirmasi'],
                ['key' => 'PICKED_UP', 'label' => 'Picked Up', 'desc' => 'Kontainer Diambil'],
                ['key' => 'IN_TRANSIT', 'label' => 'In Transit', 'desc' => 'Dalam Pelayaran'],
                ['key' => 'DELIVERED', 'label' => 'Delivered', 'desc' => 'Tiba di Tujuan'],
            ];

            $stepKeys = array_column($steps, 'key');
            $currentIdx = array_search($shipment->current_status, $stepKeys, true);
            if ($currentIdx === false) $currentIdx = 0;
        @endphp

        <div class="rounded-2xl bg-slate-900/70 border border-slate-800 p-6 shadow-xl mb-8 overflow-x-auto">
            <h2 class="text-xs uppercase font-bold tracking-wider text-slate-400 mb-6">Progres Siklus Hidup Kargo (State Machine)</h2>
            <div class="flex items-center justify-between min-w-[620px] relative">
                
                <!-- Background connecting line -->
                <div class="absolute left-6 right-6 top-5 -translate-y-1/2 h-1 bg-slate-800 -z-0"></div>

                @foreach ($steps as $index => $step)
                    @php
                        $isPast = $index < $currentIdx;
                        $isCurrent = $index === $currentIdx;
                        $isFuture = $index > $currentIdx;
                    @endphp
                    
                    <div class="flex flex-col items-center relative z-10 text-center px-2">
                        <!-- Step Circle Icon -->
                        @if ($isPast)
                            <div class="w-10 h-10 rounded-full bg-emerald-500/20 text-emerald-400 border-2 border-emerald-500 flex items-center justify-center font-bold text-xs shadow-md shadow-emerald-500/20">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                        @elseif ($isCurrent)
                            <div class="w-10 h-10 rounded-full bg-sky-500 text-white border-4 border-slate-950 flex items-center justify-center font-bold text-xs shadow-lg shadow-sky-500/40 ring-4 ring-sky-500/30 animate-pulse">
                                {{ $index + 1 }}
                            </div>
                        @else
                            <div class="w-10 h-10 rounded-full bg-slate-900 text-slate-500 border-2 border-slate-800 flex items-center justify-center font-bold text-xs">
                                {{ $index + 1 }}
                            </div>
                        @endif

                        <!-- Step Labels -->
                        <span class="text-xs font-bold mt-2 {{ $isCurrent ? 'text-sky-300' : ($isPast ? 'text-slate-200' : 'text-slate-500') }}">
                            {{ $step['label'] }}
                        </span>
                        <span class="text-[10px] {{ $isCurrent ? 'text-sky-400' : 'text-slate-500' }}">
                            {{ $step['desc'] }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- 2-Column Responsive Layout -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            
            <!-- Left Column: Vertical Timeline (8 Cols) -->
            <div class="lg:col-span-7 xl:col-span-8 rounded-2xl bg-slate-900/90 border border-slate-800 p-6 sm:p-8 shadow-xl">
                <div class="flex items-center justify-between mb-6 pb-4 border-b border-slate-800">
                    <div>
                        <h2 class="text-lg font-bold text-white flex items-center gap-2">
                            <svg class="w-5 h-5 text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>Linimasa Perjalanan Kargo (Tracking Events)</span>
                        </h2>
                        <p class="text-xs text-slate-400 mt-0.5">Catatan milestone kronologis terverifikasi dari pelabuhan asal hingga tujuan.</p>
                    </div>
                    <span class="text-xs font-mono font-semibold px-2.5 py-1 rounded-full bg-slate-800 text-slate-300">
                        {{ $timeline->count() }} Checkpoint
                    </span>
                </div>

                @if($timeline->isEmpty())
                    <div class="py-12 text-center text-slate-500">
                        <svg class="w-10 h-10 mx-auto text-slate-600 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                        <p class="font-medium text-slate-300 text-sm">Belum ada riwayat milestone kargo.</p>
                        <p class="text-xs text-slate-500 mt-1">Checkpoint pertama akan tampil otomatis saat operator mencatat kejadian.</p>
                    </div>
                @else
                    <!-- Vertical Timeline Nodes -->
                    <div class="relative pl-6 sm:pl-8 border-l-2 border-slate-800 space-y-8 my-4 ml-3 sm:ml-4">
                        @foreach ($timeline as $index => $event)
                            @php
                                $isLatest = $loop->last;
                                $nodeColorClass = match($event->status) {
                                    'CREATED' => 'bg-slate-400 border-slate-300 text-slate-950',
                                    'BOOKED' => 'bg-sky-400 border-sky-300 text-slate-950',
                                    'PICKED_UP' => 'bg-indigo-400 border-indigo-300 text-white',
                                    'IN_TRANSIT' => 'bg-amber-400 border-amber-300 text-slate-950',
                                    'DELIVERED' => 'bg-emerald-400 border-emerald-300 text-slate-950',
                                    default => 'bg-slate-400 border-slate-300 text-slate-950',
                                };
                            @endphp

                            <div class="relative group">
                                <!-- Marker Node Icon on the vertical line -->
                                <div class="absolute -left-[31px] sm:-left-[39px] top-1.5 w-7 h-7 sm:w-8 sm:h-8 rounded-full {{ $nodeColorClass }} border-2 flex items-center justify-center font-bold text-xs shadow-md transition group-hover:scale-110">
                                    @if ($event->status === 'DELIVERED')
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                        </svg>
                                    @elseif ($event->status === 'IN_TRANSIT')
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                        </svg>
                                    @else
                                        <span class="text-[10px]">{{ $index + 1 }}</span>
                                    @endif
                                </div>

                                <!-- Card Content -->
                                <div class="rounded-xl p-4 sm:p-5 {{ $isLatest ? 'bg-slate-900 border-2 border-sky-500/40 shadow-lg shadow-sky-950/40' : 'bg-slate-950/60 border border-slate-800/80' }} transition">
                                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-2">
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-wider border {{ $statusBadgeClass }}">
                                                {{ $event->status }}
                                            </span>

                                            @if ($isLatest)
                                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-sky-500/20 text-sky-300 border border-sky-500/40">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-sky-400 animate-ping"></span>
                                                    Current Active Checkpoint
                                                </span>
                                            @endif
                                        </div>

                                        <time class="text-xs font-mono text-slate-400">
                                            {{ \Carbon\Carbon::parse($event->event_time)->translatedFormat('d M Y, H:i') }} WIB
                                        </time>
                                    </div>

                                    <!-- Location Info -->
                                    <div class="flex items-center gap-1.5 text-sm font-bold text-white mt-2">
                                        <svg class="w-4 h-4 text-rose-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                        <span>{{ $event->location_name }}</span>
                                    </div>

                                    @if ($event->latitude && $event->longitude)
                                        <div class="text-[11px] font-mono text-slate-400 mt-1 pl-5">
                                            GPS: {{ $event->latitude }}, {{ $event->longitude }}
                                        </div>
                                    @endif

                                    <!-- Description -->
                                    <p class="text-xs text-slate-300 leading-relaxed mt-2.5 pl-5">
                                        {{ $event->description }}
                                    </p>

                                    <!-- Recorded By Actor -->
                                    <div class="mt-3 pt-3 border-t border-slate-800/80 flex items-center justify-between text-[11px] text-slate-500 pl-5">
                                        <span>Dicatat oleh: <strong class="text-slate-400 font-semibold">{{ $event->creator?->name ?? 'System' }}</strong></span>
                                        <span>ID: #EV-{{ str_pad($event->id, 4, '0', STR_PAD_LEFT) }}</span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Right Column: Sidebar (Cargo Items & Checkpoint Form) (4-5 Cols) -->
            <div class="lg:col-span-5 xl:col-span-4 space-y-6">
                
                <!-- Card 1: Manifest Items -->
                <div class="rounded-2xl bg-slate-900/90 border border-slate-800 p-6 shadow-xl">
                    <h3 class="text-sm font-bold text-white flex items-center gap-2 mb-4">
                        <svg class="w-4 h-4 text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                        <span>Manifes Muatan Barang ({{ $shipment->items->count() }} Item)</span>
                    </h3>

                    <div class="space-y-3">
                        @foreach ($shipment->items as $item)
                            <div class="p-3 rounded-xl bg-slate-950/70 border border-slate-800/80 text-xs">
                                <div class="font-bold text-slate-200">{{ $item->item_name }}</div>
                                <div class="grid grid-cols-2 gap-1 text-[11px] text-slate-400 mt-1.5">
                                    <div><span class="text-slate-500">Qty:</span> {{ $item->quantity }} {{ $item->package_type }}</div>
                                    <div><span class="text-slate-500">Berat:</span> {{ number_format($item->weight_kg, 2) }} kg</div>
                                    <div><span class="text-slate-500">Volume:</span> {{ number_format($item->volume_cbm, 2) }} cbm</div>
                                    <div><span class="text-slate-500">HS Code:</span> <span class="font-mono text-sky-300">{{ $item->hs_code ?? '-' }}</span></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Card 2: Tambah Checkpoint Baru (Admin & Operator Only) -->
                @if(Auth::user()->isAdmin() || Auth::user()->isOperator())
                    <div class="rounded-2xl bg-slate-900/90 border border-slate-800 p-6 shadow-xl">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-sm font-bold text-white flex items-center gap-2">
                                <svg class="w-4 h-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                                <span>Catat Checkpoint Baru</span>
                            </h3>
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-emerald-500/10 text-emerald-300 border border-emerald-500/30 uppercase">
                                Operator
                            </span>
                        </div>

                        @if($shipment->current_status === 'DELIVERED')
                            <div class="p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-300 text-xs leading-relaxed">
                                <div class="font-bold flex items-center gap-1.5 text-emerald-400 mb-1">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span>Pengiriman Selesai (Delivered)</span>
                                </div>
                                Kargo telah sampai di tujuan akhir. Siklus hidup pengiriman telah selesai dan status telah dikunci secara permanen sesuai State Machine.
                            </div>
                        @else
                            <!-- Error Transition Alert -->
                            @if($errors->has('transition_error'))
                                <div class="mb-4 p-3.5 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-300 text-xs flex items-start gap-2">
                                    <svg class="w-4 h-4 text-rose-400 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span>{{ $errors->first('transition_error') }}</span>
                                </div>
                            @endif

                            <form method="POST" action="{{ route('shipments.tracking.checkpoint.store', $shipment) }}" class="space-y-4">
                                @csrf

                                <!-- Status Selection -->
                                <div>
                                    <label class="block text-xs font-semibold text-slate-300 mb-1">
                                        Status Checkpoint Berikutnya <span class="text-rose-400">*</span>
                                    </label>
                                    <select name="status" required class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-sky-500 transition">
                                        @forelse ($allowedNextStatuses as $nextStatus)
                                            <option value="{{ $nextStatus }}" {{ old('status') === $nextStatus ? 'selected' : '' }}>
                                                {{ $nextStatus }} {{ $nextStatus === 'IN_TRANSIT' && $shipment->current_status === 'IN_TRANSIT' ? '(Pembaruan Waypoint)' : '' }}
                                            </option>
                                        @empty
                                            <option value="" disabled>Tidak ada transisi tersedia</option>
                                        @endforelse
                                    </select>
                                    @error('status')
                                        <p class="text-rose-400 text-[11px] mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Location Selector / Input -->
                                <div>
                                    <label class="block text-xs font-semibold text-slate-300 mb-1">
                                        Pilih Titik Lokasi Master (Opsional)
                                    </label>
                                    <select name="location_id" id="location_id" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-xs text-slate-300 focus:outline-none focus:border-sky-500 transition">
                                        <option value="">-- Ketik manual atau pilih lokasi --</option>
                                        @foreach ($locations as $loc)
                                            <option value="{{ $loc->id }}" 
                                                    data-name="{{ $loc->name }}, {{ $loc->city }}" 
                                                    data-lat="{{ $loc->latitude }}" 
                                                    data-lon="{{ $loc->longitude }}"
                                                    {{ old('location_id') == $loc->id ? 'selected' : '' }}>
                                                {{ $loc->code }} — {{ $loc->name }} ({{ $loc->city }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-slate-300 mb-1">
                                        Nama Lokasi / Checkpoint <span class="text-rose-400">*</span>
                                    </label>
                                    <input type="text" name="location_name" id="location_name" required 
                                           value="{{ old('location_name') }}"
                                           placeholder="Contoh: Tanjung Priok Yard 2, Jakarta"
                                           class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-xs text-white placeholder-slate-600 focus:outline-none focus:border-sky-500 transition">
                                    @error('location_name')
                                        <p class="text-rose-400 text-[11px] mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Event Time -->
                                <div>
                                    <label class="block text-xs font-semibold text-slate-300 mb-1">
                                        Waktu Kejadian <span class="text-rose-400">*</span>
                                    </label>
                                    <input type="datetime-local" name="event_time" required 
                                           value="{{ old('event_time', now()->format('Y-m-d\TH:i')) }}"
                                           class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-sky-500 transition">
                                    @error('event_time')
                                        <p class="text-rose-400 text-[11px] mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Coordinates (Optional) -->
                                <div class="grid grid-cols-2 gap-2">
                                    <div>
                                        <label class="block text-xs text-slate-400 mb-1">Latitude</label>
                                        <input type="number" step="0.0000001" name="latitude" id="latitude" 
                                               value="{{ old('latitude') }}"
                                               placeholder="-6.1018800"
                                               class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-xs text-white placeholder-slate-600 focus:outline-none focus:border-sky-500 transition">
                                    </div>
                                    <div>
                                        <label class="block text-xs text-slate-400 mb-1">Longitude</label>
                                        <input type="number" step="0.0000001" name="longitude" id="longitude" 
                                               value="{{ old('longitude') }}"
                                               placeholder="106.8833800"
                                               class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-xs text-white placeholder-slate-600 focus:outline-none focus:border-sky-500 transition">
                                    </div>
                                </div>

                                <!-- Description -->
                                <div>
                                    <label class="block text-xs font-semibold text-slate-300 mb-1">
                                        Deskripsi Aktivitas <span class="text-rose-400">*</span>
                                    </label>
                                    <textarea name="description" rows="3" required
                                              placeholder="Tuliskan catatan kejadian kargo..."
                                              class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-xs text-white placeholder-slate-600 focus:outline-none focus:border-sky-500 transition">{{ old('description') }}</textarea>
                                    @error('description')
                                        <p class="text-rose-400 text-[11px] mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <button type="submit" 
                                        class="w-full py-2.5 px-4 rounded-xl text-xs font-bold text-white bg-sky-600 hover:bg-sky-500 shadow-md shadow-sky-600/30 transition flex items-center justify-center gap-2 cursor-pointer">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span>Catat Checkpoint & Perbarui Status</span>
                                </button>
                            </form>
                        @endif
                    </div>
                @endif
            </div>

        </div>

    </main>

    <!-- Footer -->
    <footer class="relative z-10 border-t border-slate-900 bg-slate-950/80 py-4 mt-auto">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-2 text-xs text-slate-500">
            <p>&copy; {{ date('Y') }} CargoTrace Global Logistics. Seluruh hak cipta dilindungi.</p>
            <div class="flex items-center gap-4 text-slate-400">
                <span>Milestone P10.3: Visual Timeline & Tracking UI</span>
            </div>
        </div>
    </footer>

    <script>
        document.getElementById('location_id')?.addEventListener('change', function() {
            const opt = this.options[this.selectedIndex];
            if (opt && opt.value) {
                const name = opt.getAttribute('data-name');
                const lat = opt.getAttribute('data-lat');
                const lon = opt.getAttribute('data-lon');

                if (name) document.getElementById('location_name').value = name;
                if (lat) document.getElementById('latitude').value = lat;
                if (lon) document.getElementById('longitude').value = lon;
            }
        });
    </script>
</body>
</html>
