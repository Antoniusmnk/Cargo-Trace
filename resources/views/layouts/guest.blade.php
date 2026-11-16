<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-950">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Autentikasi' }} — {{ config('app.name', 'CargoTrace') }}</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Styles & Scripts (Vite with Tailwind CDN Fallback) -->
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
                        },
                        colors: {
                            brand: {
                                navy: '#0F172A',
                                blue: '#0284C7',
                                sky: '#38BDF8',
                                cyan: '#06B6D4',
                            }
                        }
                    }
                }
            }
        </script>
    @endif

    <style>
        body {
            font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
        }
        .cargo-grid-pattern {
            background-size: 40px 40px;
            background-image: 
                linear-gradient(to right, rgba(255, 255, 255, 0.03) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(255, 255, 255, 0.03) 1px, transparent 1px);
        }
    </style>
</head>
<body class="min-h-full bg-slate-950 text-slate-100 flex flex-col justify-center py-12 sm:px-6 lg:px-8 relative overflow-x-hidden selection:bg-sky-500 selection:text-white">

    <!-- Background Elements -->
    <div class="fixed inset-0 cargo-grid-pattern pointer-events-none"></div>
    <div class="fixed top-0 left-1/2 -translate-x-1/2 w-[800px] h-[400px] bg-gradient-to-b from-sky-500/10 via-cyan-500/5 to-transparent blur-3xl pointer-events-none"></div>

    <!-- Header / Brand Section -->
    <div class="sm:mx-auto sm:w-full sm:max-w-md relative z-10 text-center">
        <a href="{{ url('/') }}" class="inline-flex items-center gap-3 group focus:outline-none focus:ring-2 focus:ring-sky-500 rounded-xl px-2 py-1">
            <!-- Maritime Cargo Ship / Container SVG Icon -->
            <div class="w-12 h-12 rounded-xl bg-gradient-to-tr from-sky-600 to-cyan-500 flex items-center justify-center shadow-lg shadow-sky-500/25 group-hover:shadow-sky-500/40 transition-all duration-300 transform group-hover:-translate-y-0.5">
                <svg class="w-7 h-7 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <!-- Ship Hull & Container stacks -->
                    <path d="M2 21c.6.5 1.2 1 2.5 1 2.5 0 2.5-2 5-2 2.5 0 2.5 2 5 2 2.5 0 2.5-2 5-2 1.3 0 1.9.5 2.5 1" />
                    <path d="M19.38 20A11.6 11.6 0 0 0 21 14l-9-4-9 4c0 2.9.94 5.34 2.81 7.15" />
                    <path d="M12 10V4" />
                    <path d="m8 7 4-3 4 3" />
                    <rect x="7" y="11" width="4" height="3" rx="0.5" />
                    <rect x="13" y="11" width="4" height="3" rx="0.5" />
                </svg>
            </div>

            <div class="text-left">
                <span class="text-2xl font-extrabold tracking-tight text-white block">
                    Cargo<span class="text-sky-400">Trace</span>
                </span>
                <span class="inline-flex items-center gap-1 text-[11px] font-semibold uppercase tracking-wider text-sky-400/90 bg-sky-500/10 border border-sky-500/20 px-2 py-0.5 rounded-full">
                    <span class="w-1.5 h-1.5 rounded-full bg-sky-400 animate-pulse"></span>
                    Global Visibility
                </span>
            </div>
        </a>

        @if (isset($heading))
            <h2 class="mt-6 text-xl font-bold tracking-tight text-white">
                {{ $heading }}
            </h2>
        @endif
        @if (isset($subheading))
            <p class="mt-1 text-sm text-slate-400">
                {{ $subheading }}
            </p>
        @endif
    </div>

    <!-- Main Card Container -->
    <div class="mt-6 sm:mx-auto sm:w-full sm:max-w-md px-4 sm:px-0 relative z-10">
        <div class="bg-slate-900/90 backdrop-blur-xl border border-slate-800 rounded-2xl p-6 sm:p-8 shadow-2xl shadow-slate-950/80">
            <!-- Flash Message Status -->
            @if (session('status'))
                <div class="mb-5 rounded-xl bg-sky-500/10 border border-sky-500/20 p-4 text-sm text-sky-300 flex items-start gap-3">
                    <svg class="w-5 h-5 text-sky-400 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>{{ session('status') }}</span>
                </div>
            @endif

            @if (isset($slot) && trim($slot) !== '')
                {{ $slot }}
            @else
                @yield('content')
            @endif
        </div>

        <!-- Footer -->
        <div class="mt-8 text-center text-xs text-slate-500">
            &copy; {{ date('Y') }} CargoTrace Platform. All rights reserved.
        </div>
    </div>

</body>
</html>
