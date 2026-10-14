@props(['title' => 'Dashboard'])

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title }} | {{ config('app.name') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo%20perusahaan/logo%20ntu%20transparant.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#f5f7fb] text-slate-800 antialiased selection:bg-indigo-100 selection:text-indigo-900">

@php
    $headerNotifications = auth()->user()->notifications()->latest()->limit(5)->get();
    $unreadNotificationCount = auth()->user()->unreadNotifications()->count();
    $icons = [
        'home'   => 'M2.25 12l8.954-8.955a1.126 1.126 0 011.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75',
        'users'  => 'M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0zM2.25 19.234A6.375 6.375 0 0114.214 16.06',
        'folder' => 'M2.25 12.75V12A2.25 2.25 0 014.5 9.75h15A2.25 2.25 0 0121.75 12v.75m-8.69-6.44l-2.12-2.12a1.5 1.5 0 00-1.061-.44H4.5A2.25 2.25 0 002.25 6v12a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9a2.25 2.25 0 00-2.25-2.25h-5.379a1.5 1.5 0 01-1.06-.44z',
        'chart'  => 'M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z',
        'check'  => 'M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
        'building' => 'M3 21h18M5 21V7l7-4 7 4v14M9 21v-6h6v6M9 9h.01M15 9h.01M9 13h.01M15 13h.01',
    ];

    $menus = auth()->user()->role === 'admin'
        ? [
            ['label' => 'Beranda', 'route' => 'dashboard',         'icon' => $icons['home']],
            ['label' => 'Kelola Pengguna', 'route' => 'admin.users.index', 'icon' => $icons['users']],
            ['label' => 'Pelanggan', 'route' => 'admin.end-users.index', 'icon' => $icons['building']],
            ['label' => 'Proyek',     'route' => 'admin.projects.index', 'icon' => $icons['folder']],
            ['label' => 'Laporan',      'route' => 'admin.reports.index', 'icon' => $icons['chart']],
        ]
        : [
            ['label' => 'Beranda',    'route' => 'user.dashboard',         'icon' => $icons['home']],
            ['label' => 'Proyek Saya', 'route' => 'user.projects.index',    'icon' => $icons['folder']],
        ];
@endphp

{{-- Overlay mobile --}}
<div id="overlay" class="fixed inset-0 z-30 hidden bg-slate-900/40 lg:hidden" onclick="toggleSidebar()"></div>

{{-- Sidebar --}}
<aside id="sidebar"
       class="fixed inset-y-0 left-0 z-40 flex w-64 -translate-x-full flex-col border-r border-slate-200/80 bg-white shadow-[8px_0_30px_-24px_rgba(15,23,42,0.25)] transition-transform duration-200 lg:translate-x-0">

    {{-- Logo Section --}}
    <div class="border-b border-slate-100 px-5 py-5">
        <div class="flex items-center gap-4">
            <img src="{{ asset('images/logo%20perusahaan/logo%20ntu%20transparant.png') }}" alt="Logo Nuke Teknologi Utama" class="h-12 w-12 shrink-0 rounded-xl bg-slate-50 p-1 object-contain ring-1 ring-slate-200/70">
            <div class="min-w-0">
                <p class="mb-1 text-[10px] font-bold uppercase tracking-[0.18em] text-indigo-600">NTU Workspace</p>
                <h2 class="text-sm font-bold leading-snug text-slate-900">Manajemen Proyek</h2>
            </div>
        </div>
    </div>

    {{-- Menu Section --}}
    <nav class="flex-1 overflow-y-auto px-3 py-6">
        <p class="mb-3 px-3 text-[10px] font-bold uppercase tracking-[0.18em] text-slate-400">Ruang Kerja</p>

        <div class="space-y-2">
            @foreach ($menus as $menu)
                @php
                    $active = $menu['route'] && request()->routeIs(str_replace('.index', '', $menu['route']) . '*');
                @endphp
                <a href="{{ $menu['route'] ? route($menu['route']) : '#' }}"
                   @if ($active) aria-current="page" @endif
                   class="group relative flex items-center gap-3 rounded-xl px-3.5 py-3 text-sm font-medium transition duration-200
                          {{ $active
                              ? 'bg-indigo-50 text-indigo-700 ring-1 ring-indigo-100'
                              : 'text-slate-600 hover:bg-slate-50 hover:text-slate-950' }}">
                    @if ($active)<span class="absolute inset-y-2 left-0 w-1 rounded-r-full bg-indigo-600"></span>@endif
                    <svg class="h-5 w-5 shrink-0 {{ $active ? 'text-indigo-600' : 'text-slate-400 group-hover:text-slate-600' }}" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $menu['icon'] }}" />
                    </svg>
                    <span>{{ $menu['label'] }}</span>
                </a>
            @endforeach
        </div>
    </nav>

    {{-- Footer Section --}}
    <div class="border-t border-slate-100 px-5 py-4">
        <p class="text-xs font-medium text-slate-400">&copy; {{ date('Y') }} {{ config('app.name') }}</p>
    </div>
</aside>

