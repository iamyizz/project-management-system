<x-admin-layout title="Laporan">
    @php
        $statusLabels = [
            'planning' => 'Direncanakan',
            'progress' => 'Berjalan',
            'done' => 'Selesai',
            'cancel' => 'Dibatalkan',
        ];
        $money = fn ($value) => 'Rp ' . number_format((float) $value, 0, ',', '.');
        $input = 'w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20';
    @endphp

    <div class="space-y-7 sm:space-y-8">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="workspace-kicker">Analitik operasional</p>
                <h2 class="mt-1 text-2xl font-bold tracking-tight text-slate-950 sm:text-3xl">Laporan Proyek</h2>
                <p class="mt-2 text-sm text-slate-500">Pantau performa, nilai, margin, dan tenggat proyek.</p>
            </div>
            <div class="flex flex-wrap gap-2 print:hidden">
                <a href="{{ route('admin.reports.export', request()->query()) }}" data-report-export class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700">
                    <span aria-hidden="true">↓</span> Unduh CSV
                </a>
                <a href="{{ route('admin.reports.index', [...request()->except('print'), 'print' => 1]) }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:border-indigo-200 hover:bg-indigo-50 hover:text-indigo-700">
                    Cetak / PDF
                </a>
            </div>
        </div>

        <form id="reportFilters" method="GET" action="{{ route('admin.reports.index') }}" data-live-filter data-results-target="#reportResults" class="workspace-panel grid gap-3 p-4 sm:grid-cols-2 sm:p-5 lg:grid-cols-7">
            <div class="col-span-full flex items-center gap-2 text-sm font-semibold text-slate-800"><span class="flex h-8 w-8 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600">⌕</span> Filter laporan</div>
            <input type="hidden" name="deadline_scope" value="{{ $filters['deadline_scope'] ?? '' }}">
            <label class="block min-w-0 text-xs font-semibold text-slate-600">
                Status
                <select name="status" class="workspace-input mt-1.5 h-11">
                    <option value="">Semua status</option>
                    @foreach ($statusLabels as $value => $label)
                        <option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </label>
            <label class="block min-w-0 text-xs font-semibold text-slate-600">
                PIC
                <select name="pic_id" class="workspace-input mt-1.5 h-11">
                    <option value="">Semua PIC</option>
                    @foreach ($pics as $pic)
                        <option value="{{ $pic->id }}" @selected((string) ($filters['pic_id'] ?? '') === (string) $pic->id)>{{ $pic->nama }}</option>
                    @endforeach
                </select>
            </label>
            <label class="block min-w-0 text-xs font-semibold text-slate-600">
                End user
                <select name="end_user_id" class="workspace-input mt-1.5 h-11">
                    <option value="">Semua end user</option>
                    @foreach ($endUsers as $endUser)
                        <option value="{{ $endUser->id }}" @selected((string) ($filters['end_user_id'] ?? '') === (string) $endUser->id)>{{ $endUser->nama }}</option>
                    @endforeach
                </select>
            </label>
            @foreach ([
                ['name' => 'date_from', 'label' => 'Tenggat mulai', 'aria' => 'Pilih tanggal tenggat mulai'],
                ['name' => 'date_to', 'label' => 'Tenggat sampai', 'aria' => 'Pilih tanggal tenggat akhir'],
            ] as $dateFilter)
                <label class="block min-w-0 text-xs font-semibold text-slate-600">
                    {{ $dateFilter['label'] }}
                    <input type="text" name="{{ $dateFilter['name'] }}" value="{{ $filters[$dateFilter['name']] ?? '' }}" placeholder="Pilih tanggal" autocomplete="off" class="workspace-input mt-1.5 h-11" data-flatpickr aria-label="{{ $dateFilter['aria'] }}">
                </label>
            @endforeach
            <div class="col-span-full flex flex-col gap-3 border-t border-slate-100 pt-3 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <span class="mb-2 block text-xs font-semibold text-slate-600">Periode cepat</span>
                    <div class="flex flex-wrap gap-2">
                        <button type="button" data-range-preset="month" class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-600 transition hover:border-indigo-200 hover:bg-indigo-50 hover:text-indigo-700">Bulan ini</button>
                        <button type="button" data-range-preset="quarter" class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-600 transition hover:border-indigo-200 hover:bg-indigo-50 hover:text-indigo-700">Kuartal ini</button>
                        <button type="button" data-range-preset="year" class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-600 transition hover:border-indigo-200 hover:bg-indigo-50 hover:text-indigo-700">Tahun ini</button>
                    </div>
                </div>
                <a href="{{ route('admin.reports.index') }}" data-filter-reset class="inline-flex justify-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-600 transition hover:border-indigo-200 hover:bg-indigo-50 hover:text-indigo-700">Atur ulang filter</a>
            </div>
        </form>
        <p data-filter-status role="status" aria-live="polite" class="sr-only"></p>

        <div id="reportResults" data-live-results aria-live="polite" class="space-y-6">
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
            @foreach ([
                ['Total proyek', number_format((int) $summary->total_projects)],
                ['Nilai proyek', $money($summary->total_value)],
                ['Biaya internal', $money($summary->total_cost)],
                ['Total margin', $money($summary->total_margin)],
                ['Margin', number_format($marginPercentage, 1, ',', '.') . '%'],
            ] as [$label, $value])
                <div class="workspace-stat">
                    <p class="text-sm font-medium text-slate-500">{{ $label }}</p>
                    <p class="mt-2 break-words text-xl font-bold text-slate-900">{{ $value }}</p>
                </div>
            @endforeach
        </div>

        <section class="grid gap-4 sm:grid-cols-2">
            <button type="button" data-deadline-scope="overdue" aria-pressed="{{ ($filters['deadline_scope'] ?? '') === 'overdue' ? 'true' : 'false' }}" class="group flex w-full items-center justify-between gap-4 rounded-2xl border border-rose-200 bg-rose-50 p-5 text-left transition hover:border-rose-300 hover:bg-rose-100 focus-visible:outline-rose-500 {{ ($filters['deadline_scope'] ?? '') === 'overdue' ? 'ring-2 ring-rose-300' : '' }}">
                <div><p class="text-sm font-semibold text-rose-800">Proyek terlambat</p><p class="mt-1 text-xs text-rose-700">Tenggat terlewati, status belum selesai atau dibatalkan.</p></div>
                <span class="flex items-center gap-2 text-3xl font-bold tabular-nums text-rose-800">{{ number_format($overdueCount) }}<span class="text-[11px] font-semibold text-rose-600 transition group-hover:translate-x-0.5">Lihat →</span></span>
            </button>
            <button type="button" data-deadline-scope="upcoming" aria-pressed="{{ ($filters['deadline_scope'] ?? '') === 'upcoming' ? 'true' : 'false' }}" class="group flex w-full items-center justify-between gap-4 rounded-2xl border border-amber-200 bg-amber-50 p-5 text-left transition hover:border-amber-300 hover:bg-amber-100 focus-visible:outline-amber-500 {{ ($filters['deadline_scope'] ?? '') === 'upcoming' ? 'ring-2 ring-amber-300' : '' }}">
                <div><p class="text-sm font-semibold text-amber-800">Tenggat 30 hari ke depan</p><p class="mt-1 text-xs text-amber-700">Proyek aktif yang perlu dipantau.</p></div>
                <span class="flex items-center gap-2 text-3xl font-bold tabular-nums text-amber-800">{{ number_format($upcomingCount) }}<span class="text-[11px] font-semibold text-amber-700 transition group-hover:translate-x-0.5">Lihat →</span></span>
            </button>
        </section>

        @php
            $statusOffset = 0;
            $maxMonthlyValue = max(1, $monthlyFinancials->max(fn ($month) => max(abs($month['value']), abs($month['margin']))));
        @endphp

        <div class="grid gap-4 lg:grid-cols-2">
            <section class="workspace-panel p-5 sm:p-6 workspace-stat">
                <div class>
                    <h3 class="font-semibold text-slate-900">Komposisi Status</h3>
                    <p class="mt-1 text-xs text-slate-500">Sebaran status proyek yang sesuai filter.</p>
                </div>
                <div class="mt-5 flex flex-wrap items-center justify-center gap-6 sm:justify-start">
                    <div class="report-status-chart relative h-36 w-36 shrink-0" role="img" aria-label="Diagram komposisi status proyek">
                        <svg viewBox="0 0 120 120" class="h-full w-full" aria-hidden="true">
                            <g transform="rotate(-90 60 60)">
                                <circle cx="60" cy="60" r="46" fill="none" stroke="#e2e8f0" stroke-width="14" pathLength="100" />
                                @foreach ($statusSegments as $segment)
                                    @if ($segment['percent'] > 0)
                                        <circle cx="60" cy="60" r="46" fill="none" stroke="{{ $segment['color'] }}" stroke-width="14" stroke-dasharray="{{ $segment['percent'] }} 100" stroke-dashoffset="{{ -$statusOffset }}" pathLength="100" />
                                        @php $statusOffset += $segment['percent']; @endphp
                                    @endif
                                @endforeach
                            </g>
                            <circle cx="60" cy="60" r="34" fill="#fff" />
                        </svg>
                        <div class="absolute inset-0 flex flex-col items-center justify-center">
                            <span class="text-2xl font-bold text-slate-900">{{ number_format((int) $summary->total_projects) }}</span>
                            <span class="text-xs text-slate-500">Proyek</span>
                        </div>
                    </div>
                    <ul class="space-y-2.5 text-sm">
                        @foreach ($statusSegments as $segment)
                            <li class="flex items-center gap-2 text-slate-600">
                                <span class="h-2.5 w-2.5 rounded-full" style="background-color: {{ $segment['color'] }}"></span>
                                <span>{{ $statusLabels[$segment['status']] }}</span>
                                <span class="font-semibold text-slate-900">{{ $segment['count'] }}</span>
                                <span class="text-slate-400">({{ number_format($segment['percent'], 1) }}%)</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </section>

            <section class="workspace-panel p-5 sm:p-6 workspace-stat">
                <div>
                    <h3 class="font-semibold text-slate-900">Tenggat terdekat</h3>
                    <p class="mt-1 text-xs text-slate-500">Proyek aktif berikutnya yang perlu dipantau.</p>
                </div>
                <div class="mt-4 divide-y divide-slate-100">
                    @forelse ($upcomingProjects as $upcomingProject)
                        @php $daysUntilDeadline = (int) today()->diffInDays($upcomingProject->deadline_date, false); @endphp
                        <div class="flex items-center justify-between gap-3 py-3 first:pt-0 last:pb-0">
                            <div class="min-w-0">
                                <p class="truncate text-sm font-semibold text-slate-800">{{ $upcomingProject->project_name }}</p>
                                <p class="mt-1 truncate text-xs text-slate-500">{{ $upcomingProject->pic?->nama ?? 'Belum ada PIC' }} · {{ $upcomingProject->deadline_date?->translatedFormat('d M Y') }}</p>
                            </div>
                            <span class="shrink-0 rounded-full px-2.5 py-1 text-xs font-semibold {{ $daysUntilDeadline <= 7 ? 'bg-rose-50 text-rose-700' : 'bg-amber-50 text-amber-700' }}">
                                {{ $daysUntilDeadline === 0 ? 'Hari ini' : $daysUntilDeadline . ' hari lagi' }}
                            </span>
                        </div>
                    @empty
                        <p class="py-8 text-center text-sm text-slate-500">Tidak ada deadline aktif yang akan datang.</p>
                    @endforelse
                </div>
            </section>
        </div>

        <section class="workspace-panel p-5 transition duration-200 hover:-translate-y-0.5 hover:border-indigo-200 hover:shadow-md sm:p-6 workspace-stat">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <h3 class="font-semibold text-slate-900">Tren Margin / Nilai per Bulan</h3>
                    <p class="mt-1 text-xs text-slate-500">Total nilai dan margin proyek yang dibuat dalam 12 bulan terakhir.</p>
                </div>
                <div class="flex flex-wrap gap-4 text-xs font-medium text-slate-600">
                    <span class="inline-flex items-center gap-2"><span class="h-2.5 w-2.5 rounded-sm bg-indigo-500"></span>Nilai proyek</span>
                    <span class="inline-flex items-center gap-2"><span class="h-2.5 w-2.5 rounded-sm bg-emerald-500"></span>Margin</span>
                </div>
            </div>
            @if ($monthlyFinancials->isNotEmpty())
                <div class="report-chart mt-6 pb-2">
                    <div class="flex gap-2 sm:gap-3" aria-label="Grafik tren nilai proyek dan margin per bulan">
                        <div class="flex h-48 w-11 shrink-0 flex-col justify-between pb-1 text-right text-[9px] tabular-nums text-slate-500 sm:w-16 sm:text-[10px]" aria-hidden="true">
                            @foreach ([1, 0.75, 0.5, 0.25, 0] as $tick)
                                <span>{{ $tick === 0 ? 'Rp 0' : 'Rp '.number_format(($maxMonthlyValue * $tick) / 1000000, 0, ',', '.').' jt' }}</span>
                            @endforeach
                        </div>
                        <div class="relative flex h-48 min-w-0 flex-1 items-end border-b border-slate-200">
                            <div class="pointer-events-none absolute inset-x-0 top-0 flex h-full flex-col justify-between pb-px" aria-hidden="true">
                                @foreach ([1, 0.75, 0.5, 0.25, 0] as $tick)
                                    <span class="border-t {{ $tick === 0 ? 'border-slate-200' : 'border-slate-100 border-dashed' }}"></span>
                                @endforeach
                            </div>
                            <div class="relative z-10 flex h-full w-full items-end gap-0.5 sm:gap-4">
                        @foreach ($monthlyFinancials as $month)
                            @php
                                $valueHeight = $month['value'] > 0 ? max(2, ($month['value'] / $maxMonthlyValue) * 88) : 0;
                                $marginHeight = $month['margin'] != 0 ? max(2, (abs($month['margin']) / $maxMonthlyValue) * 88) : 0;
                                $formatCompactMoney = fn ($amount) => 'Rp '.number_format((float) $amount, 0, ',', '.');
                            @endphp
                            <div class="flex h-full min-w-0 flex-1 flex-col items-center justify-end">
                                <div class="flex h-full w-full items-end justify-center gap-1.5">
                    <button type="button" aria-label="{{ $month['label'] }} · Nilai proyek: {{ $formatCompactMoney($month['value']) }}" title="{{ $month['label'] }} · Nilai proyek: {{ $formatCompactMoney($month['value']) }}" class="report-chart-bar report-chart-bar-value group/bar relative w-1/3 max-w-8 rounded-t-md bg-indigo-500 transition-colors hover:bg-indigo-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-400" style="height: {{ $valueHeight }}%">
                        <span class="pointer-events-none absolute {{ $loop->first ? 'left-0 translate-x-0' : ($loop->last ? 'right-0 translate-x-0' : 'left-1/2 -translate-x-1/2') }} top-1 z-30 hidden whitespace-nowrap rounded-lg bg-slate-900 px-2.5 py-1.5 text-[10px] font-semibold text-white shadow-lg group-hover/bar:block group-focus-visible/bar:block">Nilai proyek<br>{{ $formatCompactMoney($month['value']) }}</span>
                                    </button>
                                    <button type="button" aria-label="{{ $month['label'] }} · Margin: {{ $formatCompactMoney($month['margin']) }}" title="{{ $month['label'] }} · Margin: {{ $formatCompactMoney($month['margin']) }}" class="report-chart-bar {{ $month['margin'] < 0 ? 'report-chart-bar-negative bg-rose-500 hover:bg-rose-700' : 'report-chart-bar-margin bg-emerald-500 hover:bg-emerald-700' }} group/bar relative w-1/3 max-w-8 rounded-t-md transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-400" style="height: {{ $marginHeight }}%">
                                        <span class="pointer-events-none absolute {{ $loop->first ? 'left-0 translate-x-0' : ($loop->last ? 'right-0 translate-x-0' : 'left-1/2 -translate-x-1/2') }} top-1 z-30 hidden whitespace-nowrap rounded-lg bg-slate-900 px-2.5 py-1.5 text-[10px] font-semibold text-white shadow-lg group-hover/bar:block group-focus-visible/bar:block">Margin<br>{{ $formatCompactMoney($month['margin']) }}</span>
                                    </button>
                                </div>
                                <span class="report-month-short mt-2 shrink-0 whitespace-nowrap text-[9px] font-medium text-slate-500 sm:text-[10px]">{{ str($month['label'])->before(' ') }}</span>
                                <span class="report-month-full hidden whitespace-nowrap text-[10px] font-medium text-slate-500 sm:mt-2 sm:block">{{ $month['label'] }}</span>
                            </div>
                        @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <div class="mt-6 flex h-40 items-center justify-center rounded-xl bg-slate-50 px-4 text-center text-sm text-slate-500">Belum ada data keuangan proyek dalam 12 bulan terakhir.</div>
            @endif
        </section>

        <section data-report-table class="workspace-panel scroll-mt-6 workspace-stat overflow-hidden">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 px-5 py-5 sm:px-6">
                <div><p class="workspace-kicker">Data detail</p><h3 class="mt-1 text-lg font-bold tracking-tight text-slate-900">Rincian proyek</h3></div>
                <div class="flex flex-wrap items-center gap-3">
                    <div class="flex flex-wrap gap-2 text-xs">
                        @foreach ($statusLabels as $status => $label)
                            <span class="rounded-full bg-slate-100 px-3 py-1 text-slate-600">{{ $label }}: {{ $statusCounts[$status] ?? 0 }}</span>
                        @endforeach
                    </div>
                </div>
            </div>
            <div class="flex flex-col gap-2 border-b border-slate-100 p-4 sm:flex-row sm:items-center sm:justify-between sm:px-6 print:hidden">
                <label class="relative block w-full sm:max-w-sm">
                    <span class="sr-only">Cari rincian proyek</span>
                    <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-slate-400" aria-hidden="true">⌕</span>
                    <input form="reportFilters" type="search" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Cari proyek, PIC, atau pelanggan…" class="workspace-input h-10 pl-9" autocomplete="off">
                </label>
                @unless ($isPrint)
                    <p class="text-xs text-slate-500">Menampilkan {{ $projects->firstItem() ?? 0 }}–{{ $projects->lastItem() ?? 0 }} dari {{ $projects->total() }} proyek</p>
                @endunless
            </div>
            <div class="report-mobile-list divide-y divide-slate-100 md:hidden">
                @forelse ($projects as $project)
                    @php
                        $statusClass = match ($project->status) {
                            'planning' => 'bg-amber-50 text-amber-700 ring-amber-200',
                            'progress' => 'bg-indigo-50 text-indigo-700 ring-indigo-200',
                            'done' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
                            'cancel' => 'bg-rose-50 text-rose-700 ring-rose-200',
                            default => 'bg-slate-50 text-slate-700 ring-slate-200',
                        };
                    @endphp
                    <article class="space-y-3 p-4">
                        <div class="flex items-start justify-between gap-3">
                            <h4 class="min-w-0 break-words text-sm font-semibold text-slate-900">{{ $project->project_name }}</h4>
                            <span class="shrink-0 inline-flex rounded-full px-2.5 py-1 text-[10px] font-semibold ring-1 ring-inset {{ $statusClass }}">{{ $statusLabels[$project->status] ?? ucfirst($project->status) }}</span>
                        </div>
                        <dl class="grid grid-cols-2 gap-x-3 gap-y-2 text-xs">
                            <div class="min-w-0"><dt class="text-slate-400">Pelanggan</dt><dd class="mt-0.5 truncate font-medium text-slate-700">{{ $project->endUser?->nama ?? '-' }}</dd></div>
                            <div class="min-w-0"><dt class="text-slate-400">PIC</dt><dd class="mt-0.5 truncate font-medium text-slate-700">{{ $project->pic?->nama ?? '-' }}</dd></div>
                            <div><dt class="text-slate-400">Tenggat</dt><dd class="mt-0.5 font-medium text-slate-700">{{ $project->deadline_date?->translatedFormat('d M Y') ?? '-' }}</dd></div>
                        </dl>
                        <div class="grid grid-cols-3 gap-2 rounded-xl bg-slate-50 p-3 text-[10px]">
                            <div class="min-w-0"><p class="text-slate-400">Nilai</p><p class="mt-1 break-words font-semibold text-slate-800">{{ $project->project_value !== null ? $money($project->project_value) : '-' }}</p></div>
                            <div class="min-w-0"><p class="text-slate-400">Biaya</p><p class="mt-1 break-words font-semibold text-slate-800">{{ $project->internal_cost !== null ? $money($project->internal_cost) : '-' }}</p></div>
                            <div class="min-w-0"><p class="text-slate-400">Margin</p><p class="mt-1 break-words font-semibold {{ (float) $project->margin < 0 ? 'text-rose-700' : 'text-slate-800' }}">{{ $project->margin !== null ? $money($project->margin) : '-' }}</p></div>
                        </div>
                    </article>
                @empty
                    <p class="px-5 py-10 text-center text-sm text-slate-500">Belum ada proyek untuk filter ini.</p>
                @endforelse
            </div>
            <div class="report-desktop-table hidden overflow-x-auto md:block">
                <table class="workspace-table w-full text-left text-sm">
                    <thead class="bg-slate-50 text-xs uppercase text-slate-500">
                        <tr>
                            <th class="px-5 py-3 font-semibold">Proyek</th>
                            <th class="px-5 py-3 font-semibold">Pelanggan</th>
                            <th class="px-5 py-3 font-semibold">PIC</th>
                            <th class="px-5 py-3 font-semibold">Status</th>
                            <th class="px-5 py-3 font-semibold">Tenggat</th>
                            <th class="px-5 py-3 text-right font-semibold">Nilai</th>
                            <th class="px-5 py-3 text-right font-semibold">Biaya</th>
                            <th class="px-5 py-3 text-right font-semibold">Margin</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($projects as $project)
                            <tr class="hover:bg-slate-50">
                                <td class="px-5 py-3 font-medium text-slate-900">{{ $project->project_name }}</td>
                                <td class="px-5 py-3 text-slate-600">{{ $project->endUser?->nama ?? '-' }}</td>
                                <td class="px-5 py-3 text-slate-600">{{ $project->pic?->nama ?? '-' }}</td>
                                <td class="px-5 py-3">
                                    @php
                                        $statusClass = match ($project->status) {
                                            'planning' => 'bg-amber-50 text-amber-700 ring-amber-200',
                                            'progress' => 'bg-indigo-50 text-indigo-700 ring-indigo-200',
                                            'done' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
                                            'cancel' => 'bg-rose-50 text-rose-700 ring-rose-200',
                                            default => 'bg-slate-50 text-slate-700 ring-slate-200',
                                        };
                                    @endphp
                                    <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset {{ $statusClass }}">{{ $statusLabels[$project->status] ?? ucfirst($project->status) }}</span>
                                </td>
                                <td class="whitespace-nowrap px-5 py-3 text-slate-600">{{ $project->deadline_date?->translatedFormat('d M Y') ?? '-' }}</td>
                                <td class="whitespace-nowrap px-5 py-3 text-right">{{ $project->project_value !== null ? $money($project->project_value) : '-' }}</td>
                                <td class="whitespace-nowrap px-5 py-3 text-right">{{ $project->internal_cost !== null ? $money($project->internal_cost) : '-' }}</td>
                                <td class="whitespace-nowrap px-5 py-3 text-right font-medium {{ (float) $project->margin < 0 ? 'rounded bg-rose-50 text-rose-700' : 'text-slate-700' }}">{{ $project->margin !== null ? $money($project->margin) : '-' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="px-5 py-10 text-center text-slate-500">Belum ada proyek untuk filter ini.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if (!$isPrint && $projects->hasPages())
                <div class="report-pagination border-t border-slate-200 p-4">{{ $projects->links() }}</div>
            @endif
        </section>
        </div>
    </div>
@if ($isPrint)
    <script>
        window.addEventListener('afterprint', () => {
            window.location.replace(@js(route('admin.reports.index', request()->except('print'))));
        }, { once: true });

        window.addEventListener('load', () => window.setTimeout(() => window.print(), 150));
    </script>
@endif
</x-admin-layout>
