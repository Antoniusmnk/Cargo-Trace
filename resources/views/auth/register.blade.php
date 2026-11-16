<x-guest-layout title="Daftar Akun" heading="Buat Akun CargoTrace" subheading="Mulai pantau perjalanan logistik Anda secara real-time">
    
    <!-- Role Notification Banner -->
    <div class="mb-5 rounded-xl bg-slate-800/80 border border-slate-700/60 p-3.5 flex items-center gap-3 text-xs text-slate-300">
        <div class="w-8 h-8 rounded-lg bg-sky-500/10 text-sky-400 flex items-center justify-center shrink-0">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </div>
        <p class="leading-relaxed">
            Pendaftaran akun baru otomatis terdaftar sebagai <span class="font-semibold text-sky-400">Customer kargo</span>.
        </p>
    </div>

    <form method="POST" action="{{ route('register') }}" class="space-y-4">
        @csrf

        <!-- Full Name Field -->
        <div>
            <label for="name" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">
                Nama Lengkap
            </label>
            <div class="relative">
                <input id="name" 
                       name="name" 
                       type="text" 
                       autocomplete="name" 
                       required 
                       value="{{ old('name') }}"
                       placeholder="Contoh: Capt. Hendra Gunawan"
                       class="block w-full rounded-xl bg-slate-950/70 border @error('name') border-rose-500/80 focus:border-rose-500 focus:ring-rose-500 @else border-slate-700/80 focus:border-sky-500 focus:ring-sky-500 @enderror px-4 py-3 text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-1 transition">
            </div>
            @error('name')
                <p class="mt-1.5 text-xs text-rose-400 font-medium flex items-center gap-1">
                    <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                    <span>{{ $message }}</span>
                </p>
            @enderror
        </div>

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
            <label for="password" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">
                Kata Sandi
            </label>
            <div class="relative">
                <input id="password" 
                       name="password" 
                       type="password" 
                       autocomplete="new-password" 
                       required 
                       placeholder="Minimal 8 karakter"
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

        <!-- Password Confirmation Field -->
        <div>
            <label for="password_confirmation" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">
                Konfirmasi Kata Sandi
            </label>
            <div class="relative">
                <input id="password_confirmation" 
                       name="password_confirmation" 
                       type="password" 
                       autocomplete="new-password" 
                       required 
                       placeholder="Ulangi kata sandi"
                       class="block w-full rounded-xl bg-slate-950/70 border border-slate-700/80 focus:border-sky-500 focus:ring-sky-500 px-4 py-3 text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-1 transition">
            </div>
        </div>

        <!-- Submit Button -->
        <div class="pt-2">
            <button type="submit" 
                    class="w-full flex justify-center items-center gap-2 py-3 px-4 border border-transparent rounded-xl shadow-lg shadow-sky-600/30 text-sm font-bold text-white bg-gradient-to-r from-sky-600 to-sky-500 hover:from-sky-500 hover:to-sky-400 active:scale-[0.99] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-slate-900 focus:ring-sky-500 transition-all duration-200">
                <span>Daftar Akun Kargo</span>
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                </svg>
            </button>
        </div>
    </form>

    <!-- Switch to Login Link -->
    <div class="mt-6 pt-6 border-t border-slate-800/80 text-center text-xs text-slate-400">
        Sudah memiliki akun CargoTrace?
        <a href="{{ route('login') }}" class="font-semibold text-sky-400 hover:text-sky-300 ml-1 transition">
            Masuk Sekarang &rarr;
        </a>
    </div>
</x-guest-layout>
