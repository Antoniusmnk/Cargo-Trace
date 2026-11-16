<x-guest-layout title="Masuk" heading="Selamat Datang Kembali" subheading="Akses visibilitas kargo dan pelacakan global Anda">
    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf

        <!-- Email Field -->
        <div>
            <label for="email" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">
                Alamat Email
            </label>
            <div class="relative">
                <input id="email" 
                       name="email" 
                       type="email" 
                       autocomplete="email" 
                       required 
                       value="{{ old('email') }}"
                       placeholder="nama@perusahaan.com"
                       class="block w-full rounded-xl bg-slate-950/70 border @error('email') border-rose-500/80 focus:border-rose-500 focus:ring-rose-500 @else border-slate-700/80 focus:border-sky-500 focus:ring-sky-500 @enderror px-4 py-3 text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-1 transition">
            </div>
            @error('email')
                <p class="mt-1.5 text-xs text-rose-400 font-medium flex items-center gap-1">
                    <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                    <span>{{ $message }}</span>
                </p>
            @enderror
        </div>

        <!-- Password Field -->
        <div>
            <div class="flex items-center justify-between mb-1.5">
                <label for="password" class="block text-xs font-semibold uppercase tracking-wider text-slate-300">
                    Kata Sandi
                </label>
            </div>
            <div class="relative">
                <input id="password" 
                       name="password" 
                       type="password" 
                       autocomplete="current-password" 
                       required 
                       placeholder="••••••••"
                       class="block w-full rounded-xl bg-slate-950/70 border @error('password') border-rose-500/80 focus:border-rose-500 focus:ring-rose-500 @else border-slate-700/80 focus:border-sky-500 focus:ring-sky-500 @enderror px-4 py-3 text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-1 transition">
            </div>
            @error('password')
                <p class="mt-1.5 text-xs text-rose-400 font-medium flex items-center gap-1">
                    <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                    <span>{{ $message }}</span>
                </p>
            @enderror
        </div>

        <!-- Remember Me Checkbox -->
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <input id="remember" 
                       name="remember" 
                       type="checkbox" 
                       {{ old('remember') ? 'checked' : '' }}
                       class="h-4 w-4 rounded border-slate-700 bg-slate-950/70 text-sky-500 focus:ring-sky-500/30 focus:ring-offset-0 focus:ring-2">
                <label for="remember" class="ml-2 block text-xs font-medium text-slate-300 select-none cursor-pointer">
                    Ingat saya di perangkat ini
                </label>
            </div>
        </div>

        <!-- Submit Button -->
        <div>
            <button type="submit" 
                    class="w-full flex justify-center items-center gap-2 py-3 px-4 border border-transparent rounded-xl shadow-lg shadow-sky-600/30 text-sm font-bold text-white bg-gradient-to-r from-sky-600 to-sky-500 hover:from-sky-500 hover:to-sky-400 active:scale-[0.99] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-slate-900 focus:ring-sky-500 transition-all duration-200">
                <span>Masuk ke Platform</span>
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                </svg>
            </button>
        </div>
    </form>

    <!-- Switch to Register Link -->
    <div class="mt-6 pt-6 border-t border-slate-800/80 text-center text-xs text-slate-400">
        Belum memiliki akun CargoTrace?
        <a href="{{ route('register') }}" class="font-semibold text-sky-400 hover:text-sky-300 ml-1 transition">
            Daftar Akun Baru &rarr;
        </a>
    </div>
</x-guest-layout>
