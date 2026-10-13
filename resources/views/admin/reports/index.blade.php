<x-admin-layout title="Laporan">
    @php
        $statusLabels = [
            'planning' => 'Planning',
            'progress' => 'Progress',
            'done' => 'Done',
            'cancel' => 'Cancel',
        ];
        $money = fn ($value) => 'Rp ' . number_format((float) $value, 0, ',', '.');
        $input = 'w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20';
    @endphp

    <div class="space-y-7 sm:space-y-8">
        <div>
            <p class="workspace-kicker">Analitik operasional</p>
            <h2 class="mt-1 text-2xl font-bold tracking-tight text-slate-950 sm:text-3xl">Laporan Project</h2>
            <p class="mt-2 text-sm text-slate-500">Pantau performa status, nilai, biaya internal, margin, dan deadline project.</p>
        </div>

        <form method="GET" action="{{ route('admin.reports.index') }}" data-live-filter data-results-target="#reportResults" class="workspace-panel grid gap-3 p-4 sm:grid-cols-2 sm:p-5 lg:grid-cols-6">
            <div class="col-span-full flex items-center gap-2 text-sm font-semibold text-slate-800"><span class="flex h-8 w-8 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600">⌕</span> Filter laporan</div>
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
                ['name' => 'date_from', 'label' => 'Deadline dari', 'aria' => 'Pilih tanggal deadline mulai'],
                ['name' => 'date_to', 'label' => 'Deadline sampai', 'aria' => 'Pilih tanggal deadline akhir'],
            ] as $dateFilter)
                <label class="block min-w-0 text-xs font-semibold text-slate-600">
                    {{ $dateFilter['label'] }}
                    <input type="text" name="{{ $dateFilter['name'] }}" value="{{ $filters[$dateFilter['name']] ?? '' }}" placeholder="Pilih tanggal" autocomplete="off" class="workspace-input mt-1.5 h-11" data-flatpickr aria-label="{{ $dateFilter['aria'] }}">
                </label>
            @endforeach
            <div class="flex items-end gap-2">
                <a href="{{ route('admin.reports.index') }}" data-filter-reset class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-600 transition hover:border-indigo-200 hover:bg-indigo-50 hover:text-indigo-700">Reset filter</a>
            </div>
        </form>
        <p data-filter-status role="status" aria-live="polite" class="sr-only"></p>

        <div id="reportResults" data-live-results aria-live="polite" class="space-y-6">
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
            @foreach ([
                ['Total Project', number_format((int) $summary->total_projects)],
                ['Project Value', $money($summary->total_value)],
                ['Internal Cost', $money($summary->total_cost)],
                ['Total Margin', $money($summary->total_margin)],
                ['Margin', number_format($marginPercentage, 1, ',', '.') . '%'],
            ] as [$label, $value])
                <div class="workspace-stat">
                    <p class="text-sm font-medium text-slate-500">{{ $label }}</p>
                    <p class="mt-2 break-words text-xl font-bold text-slate-900">{{ $value }}</p>
                </div>
            @endforeach
        </div>

        <section class="grid gap-4 sm:grid-cols-2">
            <article class="flex items-center justify-between gap-4 rounded-2xl border border-rose-200 bg-rose-50 p-5">
                <div><p class="text-sm font-semibold text-rose-800">Project terlambat</p><p class="mt-1 text-xs text-rose-700">Deadline lewat, status belum selesai atau dibatalkan.</p></div>
                <span class="text-3xl font-bold tabular-nums text-rose-800">{{ number_format($overdueCount) }}</span>
            </article>
            <article class="flex items-center justify-between gap-4 rounded-2xl border border-amber-200 bg-amber-50 p-5">
                <div><p class="text-sm font-semibold text-amber-800">Deadline 30 hari ke depan</p><p class="mt-1 text-xs text-amber-700">Project aktif yang perlu dipantau.</p></div>
                <span class="text-3xl font-bold tabular-nums text-amber-800">{{ number_format($upcomingCount) }}</span>
            </article>
        </section>

        @php
            $statusStops = [];
            $statusOffset = 0;
            foreach ($statusSegments as $segment) {
                if ($segment['percent'] > 0) {
                    $nextOffset = $statusOffset + $segment['percent'];
                    $statusStops[] = "{$segment['color']} {$statusOffset}% {$nextOffset}%";
                    $statusOffset = $nextOffset;
                }
            }
            $statusGradient = $statusStops ? 'conic-gradient(' . implode(', ', $statusStops) . ')' : 'conic-gradient(#e2e8f0 0% 100%)';
            $maxMonthlyCount = max(1, $monthlyProjects->max('count'));
            $financeItems = [
                ['label' => 'Project Value', 'value' => (float) $summary->total_value, 'color' => 'bg-indigo-500'],
                ['label' => 'Internal Cost', 'value' => (float) $summary->total_cost, 'color' => 'bg-slate-500'],
                ['label' => 'Margin', 'value' => (float) $summary->total_margin, 'color' => $summary->total_margin < 0 ? 'bg-rose-500' : 'bg-emerald-500'],
            ];
            $maxFinanceValue = max(1, ...array_map(fn ($item) => abs($item['value']), $financeItems));
        @endphp

        <div class="grid gap-4 xl:grid-cols-3">
            <section class="workspace-panel p-5 sm:p-6 workspace-stat">
                <div class>
                    <h3 class="font-semibold text-slate-900">Komposisi Status</h3>
                    <p class="mt-1 text-xs text-slate-500">Sebaran status dari project yang sesuai filter.</p>
                </div>
                <div class="mt-5 flex flex-wrap items-center justify-center gap-6 sm:justify-start">
                    <div class="relative h-36 w-36 shrink-0 rounded-full" style="background: {{ $statusGradient }}" role="img" aria-label="Diagram komposisi status project">
                        <div class="absolute inset-5 flex flex-col items-center justify-center rounded-full bg-white">
                            <span class="text-2xl font-bold text-slate-900">{{ number_format((int) $summary->total_projects) }}</span>
                            <span class="text-xs text-slate-500">Project</span>
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
                    <h3 class="font-semibold text-slate-900">Project Dibuat per Bulan</h3>
                    <p class="mt-1 text-xs text-slate-500">Jumlah project berdasarkan tanggal dibuat, 12 bulan terakhir.</p>
                </div>
                <div class="mt-6 flex h-40 items-end gap-1.5 sm:gap-2" role="img" aria-label="Grafik jumlah project dibuat per bulan selama 12 bulan terakhir">
                    @foreach ($monthlyProjects as $month)
                        @php $barHeight = $month['count'] > 0 ? max(8, ($month['count'] / $maxMonthlyCount) * 100) : 2; @endphp
                        <div class="flex h-full min-w-0 flex-1 flex-col items-center justify-end gap-2" title="{{ $month['label'] }}: {{ $month['count'] }} project">
                            <span class="text-[10px] font-medium text-slate-500">{{ $month['count'] ?: '' }}</span>
                            <div class="w-full max-w-6 rounded-t-md bg-indigo-400 transition-colors hover:bg-indigo-600" style="height: {{ $barHeight }}%"></div>
                            <span class="whitespace-nowrap text-[9px] text-slate-400 sm:text-[10px]">{{ $month['label'] }}</span>
                        </div>
                    @endforeach
                </div>
            </section>

            <section class="workspace-panel p-5 sm:p-6 workspace-stat">
                <div>
                    <h3 class="font-semibold text-slate-900">Perbandingan Keuangan</h3>
                    <p class="mt-1 text-xs text-slate-500">Nilai agregat dari project yang sesuai filter.</p>
                </div>
                <div class="mt-6 space-y-5">
                    @foreach ($financeItems as $item)
                        @php $barWidth = min(100, (abs($item['value']) / $maxFinanceValue) * 100); @endphp
                        <div>
                            <div class="mb-2 flex items-center justify-between gap-3 text-sm">
                                <span class="font-medium text-slate-600">{{ $item['label'] }}</span>
                                <span class="text-right font-semibold text-slate-900">{{ $money($item['value']) }}</span>
                            </div>
                            <div class="h-2.5 overflow-hidden rounded-full bg-slate-100">
                                <div class="h-full rounded-full {{ $item['color'] }}" style="width: {{ $barWidth }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        </div>

        <section class="workspace-panel overflow-hidden">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 px-5 py-5 sm:px-6">
                <div><p class="workspace-kicker">Data detail</p><h3 class="mt-1 text-lg font-bold tracking-tight text-slate-900">Rincian project</h3></div>
                <div class="flex flex-wrap items-center gap-3">
                    <a href="{{ route('admin.reports.export', request()->query()) }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:border-indigo-200 hover:bg-indigo-50 hover:text-indigo-700">
                        <span aria-hidden="true">↓</span> Unduh CSV
                    </a>
                    <button type="button" onclick="window.print()" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:border-indigo-200 hover:bg-indigo-50 hover:text-indigo-700 print:hidden">
                        Cetak / PDF
                    </button>
                    <div class="flex flex-wrap gap-2 text-xs">
                        @foreach ($statusLabels as $status => $label)
                            <span class="rounded-full bg-slate-100 px-3 py-1 text-slate-600">{{ $label }}: {{ $statusCounts[$status] ?? 0 }}</span>
                        @endforeach
                    </div>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="workspace-table w-full text-left text-sm">
                    <thead class="bg-slate-50 text-xs uppercase text-slate-500">
                        <tr>
                            <th class="px-5 py-3 font-semibold">Project</th>
                            <th class="px-5 py-3 font-semibold">End User</th>
                            <th class="px-5 py-3 font-semibold">PIC</th>
                            <th class="px-5 py-3 font-semibold">Status</th>
                            <th class="px-5 py-3 font-semibold">Deadline</th>
                            <th class="px-5 py-3 text-right font-semibold">Value</th>
                            <th class="px-5 py-3 text-right font-semibold">Cost</th>
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
                                <td class="whitespace-nowrap px-5 py-3 text-right font-semibold {{ (float) $project->margin < 0 ? 'text-rose-600' : 'text-emerald-700' }}">{{ $project->margin !== null ? $money($project->margin) : '-' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="px-5 py-10 text-center text-slate-500">Belum ada project untuk filter ini.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($projects->hasPages())
                <div class="border-t border-slate-200 p-4">{{ $projects->links() }}</div>
            @endif
        </section>
        </div>
    </div>
</x-admin-layout>