{{-- Main Content --}}
<div class="lg:pl-64">

    {{-- Header --}}
    <header class="sticky top-0 z-20 border-b border-slate-200/80 bg-white/90 backdrop-blur-xl supports-[backdrop-filter]:bg-white/75">
        <div class="flex h-16 items-center justify-between px-4 sm:px-6 lg:px-8">

            {{-- Left Section --}}
            <div class="flex items-center gap-4">
                <button type="button" onclick="toggleSidebar()"
                        class="rounded-lg p-2 text-slate-600 transition hover:bg-slate-100 hover:text-slate-900 lg:hidden">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                    </svg>
                </button>
                <div>
                    <h1 class="text-lg font-bold tracking-tight text-slate-900">{{ $title }}</h1>
                </div>
            </div>

            {{-- Right Section --}}
            <div class="flex items-center gap-4 sm:gap-6">
                <details class="relative">
                    <summary aria-label="Notifikasi" class="relative flex h-10 w-10 cursor-pointer list-none items-center justify-center rounded-xl border border-slate-200 text-slate-600 transition hover:bg-slate-50 hover:text-indigo-600">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M14.25 18.75a2.25 2.25 0 01-4.5 0m9-3H5.25c1.1-1.1 1.5-2.6 1.5-4.5a5.25 5.25 0 1110.5 0c0 1.9.4 3.4 1.5 4.5z" /></svg>
                        @if ($unreadNotificationCount > 0)
                            <span class="absolute -right-1 -top-1 min-w-5 rounded-full bg-rose-600 px-1 text-center text-[10px] font-bold leading-5 text-white">{{ $unreadNotificationCount > 9 ? '9+' : $unreadNotificationCount }}</span>
                        @endif
                    </summary>
                    <div class="absolute right-0 z-50 mt-3 w-[min(22rem,calc(100vw-2rem))] overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-xl">
                        <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3">
                            <p class="text-sm font-semibold text-slate-900">Notifikasi</p>
                            @if ($unreadNotificationCount > 0)
                                <form method="POST" action="{{ route('notifications.read-all') }}">@csrf<button class="text-xs font-semibold text-indigo-600 hover:text-indigo-800">Tandai semua dibaca</button></form>
                            @endif
                        </div>
                        <div class="max-h-80 overflow-y-auto">
                            @forelse ($headerNotifications as $notification)
                                <form method="POST" action="{{ route('notifications.read', $notification->id) }}" class="border-b border-slate-100 last:border-0">
                                    @csrf
                                    <button class="w-full px-4 py-3 text-left transition hover:bg-slate-50 {{ $notification->read_at ? '' : 'bg-indigo-50/60' }}">
                                        <span class="block text-sm font-medium text-slate-800">{{ $notification->data['message'] ?? 'Ada pembaruan proyek.' }}</span>
                                        <span class="mt-1 block truncate text-xs text-slate-500">{{ $notification->data['project_name'] ?? '' }}{{ isset($notification->data['deadline']) ? ' · Tenggat '.\Illuminate\Support\Carbon::parse($notification->data['deadline'])->translatedFormat('d M Y') : '' }}</span>
                                    </button>
                                </form>
                            @empty
                                <p class="px-4 py-8 text-center text-sm text-slate-500">Belum ada notifikasi.</p>
                            @endforelse
                        </div>
                    </div>
                </details>
                <details class="relative">
                    <summary class="flex cursor-pointer list-none items-center gap-2 rounded-xl p-1.5 transition hover:bg-slate-50 focus-visible:outline-none sm:gap-3 sm:pl-2">
                        <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-indigo-600 text-sm font-bold text-white">{{ strtoupper(substr(auth()->user()->nama, 0, 1)) }}</span>
                        <span class="hidden min-w-0 text-left sm:block">
                            <span class="block max-w-36 truncate text-sm font-semibold text-slate-800">{{ auth()->user()->nama }}</span>
                            <span class="block text-xs text-slate-500">{{ ucfirst(auth()->user()->role) }}</span>
                        </span>
                        <svg class="hidden h-4 w-4 text-slate-400 sm:block" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M5.22 7.22a.75.75 0 011.06 0L10 10.94l3.72-3.72a.75.75 0 111.06 1.06l-4.25 4.25a.75.75 0 01-1.06 0L5.22 8.28a.75.75 0 010-1.06z" clip-rule="evenodd" /></svg>
                    </summary>
                    <div class="absolute right-0 z-50 mt-2 w-56 overflow-hidden rounded-2xl border border-slate-200 bg-white p-1.5 shadow-xl">
                        <a href="{{ route('profile.edit') }}" class="block rounded-xl px-3 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50 hover:text-indigo-700">Profil dan keamanan</a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="w-full rounded-xl px-3 py-2.5 text-left text-sm font-semibold text-rose-600 transition hover:bg-rose-50">Keluar dari akun</button>
                        </form>
                    </div>
                </details>
            </div>
        </div>
    </header>

    {{-- Main Content Area --}}
    <main class="min-h-[calc(100vh-4rem)] bg-[#f6f7fb] p-4 sm:p-6 lg:p-8">
        <div class="mx-auto w-full max-w-[1600px]">{{ $slot }}</div>
    </main>
</div>

<script>
    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('overlay');

        sidebar.classList.toggle('-translate-x-full');
        overlay.classList.toggle('hidden');
    }

    // Close sidebar when clicking a menu item on mobile
    document.querySelectorAll('#sidebar a').forEach(link => {
        link.addEventListener('click', () => {
            if (window.innerWidth < 1024) {
                toggleSidebar();
            }
        });
    });
</script>
</body>
</html>
