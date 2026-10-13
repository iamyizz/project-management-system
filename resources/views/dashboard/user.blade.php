<x-admin-layout title="Dashboard">
    @php
        $statusStyles = [
            'planning' => ['Planning', 'bg-amber-50 text-amber-700'],
            'progress' => ['Berjalan', 'bg-blue-50 text-blue-700'],
            'done' => ['Selesai', 'bg-emerald-50 text-emerald-700'],
            'cancel' => ['Dibatalkan', 'bg-rose-50 text-rose-700'],
        ];
    @endphp

    <div class="space-y-7 sm:space-y-8">
        <section class="flex flex-col gap-5 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:flex-row sm:items-center sm:justify-between sm:p-7">
            <div class="flex items-start gap-4">
                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-indigo-50 text-indigo-700">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75A2.25 2.25 0 016 4.5h12a2.25 2.25 0 012.25 2.25v10.5A2.25 2.25 0 0118 19.5H6a2.25 2.25 0 01-2.25-2.25V6.75zM8.25 9h7.5M8.25 12h7.5m-7.5 3h4.5" /></svg>
                </span>
                <div class="max-w-2xl">
                    <p class="text-xs font-semibold text-indigo-700">Workspace project</p>
                    <h2 class="mt-1 text-xl font-bold tracking-tight text-slate-950 sm:text-2xl">Halo, {{ auth()->user()->nama }}</h2>
                    <p class="mt-1.5 text-sm leading-6 text-slate-500">Lihat prioritas kerja, pantau deadline, dan perbarui progres project yang kamu pegang.</p>
                </div>
            </div>
            <a href="{{ route('user.projects.index') }}" class="inline-flex shrink-0 items-center justify-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700 focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-indigo-500/20">
                    Buka project saya <span aria-hidden="true">&rarr;</span>
            </a>
        </section>

        @if (session('success'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">{{ session('success') }}</div>
        @endif
        @error('status')
            <div role="alert" class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-700">{{ $message }}</div>
        @enderror

        <section class="grid grid-cols-2 gap-3 sm:gap-4 xl:grid-cols-4">
            @foreach ([
                ['Total Project', $myProjects, 'text-slate-900', 'bg-slate-100', 'border-slate-200'],
                ['Project Aktif', $myOngoing, 'text-blue-700', 'bg-blue-50', 'border-blue-100'],
                ['Selesai', $myDone, 'text-emerald-700', 'bg-emerald-50', 'border-emerald-100'],
                ['Perlu Perhatian', $needsAttention, 'text-rose-700', 'bg-rose-50', 'border-rose-100'],
            ] as [$label, $value, $color, $iconBg, $border])
                <div class="workspace-stat {{ $border }}">
                    <div class="flex items-center justify-between gap-2">
                        <p class="text-xs font-medium text-slate-500 sm:text-sm">{{ $label }}</p>
                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg {{ $iconBg }} {{ $color }}">
                            @if ($label === 'Perlu Perhatian')
                                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M8.485 2.495a1.75 1.75 0 013.03 0l6.063 10.94A1.75 1.75 0 0116.048 16H3.952a1.75 1.75 0 01-1.53-2.565l6.063-10.94zM10 6a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 6zm0 7.5a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" /></svg>
                            @else
                                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M4 3.75A1.75 1.75 0 015.75 2h8.5A1.75 1.75 0 0116 3.75v12.5A1.75 1.75 0 0114.25 18h-8.5A1.75 1.75 0 014 16.25V3.75z" /><path fill="#fff" d="M7 6h6v1.2H7zm0 3.2h6v1.2H7zm0 3.2h4v1.2H7z" /></svg>
                            @endif
                        </span>
                    </div>
                    <p class="mt-3 text-2xl font-bold tracking-tight {{ $color }} sm:text-3xl">{{ number_format($value) }}</p>
                    @if ($label === 'Perlu Perhatian')
                        <p class="mt-1 text-[11px] text-slate-500">Terlambat atau deadline 7 hari ke depan</p>
                    @endif
                </div>
            @endforeach
        </section>

        <section class="workspace-panel overflow-hidden">
            <div class="flex flex-wrap items-start justify-between gap-3 border-b border-slate-200 px-5 py-4 sm:px-6">
                <div>
                    <h3 class="font-semibold text-slate-900">Prioritas Deadline</h3>
                    <p class="mt-1 text-xs text-slate-500">Project aktif yang terlambat atau jatuh tempo dalam 7 hari.</p>
                </div>
                <a href="{{ route('user.projects.index') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-800">Semua project <span aria-hidden="true">&rarr;</span></a>
            </div>

            @forelse ($priorityProjects as $project)
                @php
                    [$statusLabel, $statusClass] = $statusStyles[$project->status] ?? [ucfirst($project->status), 'bg-slate-100 text-slate-700'];
                    $deadline = $project->deadline_date;
                    $isOverdue = $deadline->lt(today());
                    $daysUntilDeadline = (int) today()->diffInDays($deadline);
                @endphp
                <article class="border-b border-slate-100 px-5 py-5 last:border-b-0 sm:px-6">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <h4 class="font-semibold text-slate-900">{{ $project->project_name }}</h4>
                                <span class="rounded-full px-2.5 py-1 text-[11px] font-semibold {{ $statusClass }}">{{ $statusLabel }}</span>
                            </div>
                            <p class="mt-1 text-sm text-slate-500">{{ $project->endUser?->nama ?? 'End User belum tersedia' }}</p>
                            <p class="mt-2 inline-flex items-center gap-1.5 text-sm font-medium {{ $isOverdue ? 'text-rose-600' : 'text-amber-700' }}">
                                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M10 2a8 8 0 100 16 8 8 0 000-16zM8.75 5.5a.75.75 0 011.5 0v4.19l2.03 1.22a.75.75 0 11-.78 1.28l-2.39-1.44a.75.75 0 01-.36-.64V5.5z" clip-rule="evenodd" /></svg>
                                @if ($isOverdue)
                                    Terlambat {{ $deadline->diffInDays(today()) }} hari · {{ $deadline->translatedFormat('d M Y') }}
                                @elseif ($daysUntilDeadline === 0)
                                    Jatuh tempo hari ini · {{ $deadline->translatedFormat('d M Y') }}
                                @else
                                    Dalam {{ $daysUntilDeadline }} hari · {{ $deadline->translatedFormat('d M Y') }}
                                @endif
                            </p>
                        </div>

                        <form method="POST" action="{{ route('user.projects.update', $project) }}" class="flex w-full flex-col gap-2 sm:flex-row lg:w-auto">
                            @csrf
                            @method('PUT')
                            <label for="status-{{ $project->id }}" class="sr-only">Update status {{ $project->project_name }}</label>
                            <select id="status-{{ $project->id }}" name="status" class="workspace-input sm:w-44">
                                @foreach (($project->status === 'planning' ? ['progress' => 'Mulai project'] : ['done' => 'Tandai selesai']) as $status => $label)
                                    <option value="{{ $status }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            <button type="submit" class="rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700 focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-indigo-500/20">{{ $project->status === 'planning' ? 'Mulai progress' : 'Tandai selesai' }}</button>
                        </form>
                    </div>
                </article>
            @empty
                <div class="px-5 py-10 text-center sm:px-6">
                    <span class="mx-auto flex h-11 w-11 items-center justify-center rounded-full bg-emerald-50 text-emerald-600">
                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.78-9.22a.75.75 0 00-1.06-1.06l-3.47 3.47-1.47-1.47a.75.75 0 00-1.06 1.06l2 2a.75.75 0 001.06 0l4-4z" clip-rule="evenodd" /></svg>
                    </span>
                    <p class="mt-3 font-semibold text-slate-800">Belum ada deadline yang mendesak</p>
                    <p class="mt-1 text-sm text-slate-500">Project aktif dengan deadline dekat akan muncul di sini.</p>
                </div>
            @endforelse
        </section>

        <section class="workspace-panel overflow-hidden">
            <div class="flex items-center justify-between gap-3 border-b border-slate-200 px-5 py-4 sm:px-6">
                <div>
                    <h3 class="font-semibold text-slate-900">Project Terbaru</h3>
                    <p class="mt-1 text-xs text-slate-500">Project yang terakhir diperbarui.</p>
                </div>
                <a href="{{ route('user.projects.index') }}" class="shrink-0 text-sm font-medium text-indigo-600 hover:text-indigo-800">Lihat semua <span aria-hidden="true">&rarr;</span></a>
            </div>
            <div class="divide-y divide-slate-100">
                @forelse ($recentProjects as $project)
                    @php [$statusLabel, $statusClass] = $statusStyles[$project->status] ?? [ucfirst($project->status), 'bg-slate-100 text-slate-700']; @endphp
                    <div class="flex flex-col gap-2 px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                        <div class="min-w-0">
                            <p class="truncate text-sm font-semibold text-slate-900">{{ $project->project_name }}</p>
                            <p class="mt-1 text-xs text-slate-500">{{ $project->endUser?->nama ?? '-' }}@if ($project->deadline_date) · Deadline {{ $project->deadline_date->translatedFormat('d M Y') }}@endif</p>
                        </div>
                        <span class="w-fit shrink-0 rounded-full px-2.5 py-1 text-xs font-semibold {{ $statusClass }}">{{ $statusLabel }}</span>
                    </div>
                @empty
                    <div class="px-5 py-10 text-center sm:px-6">
                        <p class="font-medium text-slate-700">Belum ada project yang ditugaskan</p>
                        <p class="mt-1 text-sm text-slate-500">Project dari admin akan muncul di sini.</p>
                    </div>
                @endforelse
            </div>
        </section>
    </div>
</x-admin-layout>
