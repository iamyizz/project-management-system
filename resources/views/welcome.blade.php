<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/png" href="{{ asset('images/logo%20perusahaan/logo%20ntu%20transparant.png') }}">
    <title>{{ config('app.name', 'Project Management System') }} | Nuke Teknologi Utama</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-50 font-sans text-slate-900 antialiased">
    <header class="relative z-10 mx-auto flex max-w-7xl items-center justify-between px-6 py-5 sm:px-10 lg:px-12">
        <a href="{{ url('/') }}" class="flex items-center gap-3 rounded-xl focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500">
            <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-white p-1.5 shadow-sm ring-1 ring-slate-200">
                <img src="{{ asset('images/logo%20perusahaan/logo%20ntu%20transparant.png') }}" alt="Logo Nuke Teknologi Utama" class="h-full w-full object-contain">
            </span>
            <span>
                <span class="block text-sm font-bold tracking-wide text-slate-900">NUKE TEKNOLOGI UTAMA</span>
                <span class="mt-0.5 block text-xs text-slate-500">Project Management System</span>
            </span>
        </a>
        <a href="{{ route('login') }}" class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:-translate-y-0.5 hover:bg-indigo-700 hover:shadow-md focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-indigo-500/20 sm:px-5">
            Masuk ke Sistem
            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M3 10a.75.75 0 01.75-.75h10.69L10.22 5.03a.75.75 0 111.06-1.06l5.5 5.5a.75.75 0 010 1.06l-5.5 5.5a.75.75 0 11-1.06-1.06l4.22-4.22H3.75A.75.75 0 013 10z" clip-rule="evenodd" /></svg>
        </a>
    </header>

    <main>
        <section class="relative isolate overflow-hidden px-6 pb-16 pt-12 sm:px-10 sm:pb-20 sm:pt-16 lg:px-12 lg:pb-24 lg:pt-20">
            <div class="absolute inset-x-0 top-0 -z-10 h-[560px] bg-gradient-to-b from-indigo-50 via-slate-50 to-slate-50"></div>
            <div class="absolute -right-32 top-8 -z-10 h-96 w-96 rounded-full bg-indigo-100/70 blur-3xl"></div>
            <div class="mx-auto grid max-w-7xl items-center gap-14 lg:grid-cols-[.92fr_1.08fr] lg:gap-16">
                <div class="max-w-xl">
                    <div class="inline-flex items-center gap-2 rounded-full border border-indigo-100 bg-white/80 px-3 py-1.5 text-xs font-semibold text-indigo-700 shadow-sm">
                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                        Workspace internal NTU
                    </div>
                    <h1 class="mt-6 text-4xl font-semibold leading-[1.1] tracking-tight text-slate-950 sm:text-5xl lg:text-[3.65rem]">Project lebih tertata, progres lebih terlihat.</h1>
                    <p class="mt-6 max-w-lg text-base leading-7 text-slate-600">Kelola project, pantau deadline, dan lihat ringkasan pekerjaan tim dalam satu workspace yang mudah digunakan.</p>
                    <div class="mt-8 flex flex-wrap items-center gap-4">
                        <a href="{{ route('login') }}" class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-6 py-3.5 text-sm font-semibold text-white shadow-lg shadow-indigo-600/20 transition hover:-translate-y-0.5 hover:bg-indigo-700 hover:shadow-xl focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-indigo-500/25">
                            Masuk ke Workspace
                            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M3 10a.75.75 0 01.75-.75h10.69L10.22 5.03a.75.75 0 111.06-1.06l5.5 5.5a.75.75 0 010 1.06l-5.5 5.5a.75.75 0 11-1.06-1.06l4.22-4.22H3.75A.75.75 0 013 10z" clip-rule="evenodd" /></svg>
                        </a>
                        <span class="text-xs text-slate-500">Akses khusus pengguna terdaftar</span>
                    </div>

                    <div class="mt-10 flex flex-wrap gap-x-6 gap-y-3 border-t border-slate-200 pt-6 text-sm text-slate-600">
                        <span class="inline-flex items-center gap-2"><svg class="h-4 w-4 text-emerald-600" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.78-9.22a.75.75 0 00-1.06-1.06l-3.47 3.47-1.47-1.47a.75.75 0 00-1.06 1.06l2 2a.75.75 0 001.06 0l4-4z" clip-rule="evenodd" /></svg>Project terpusat</span>
                        <span class="inline-flex items-center gap-2"><svg class="h-4 w-4 text-emerald-600" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.78-9.22a.75.75 0 00-1.06-1.06l-3.47 3.47-1.47-1.47a.75.75 0 00-1.06 1.06l2 2a.75.75 0 001.06 0l4-4z" clip-rule="evenodd" /></svg>Progres tim terlihat</span>
                    </div>
                </div>

                <div class="relative mx-auto w-full max-w-2xl lg:mx-0">
                    <div class="absolute -inset-5 -z-10 rounded-[2rem] bg-indigo-200/40 blur-2xl"></div>
                    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl shadow-slate-900/10">
                        <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4 sm:px-6">
                            <div class="flex items-center gap-3">
                                <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600">
                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75A2.25 2.25 0 016 4.5h4.19c.6 0 1.17.24 1.59.66l1.06 1.06c.42.42.99.66 1.59.66H18a2.25 2.25 0 012.25 2.25v8.25A2.25 2.25 0 0118 19.63H6a2.25 2.25 0 01-2.25-2.25V6.75z" /></svg>
                                </span>
                                <div><p class="text-sm font-semibold text-slate-900">Project Overview</p><p class="mt-0.5 text-xs text-slate-500">Kelola aktivitas workspace</p></div>
                            </div>
                            <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-[11px] font-semibold text-emerald-700">Workspace</span>
                        </div>

                        <div class="grid grid-cols-3 gap-3 p-4 sm:gap-4 sm:p-6">
                            <div class="rounded-xl border border-slate-200 p-3 sm:p-4"><p class="text-[11px] font-medium text-slate-500 sm:text-xs">Project</p><div class="mt-3 h-2 w-12 rounded bg-indigo-100"></div><div class="mt-2 h-2 w-8 rounded bg-slate-100"></div></div>
                            <div class="rounded-xl border border-slate-200 p-3 sm:p-4"><p class="text-[11px] font-medium text-slate-500 sm:text-xs">Berjalan</p><div class="mt-3 h-2 w-10 rounded bg-blue-100"></div><div class="mt-2 h-2 w-8 rounded bg-slate-100"></div></div>
                            <div class="rounded-xl border border-slate-200 p-3 sm:p-4"><p class="text-[11px] font-medium text-slate-500 sm:text-xs">Selesai</p><div class="mt-3 h-2 w-11 rounded bg-emerald-100"></div><div class="mt-2 h-2 w-8 rounded bg-slate-100"></div></div>
                        </div>

                        <div class="px-4 pb-5 sm:px-6 sm:pb-6">
                            <div class="overflow-hidden rounded-xl border border-slate-200">
                                <div class="flex items-center justify-between border-b border-slate-100 bg-slate-50 px-4 py-3"><span class="text-xs font-semibold text-slate-600">Aktivitas project</span><span class="text-[10px] font-medium text-slate-400">Ringkasan</span></div>
                                <div class="space-y-1 p-2 sm:p-3">
                                    <div class="flex items-center gap-3 rounded-lg px-2 py-2.5"><span class="h-2 w-2 rounded-full bg-indigo-500"></span><span class="h-2 flex-1 rounded bg-slate-200"></span><span class="h-5 w-14 rounded-full bg-blue-50"></span></div>
                                    <div class="flex items-center gap-3 rounded-lg px-2 py-2.5"><span class="h-2 w-2 rounded-full bg-amber-500"></span><span class="h-2 w-2/3 rounded bg-slate-200"></span><span class="h-5 w-14 rounded-full bg-amber-50"></span></div>
                                    <div class="flex items-center gap-3 rounded-lg px-2 py-2.5"><span class="h-2 w-2 rounded-full bg-emerald-500"></span><span class="h-2 w-3/4 rounded bg-slate-200"></span><span class="h-5 w-14 rounded-full bg-emerald-50"></span></div>
                                </div>
                            </div>
                            <div class="mt-4 flex flex-wrap gap-2">
                                <span class="rounded-lg bg-slate-50 px-3 py-2 text-[11px] font-medium text-slate-600">End User</span>
                                <span class="rounded-lg bg-slate-50 px-3 py-2 text-[11px] font-medium text-slate-600">Laporan</span>
                            </div>
                        </div>
                    </div>
                    <div class="absolute -bottom-5 -left-5 hidden items-center gap-3 rounded-xl border border-slate-200 bg-white px-4 py-3 shadow-lg sm:flex">
                        <span class="flex h-9 w-9 items-center justify-center rounded-full bg-emerald-50 text-emerald-600"><svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.78-9.22a.75.75 0 00-1.06-1.06l-3.47 3.47-1.47-1.47a.75.75 0 00-1.06 1.06l2 2a.75.75 0 001.06 0l4-4z" clip-rule="evenodd" /></svg></span>
                        <span><span class="block text-xs font-semibold text-slate-900">Lebih terorganisir</span><span class="mt-0.5 block text-[11px] text-slate-500">Informasi tersimpan terpusat</span></span>
                    </div>
                </div>
            </div>
        </section>

        <section class="border-t border-slate-200 bg-white px-6 py-12 sm:px-10 lg:px-12 lg:py-14">
            <div class="mx-auto max-w-7xl">
                <div class="max-w-xl">
                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-indigo-600">Dalam satu workspace</p>
                    <h2 class="mt-2 text-2xl font-semibold tracking-tight text-slate-900 sm:text-3xl">Alur kerja project yang lebih jelas.</h2>
                </div>
                <div class="mt-8 grid gap-4 sm:grid-cols-3">
                    <article class="rounded-2xl border border-slate-200 p-5 transition hover:-translate-y-1 hover:border-indigo-200 hover:shadow-lg hover:shadow-slate-900/5">
                        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75A2.25 2.25 0 016 4.5h4.19c.6 0 1.17.24 1.59.66l1.06 1.06c.42.42.99.66 1.59.66H18a2.25 2.25 0 012.25 2.25v8.25A2.25 2.25 0 0118 19.63H6a2.25 2.25 0 01-2.25-2.25V6.75z" /></svg></span>
                        <h3 class="mt-4 text-sm font-semibold text-slate-900">Kelola project</h3><p class="mt-1.5 text-sm leading-6 text-slate-500">Simpan detail project, end user, PIC, dan nomor dokumen dalam satu tempat.</p>
                    </article>
                    <article class="rounded-2xl border border-slate-200 p-5 transition hover:-translate-y-1 hover:border-indigo-200 hover:shadow-lg hover:shadow-slate-900/5">
                        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-50 text-blue-600"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3v18h18M7.5 14.25l4.5-4.5 3 3 5.25-6" /></svg></span>
                        <h3 class="mt-4 text-sm font-semibold text-slate-900">Pantau progres</h3><p class="mt-1.5 text-sm leading-6 text-slate-500">Lihat status, deadline, dan ringkasan laporan untuk membantu pekerjaan tetap terarah.</p>
                    </article>
                    <article class="rounded-2xl border border-slate-200 p-5 transition hover:-translate-y-1 hover:border-indigo-200 hover:shadow-lg hover:shadow-slate-900/5">
                        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 7.5h7.5m-7.5 4.5h7.5m-7.5 4.5h4.5M6 3.75h9.75L20.25 8v12.25A1.75 1.75 0 0118.5 22h-12A1.75 1.75 0 014.75 20.25v-14.75A1.75 1.75 0 016.5 3.75z" /></svg></span>
                        <h3 class="mt-4 text-sm font-semibold text-slate-900">Buat kwitansi</h3><p class="mt-1.5 text-sm leading-6 text-slate-500">Hubungkan kwitansi dengan project dan unduh dokumennya sebagai PDF.</p>
                    </article>
                </div>
            </div>
        </section>
    </main>

    <footer class="flex flex-col items-center justify-between gap-2 bg-white px-6 py-5 text-xs text-slate-500 sm:flex-row sm:px-10 lg:px-12">
        <span>&copy; {{ date('Y') }} Nuke Teknologi Utama</span>
        <span>Project Management System</span>
    </footer>
</body>
</html>
