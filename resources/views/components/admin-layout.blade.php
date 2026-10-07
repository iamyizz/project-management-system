@props(['title' => 'Dashboard'])

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title }} | {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-50 text-slate-800 antialiased">

@php
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
            ['label' => 'Dashboard',    'route' => 'dashboard',         'icon' => $icons['home']],
            ['label' => 'Manage Users', 'route' => 'admin.users.index', 'icon' => $icons['users']],
            ['label' => 'End Users', 'route' => 'admin.end-users.index', 'icon' => $icons['building']],
            ['label' => 'Projects',     'route' => 'admin.projects.index', 'icon' => $icons['folder']],
            ['label' => 'Laporan',      'route' => null,                'icon' => $icons['chart']],
        ]
        : [
            ['label' => 'Dashboard',    'route' => 'user.dashboard',         'icon' => $icons['home']],
            ['label' => 'Project Saya', 'route' => 'user.projects.index',    'icon' => $icons['folder']],
            ['label' => 'Task Saya',    'route' => null,                'icon' => $icons['check']],
        ];
@endphp

{{-- Overlay mobile --}}
<div id="overlay" class="fixed inset-0 z-30 hidden bg-slate-900/40 lg:hidden" onclick="toggleSidebar()"></div>

{{-- Sidebar --}}
<aside id="sidebar"
       class="fixed inset-y-0 left-0 z-40 flex w-64 -translate-x-full flex-col border-r border-slate-200 bg-white transition-transform duration-200 lg:translate-x-0">

    {{-- Logo Section --}}
    <div class="border-b border-slate-200 px-6 py-6">
        <div class="flex items-center gap-4">
            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-indigo-600 to-indigo-700 text-lg font-bold text-white shadow-md">
                N
            </div>
            <div class="min-w-0">
                <h2 class="text-sm font-bold text-slate-900 mb-1">Project Management System</h2>
                <p class="text-xs font-medium text-indigo-600">Nuke Teknologi Utama</p>
            </div>
        </div>
    </div>

    {{-- Menu Section --}}
    <nav class="flex-1 overflow-y-auto px-4 py-6">
        <p class="mb-4 px-2 text-xs font-semibold uppercase tracking-widest text-slate-400">Menu Utama</p>

        <div class="space-y-2">
            @foreach ($menus as $menu)
                @php
                    $active = $menu['route'] && request()->routeIs(str_replace('.index', '', $menu['route']) . '*');
                @endphp
                <a href="{{ $menu['route'] ? route($menu['route']) : '#' }}"
                   class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition duration-200
                          {{ $active
                              ? 'bg-indigo-50 text-indigo-700 shadow-sm ring-1 ring-indigo-200'
                              : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                    <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $menu['icon'] }}" />
                    </svg>
                    <span>{{ $menu['label'] }}</span>
                </a>
            @endforeach
        </div>
    </nav>

    {{-- Footer Section --}}
    <div class="border-t border-slate-200 px-6 py-4">
        <p class="text-center text-xs text-slate-400">&copy; {{ date('Y') }} {{ config('app.name') }}</p>
    </div>
</aside>

{{-- Main Content --}}
<div class="lg:pl-64">

    {{-- Header --}}
    <header class="sticky top-0 z-20 border-b border-slate-200 bg-white/95 backdrop-blur supports-[backdrop-filter]:bg-white/60">
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
                    <h1 class="text-base font-semibold text-slate-900 sm:text-lg">{{ $title }}</h1>
                </div>
            </div>

            {{-- Right Section --}}
            <div class="flex items-center gap-4 sm:gap-6">
                {{-- User Info --}}
                <div class="hidden flex-col text-right sm:flex">
                    <p class="text-sm font-semibold text-slate-900">{{ auth()->user()->nama }}</p>
                    <p class="text-xs text-slate-500">{{ ucfirst(auth()->user()->role) }}</p>
                </div>

                {{-- Avatar --}}
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-gradient-to-br from-indigo-600 to-indigo-700 font-semibold text-white shadow-md">
                    {{ strtoupper(substr(auth()->user()->nama, 0, 1)) }}
                </div>

                {{-- Logout Button --}}
                <form method="POST" action="{{ route('logout') }}" class="flex">
                    @csrf
                    <button type="submit"
                            class="rounded-lg px-3 py-2 text-sm font-medium text-slate-600 transition hover:bg-red-50 hover:text-red-600">
                        Logout
                    </button>
                </form>
            </div>
        </div>
    </header>

    {{-- Main Content Area --}}
    <main class="min-h-[calc(100vh-4rem)] bg-slate-50 p-4 sm:p-6 lg:p-8">
        {{ $slot }}
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
