<x-admin-layout title="Dashboard">
    <div class="space-y-8">

        {{-- Banner --}}
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-emerald-500 to-teal-600 p-6 shadow-lg sm:p-8">
            <div class="relative z-10">
                <p class="text-sm font-medium text-emerald-50">Halo, semangat kerja hari ini 💪</p>
                <h2 class="mt-1 text-2xl font-bold text-white sm:text-3xl">{{ auth()->user()->nama }}</h2>
                <p class="mt-2 max-w-xl text-sm text-emerald-50 sm:text-base">Ini ringkasan project dan task yang lagi kamu pegang.</p>
            </div>
            <div class="absolute -right-10 -top-10 h-48 w-48 rounded-full bg-white/10"></div>
            <div class="absolute -right-4 bottom-0 h-32 w-32 rounded-full bg-white/10"></div>
        </div>

        {{-- Statistik --}}
        <div class="grid grid-cols-2 gap-4 sm:grid-cols-2 lg:grid-cols-4 lg:gap-6">
            @foreach ([
                ['Project Diikuti', $myProjects ?? 0,  'text-indigo-600', 'bg-indigo-50'],
                ['Task Aktif',      $myOngoing ?? 0,   'text-amber-600',  'bg-amber-50'],
                ['Task Selesai',    $myDone ?? 0,      'text-emerald-600','bg-emerald-50'],
                ['Deadline Dekat',  $nearDeadline ?? 0,'text-red-600',    'bg-red-50'],
            ] as [$label, $value, $color, $bg])
                <div class="rounded-xl sm:rounded-2xl {{ $bg }} p-4 sm:p-6 ring-1 ring-slate-200 transition hover:shadow-md">
                    <p class="text-xs sm:text-sm font-medium text-slate-600">{{ $label }}</p>
                    <p class="mt-2 sm:mt-3 text-2xl sm:text-3xl font-bold {{ $color }}">{{ $value }}</p>
                </div>
            @endforeach
        </div>

        {{-- Task Terbaru --}}
        <div class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
            <div class="flex items-center justify-between border-b border-slate-200 px-4 py-4 sm:px-6">
                <h3 class="text-base sm:text-lg font-semibold text-slate-800">Task Terbaru</h3>
                <a href="{{ route('user.projects.index') }}" class="text-xs sm:text-sm font-medium text-indigo-600 hover:text-indigo-700 whitespace-nowrap">Lihat semua →</a>
            </div>

            {{-- Desktop: List View --}}
            <ul class="hidden divide-y divide-slate-100 md:block">
                @forelse ($tasks ?? [] as $task)
                    @php
                        $badge = match ($task->status) {
                            'done'     => 'bg-emerald-50 text-emerald-700',
                            'progress' => 'bg-amber-50 text-amber-700',
                            default    => 'bg-slate-100 text-slate-600',
                        };
                    @endphp
                    <li class="flex items-center justify-between gap-4 px-6 py-4 transition hover:bg-slate-50">
                        <div class="min-w-0 flex-1">
                            <p class="truncate font-medium text-slate-900">{{ $task->project_name }}</p>
                            <p class="text-sm text-slate-500">
                                {{ $task->endUser->nama ?? '-' }}
                                @if ($task->deadline_date)
                                    · Deadline {{ \Carbon\Carbon::parse($task->deadline_date)->translatedFormat('d M Y') }}
                                @endif
                            </p>
                        </div>
                        <span class="shrink-0 rounded-full px-3 py-1 text-xs font-semibold {{ $badge }}">
                            {{ ucfirst($task->status) }}
                        </span>
                    </li>
                @empty
                    <li class="px-6 py-12 text-center">
                        <p class="font-medium text-slate-700">Belum ada task</p>
                        <p class="mt-1 text-sm text-slate-500">Task yang di-assign ke kamu bakal muncul di sini.</p>
                    </li>
                @endforelse
            </ul>

            {{-- Mobile: Card View --}}
            <div class="space-y-3 p-4 md:hidden">
                @forelse ($tasks ?? [] as $task)
                    @php
                        $badge = match ($task->status) {
                            'done'     => 'bg-emerald-50 text-emerald-700',
                            'progress' => 'bg-amber-50 text-amber-700',
                            default    => 'bg-slate-100 text-slate-600',
                        };
                    @endphp
                    <div class="rounded-lg border border-slate-200 bg-slate-50 p-4 transition hover:shadow-md">
                        <div class="mb-3 flex items-start justify-between gap-2">
                            <div class="min-w-0 flex-1">
                                <p class="truncate font-semibold text-slate-900">{{ $task->project_name }}</p>
                                <p class="text-sm text-slate-600">{{ $task->endUser->nama ?? '-' }}</p>
                            </div>
                            <span class="shrink-0 rounded-full px-2 py-1 text-xs font-semibold {{ $badge }}">
                                {{ ucfirst($task->status) }}
                            </span>
                        </div>
                        @if ($task->deadline_date)
                            <div class="flex items-center gap-2 text-sm text-slate-600">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                Deadline {{ \Carbon\Carbon::parse($task->deadline_date)->translatedFormat('d M Y') }}
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="rounded-lg border border-dashed border-slate-300 bg-slate-50 px-4 py-8 text-center">
                        <p class="font-medium text-slate-700">Belum ada task</p>
                        <p class="mt-1 text-sm text-slate-500">Task yang di-assign ke kamu bakal muncul di sini.</p>
                    </div>
                @endforelse
            </div>
        </div>

    </div>
</x-admin-layout>
