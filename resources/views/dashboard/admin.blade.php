<x-admin-layout title="Dashboard Admin">
    <div class="space-y-8">

        {{-- Banner --}}
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-indigo-600 to-violet-600 p-6 shadow-lg sm:p-8">
            <div class="relative z-10">
                <p class="text-sm font-medium text-indigo-100">Selamat datang kembali 👋</p>
                <h2 class="mt-1 text-2xl font-bold text-white sm:text-3xl">{{ auth()->user()->nama }}</h2>
                <p class="mt-2 max-w-xl text-sm text-indigo-100 sm:text-base">Pantau project, kelola tim, dan atur semuanya dari satu tempat.</p>
            </div>
            <div class="absolute -right-10 -top-10 h-48 w-48 rounded-full bg-white/10"></div>
            <div class="absolute -right-4 bottom-0 h-32 w-32 rounded-full bg-white/10"></div>
        </div>

        {{-- Statistik - 2x2 di Mobile, 4 kolom di Desktop --}}
        @php
            $stats = [
                ['label' => 'Total Users',   'value' => $totalUsers ?? 0,    'class' => 'bg-indigo-50 text-indigo-600'],
                ['label' => 'Total Project', 'value' => $totalProjects ?? 0, 'class' => 'bg-emerald-50 text-emerald-600'],
                ['label' => 'Task Berjalan', 'value' => $ongoingTasks ?? 0,  'class' => 'bg-amber-50 text-amber-600'],
                ['label' => 'Task Selesai',  'value' => $doneTasks ?? 0,     'class' => 'bg-sky-50 text-sky-600'],
            ];
        @endphp

        <div class="grid grid-cols-2 gap-3 sm:grid-cols-2 sm:gap-4 lg:grid-cols-4 lg:gap-6">
            @foreach ($stats as $stat)
                <div class="rounded-lg sm:rounded-2xl bg-white p-4 sm:p-6 shadow-sm ring-1 ring-slate-200 transition hover:shadow-md">
                    <div class="flex items-center justify-between gap-2">
                        <p class="text-xs sm:text-sm font-medium text-slate-500">{{ $stat['label'] }}</p>
                        <span class="shrink-0 rounded-lg px-2 py-0.5 sm:py-1 text-xs font-semibold {{ $stat['class'] }}">Live</span>
                    </div>
                    <p class="mt-2 sm:mt-3 text-xl sm:text-3xl font-bold text-slate-900">{{ $stat['value'] }}</p>
                </div>
            @endforeach
        </div>

        {{-- Menu cepat --}}
        <div>
            <h3 class="mb-4 text-base sm:text-lg font-semibold text-slate-800">Menu Cepat</h3>
            <div class="grid grid-cols-1 gap-4 sm:gap-6 md:grid-cols-3">
                @foreach ([
                    ['Manage Users',    'Tambah, edit, dan hapus user', route('admin.users.index'), 'bg-indigo-600'],
                    ['Manage Projects', 'Kelola semua project',          route('admin.projects.index'), 'bg-emerald-600'],
                    ['Laporan',         'Lihat progres & statistik',     '#',                        'bg-amber-500'],
                ] as [$label, $desc, $url, $bg])
                    <a href="{{ $url }}"
                       class="group flex items-center gap-3 sm:gap-4 rounded-lg sm:rounded-2xl bg-white p-4 sm:p-6 shadow-sm ring-1 ring-slate-200 transition hover:shadow-md hover:ring-indigo-400">
                        <div class="flex h-10 w-10 sm:h-12 sm:w-12 shrink-0 items-center justify-center rounded-lg sm:rounded-xl {{ $bg }} text-base sm:text-lg font-bold text-white transition group-hover:scale-110">
                            {{ substr($label, 0, 1) }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="truncate text-sm sm:text-base font-semibold text-slate-900">{{ $label }}</p>
                            <p class="truncate text-xs sm:text-sm text-slate-500">{{ $desc }}</p>
                        </div>
                        <svg class="h-4 w-4 sm:h-5 sm:w-5 shrink-0 text-slate-400 transition group-hover:translate-x-1 group-hover:text-indigo-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                        </svg>
                    </a>
                @endforeach
            </div>
        </div>

        {{-- Project Terbaru --}}
        <div class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
            <div class="flex items-center justify-between border-b border-slate-200 px-4 py-4 sm:px-6">
                <h3 class="text-base sm:text-lg font-semibold text-slate-800">Project Terbaru</h3>
                <a href="{{ route('admin.projects.index') }}" class="text-xs sm:text-sm font-medium text-indigo-600 hover:text-indigo-700 whitespace-nowrap">Lihat semua →</a>
            </div>

            {{-- Desktop: Tabel --}}
            <div class="hidden overflow-x-auto md:block">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 text-xs uppercase text-slate-500">
                        <tr>
                            <th class="px-6 py-3 font-semibold">No</th>
                            <th class="px-6 py-3 font-semibold">Project Name</th>
                            <th class="px-6 py-3 font-semibold">End User</th>
                            <th class="px-6 py-3 font-semibold">PIC</th>
                            <th class="px-6 py-3 font-semibold">Deadline</th>
                            <th class="px-6 py-3 font-semibold">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($recentProjects ?? [] as $project)
                            @php
                                $statuses = [
                                    'planning' => 'bg-yellow-100 text-yellow-700',
                                    'progress' => 'bg-blue-100 text-blue-700',
                                    'done'     => 'bg-green-100 text-green-700',
                                    'cancel'   => 'bg-red-100 text-red-700',
                                ];
                                $badge = $statuses[$project->status] ?? 'bg-slate-100 text-slate-600';
                                $overdue = $project->deadline_date
                                    && $project->deadline_date->lt(now())
                                    && !in_array($project->status, ['done', 'cancel']);
                            @endphp
                            <tr class="transition hover:bg-slate-50">
                                <td class="px-6 py-4 text-slate-500">{{ $loop->iteration }}</td>
                                <td class="px-6 py-4 font-medium text-slate-900">{{ $project->project_name }}</td>
                                <td class="px-6 py-4 text-slate-700">{{ $project->endUser?->nama ?? '-' }}</td>
                                <td class="px-6 py-4 text-slate-700">{{ $project->pic?->nama ?? '-' }}</td>
                                <td class="whitespace-nowrap px-6 py-4 {{ $overdue ? 'font-semibold text-red-600' : 'text-slate-700' }}">
                                    @if ($project->deadline_date)
                                        {{ $project->deadline_date->translatedFormat('d M Y') }}
                                        @if ($overdue)
                                            <span class="ml-2 rounded-full bg-red-100 px-2 py-0.5 text-xs font-medium text-red-700">Overdue</span>
                                        @endif
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $badge }}">
                                        {{ ucfirst($project->status) }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-10 text-center text-slate-500">Belum ada project</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Mobile: Card View --}}
            <div class="space-y-3 p-4 sm:p-6 md:hidden">
                @forelse ($recentProjects ?? [] as $project)
                    @php
                        $statuses = [
                            'planning' => 'bg-yellow-100 text-yellow-700',
                            'progress' => 'bg-blue-100 text-blue-700',
                            'done'     => 'bg-green-100 text-green-700',
                            'cancel'   => 'bg-red-100 text-red-700',
                        ];
                        $badge = $statuses[$project->status] ?? 'bg-slate-100 text-slate-600';
                        $overdue = $project->deadline_date
                            && $project->deadline_date->lt(now())
                            && !in_array($project->status, ['done', 'cancel']);
                    @endphp
                    <div class="rounded-lg border border-slate-200 bg-slate-50 p-4 transition hover:shadow-md">
                        <div class="mb-3 flex items-start justify-between gap-2">
                            <div class="min-w-0 flex-1">
                                <p class="truncate font-semibold text-slate-900">{{ $project->project_name }}</p>
                                <p class="truncate text-sm text-slate-600">{{ $project->endUser?->nama ?? '-' }}</p>
                            </div>
                            <span class="shrink-0 rounded-full px-2.5 py-1 text-xs font-semibold {{ $badge }}">
                                {{ ucfirst($project->status) }}
                            </span>
                        </div>

                        <div class="space-y-2 text-sm text-slate-700">
                            <div class="flex justify-between">
                                <span class="text-slate-500">PIC:</span>
                                <span class="font-medium">{{ $project->pic?->nama ?? '-' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-500">Deadline:</span>
                                <span class="font-medium {{ $overdue ? 'text-red-600' : '' }}">
                                    {{ $project->deadline_date?->translatedFormat('d M Y') ?? '-' }}
                                    @if ($overdue)
                                        <span class="ml-1 text-red-600">(Overdue)</span>
                                    @endif
                                </span>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="rounded-lg border border-dashed border-slate-300 bg-slate-50 px-4 py-8 text-center">
                        <p class="text-sm text-slate-500">Belum ada project</p>
                    </div>
                @endforelse
            </div>
        </div>

    </div>
</x-admin-layout>
