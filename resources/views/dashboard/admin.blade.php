<x-admin-layout title="Dashboard Admin">
    <div class="space-y-7 sm:space-y-8">

        {{-- Banner --}}
        <div class="flex flex-col gap-5 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:flex-row sm:items-center sm:justify-between sm:p-7">
            <div class="flex items-start gap-4">
                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-indigo-50 text-indigo-700">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3v17.25h17.25M7.5 14.25l3.75-3.75 3 3 5.25-6" /></svg>
                </span>
                <div>
                    <p class="text-xs font-semibold text-indigo-700">Ringkasan workspace</p>
                    <h2 class="mt-1 text-xl font-bold tracking-tight text-slate-950 sm:text-2xl">Selamat datang, {{ auth()->user()->nama }}</h2>
                    <p class="mt-1.5 max-w-xl text-sm leading-6 text-slate-500">Pantau project, kelola tim, dan lihat perkembangan operasional perusahaan dari satu tempat.</p>
                </div>
            </div>
            <a href="{{ route('admin.projects.index') }}" class="inline-flex shrink-0 items-center justify-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700 focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-indigo-500/20">
                    Kelola project <span aria-hidden="true">&rarr;</span>
            </a>
        </div>

        {{-- Statistik - 2x2 di Mobile, 4 kolom di Desktop --}}
        @php
            $stats = [
                ['label' => 'Total user',   'value' => $totalUsers ?? 0,    'class' => 'bg-indigo-50 text-indigo-700', 'note' => 'Akun terdaftar'],
                ['label' => 'Total project', 'value' => $totalProjects ?? 0, 'class' => 'bg-emerald-50 text-emerald-700', 'note' => 'Seluruh project'],
                ['label' => 'Sedang berjalan', 'value' => $ongoingTasks ?? 0,  'class' => 'bg-amber-50 text-amber-700', 'note' => 'Status progress'],
                ['label' => 'Project selesai',  'value' => $doneTasks ?? 0,     'class' => 'bg-sky-50 text-sky-700', 'note' => 'Status done'],
            ];
        @endphp

        <div class="grid grid-cols-2 gap-3 sm:grid-cols-2 sm:gap-4 lg:grid-cols-4 lg:gap-6">
            @foreach ($stats as $stat)
                <div class="workspace-stat">
                    <div class="flex items-center justify-between gap-2">
                        <p class="text-xs font-semibold text-slate-500 sm:text-sm">{{ $stat['label'] }}</p>
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl {{ $stat['class'] }}">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M16 18.5a4 4 0 00-8 0m12 0a4 4 0 00-3.5-3.97M4 18.5a4 4 0 013.5-3.97M15.5 7.5a3.5 3.5 0 11-7 0 3.5 3.5 0 017 0zm4 1a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0zM9.5 8.5a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" /></svg>
                        </span>
                    </div>
                    <p class="mt-3 text-2xl font-bold tracking-tight text-slate-950 sm:text-3xl">{{ number_format($stat['value']) }}</p>
                    <p class="mt-1 text-xs text-slate-500">{{ $stat['note'] }}</p>
                </div>
            @endforeach
        </div>

        {{-- Menu cepat --}}
        <div>
            <div class="mb-4 flex items-end justify-between gap-3">
                <div><p class="workspace-kicker">Akses cepat</p><h3 class="mt-1 text-lg font-bold tracking-tight text-slate-900">Menu utama</h3></div>
            </div>
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-4">
                @foreach ([
                    ['Manage Users',    'Atur akses dan akun tim', route('admin.users.index'), 'bg-indigo-600'],
                    ['End Users', 'Kelola data customer', route('admin.end-users.index'), 'bg-sky-600'],
                    ['Projects', 'Kelola seluruh project', route('admin.projects.index'), 'bg-emerald-600'],
                    ['Laporan',         'Pantau progres dan nilai',     route('admin.reports.index'), 'bg-amber-500'],
                ] as [$label, $desc, $url, $bg])
                    <a href="{{ $url }}"
                       class="group flex items-center gap-3 rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm shadow-slate-900/[0.03] transition duration-200 hover:-translate-y-0.5 hover:border-indigo-200 hover:shadow-md sm:gap-4 sm:p-5">
                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl {{ $bg }} text-base font-bold text-white shadow-sm transition group-hover:scale-105">
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
        <div class="workspace-panel overflow-hidden">
            <div class="flex items-center justify-between gap-4 border-b border-slate-100 px-5 py-5 sm:px-6">
                <div><p class="workspace-kicker">Aktivitas terbaru</p><h3 class="mt-1 text-lg font-bold tracking-tight text-slate-900">Project terbaru</h3></div>
                <a href="{{ route('admin.projects.index') }}" class="shrink-0 rounded-xl px-3 py-2 text-sm font-semibold text-indigo-700 transition hover:bg-indigo-50">Semua project <span aria-hidden="true">&rarr;</span></a>
            </div>

            {{-- Desktop: Tabel --}}
            <div class="hidden overflow-x-auto md:block">
                <table class="workspace-table w-full text-left text-sm">
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
