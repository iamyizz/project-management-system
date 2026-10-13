<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/png" href="{{ asset('images/logo%20perusahaan/logo%20ntu%20transparant.png') }}">
    <title>Masuk | {{ config('app.name', 'Project Management System') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-50 font-sans text-slate-900 antialiased">
    <main class="grid min-h-screen lg:grid-cols-[1.05fr_.95fr]">
        <section class="relative isolate flex min-h-[300px] flex-col overflow-hidden bg-[#101b3d] px-7 py-8 text-white sm:px-12 sm:py-10 lg:min-h-screen lg:px-16 lg:py-12">
            <div class="absolute -right-28 -top-24 -z-10 h-96 w-96 rounded-full border border-white/10"></div>
            <div class="absolute -right-12 -top-8 -z-10 h-64 w-64 rounded-full border border-white/10"></div>
            <div class="absolute -bottom-32 -left-24 -z-10 h-80 w-80 rounded-full bg-indigo-500/20 blur-3xl"></div>

            <a href="{{ url('/') }}" class="flex w-fit items-center gap-3 rounded-xl focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-300">
                <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-white p-1.5 shadow-lg shadow-black/10">
                    <img src="{{ asset('images/logo%20perusahaan/logo%20ntu%20transparant.png') }}" alt="Logo Nuke Teknologi Utama" class="h-full w-full object-contain">
                </span>
                <span>
                    <span class="block text-sm font-bold tracking-wide">NUKE TEKNOLOGI UTAMA</span>
                    <span class="mt-0.5 block text-xs text-indigo-200">Project Management System</span>
                </span>
            </a>

            <div class="my-auto max-w-xl py-12 lg:py-0">
                <div class="mb-5 inline-flex items-center gap-2 rounded-full border border-indigo-300/20 bg-white/5 px-3 py-1.5 text-xs font-medium text-indigo-100">
                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span>
                    Ruang kerja project NTU
                </div>
                <h1 class="max-w-lg text-3xl font-semibold leading-tight tracking-tight sm:text-4xl lg:text-5xl">Semua project, lebih mudah dipantau.</h1>
                <p class="mt-5 max-w-md text-sm leading-6 text-slate-300 sm:text-base sm:leading-7">Masuk untuk melihat progres, mengelola tim, dan menjaga setiap project tetap berjalan sesuai rencana.</p>

                <div class="mt-10 hidden max-w-md rounded-2xl border border-white/10 bg-white/[0.06] p-5 shadow-2xl shadow-black/10 backdrop-blur sm:block">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-medium text-slate-300">Workspace</p>
                            <p class="mt-1 text-sm font-semibold">Ringkasan aktivitas project</p>
                        </div>
                        <span class="rounded-lg bg-emerald-400/10 px-2.5 py-1 text-[11px] font-semibold text-emerald-300">Live overview</span>
                    </div>
                    <div class="mt-6 flex h-20 items-end gap-2" aria-hidden="true">
                        <span class="h-[38%] flex-1 rounded-t bg-indigo-300/60"></span>
                        <span class="h-[58%] flex-1 rounded-t bg-indigo-300/70"></span>
                        <span class="h-[46%] flex-1 rounded-t bg-indigo-300/60"></span>
                        <span class="h-[78%] flex-1 rounded-t bg-indigo-200"></span>
                        <span class="h-[62%] flex-1 rounded-t bg-indigo-300/75"></span>
                        <span class="h-[92%] flex-1 rounded-t bg-indigo-200"></span>
                        <span class="h-[70%] flex-1 rounded-t bg-indigo-300/75"></span>
                        <span class="h-[84%] flex-1 rounded-t bg-indigo-200"></span>
                    </div>
                    <div class="mt-4 flex items-center gap-2 text-xs text-slate-300">
                        <svg class="h-4 w-4 text-emerald-300" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.78-9.22a.75.75 0 00-1.06-1.06l-3.47 3.47-1.47-1.47a.75.75 0 00-1.06 1.06l2 2a.75.75 0 001.06 0l4-4z" clip-rule="evenodd" /></svg>
                        Progres dan informasi dalam satu tempat
                    </div>
                </div>
            </div>

            <p class="hidden text-xs text-slate-400 lg:block">&copy; {{ date('Y') }} Nuke Teknologi Utama</p>
        </section>

        <section class="flex items-center justify-center px-6 py-12 sm:px-10 lg:px-14">
            <div class="w-full max-w-md">
                <div class="mb-8 lg:mb-10">
                    <p class="text-sm font-semibold text-indigo-600">Selamat datang kembali</p>
                    <h2 class="mt-2 text-3xl font-bold tracking-tight text-slate-900">Masuk ke akun</h2>
                    <p class="mt-2 text-sm leading-6 text-slate-500">Gunakan email dan password yang terdaftar untuk melanjutkan.</p>
                </div>

                <x-auth-session-status class="mb-5" :status="session('status')" />

                @if ($errors->any())
                    <div role="alert" class="mb-5 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                        <p class="font-semibold">Email atau password belum sesuai.</p>
                        <p class="mt-1">Periksa kembali data masuk lu, lalu coba lagi.</p>
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}" class="space-y-5">
                    @csrf
                    <div>
                        <label for="email" class="mb-2 block text-sm font-medium text-slate-700">Email</label>
                        <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="username" placeholder="nama@perusahaan.com" class="block w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 hover:border-slate-400 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10">
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>

                    <div>
                        <div class="mb-2 flex items-center justify-between gap-3">
                            <label for="password" class="block text-sm font-medium text-slate-700">Password</label>
                            @if (Route::has('password.request'))
                                <a href="{{ route('password.request') }}" class="text-xs font-semibold text-indigo-600 transition hover:text-indigo-800">Lupa password?</a>
                            @endif
                        </div>
                        <input id="password" name="password" type="password" required autocomplete="current-password" placeholder="Masukkan password" class="block w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 hover:border-slate-400 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10">
                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                    </div>

                    <label for="remember_me" class="flex w-fit cursor-pointer items-center gap-2.5 text-sm text-slate-600">
                        <input id="remember_me" name="remember" type="checkbox" class="h-4 w-4 rounded border-slate-300 text-indigo-600 shadow-sm focus:ring-2 focus:ring-indigo-500/20">
                        Ingat saya
                    </label>

                    <button type="submit" class="group flex w-full items-center justify-center gap-2 rounded-xl bg-indigo-600 px-4 py-3.5 text-sm font-semibold text-white shadow-lg shadow-indigo-600/20 transition hover:-translate-y-0.5 hover:bg-indigo-700 hover:shadow-xl hover:shadow-indigo-600/20 focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-indigo-500/25">
                        Masuk
                        <svg class="h-4 w-4 transition-transform group-hover:translate-x-0.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M3 10a.75.75 0 01.75-.75h10.69L10.22 5.03a.75.75 0 111.06-1.06l5.5 5.5a.75.75 0 010 1.06l-5.5 5.5a.75.75 0 11-1.06-1.06l4.22-4.22H3.75A.75.75 0 013 10z" clip-rule="evenodd" /></svg>
                    </button>
                </form>

                <div class="mt-8 border-t border-slate-200 pt-5 text-center">
                    <a href="{{ url('/') }}" class="text-sm font-medium text-slate-500 transition hover:text-indigo-700">Kembali ke halaman utama</a>
                </div>
            </div>
        </section>
    </main>
</body>
</html>
