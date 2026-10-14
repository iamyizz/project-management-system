<x-admin-layout title="Proyek Saya">
    @php
        $input    = 'w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20';
        $btnSmall = 'rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50';
        $statuses = [
            'planning' => ['Direncanakan', 'bg-yellow-100 text-yellow-700'],
            'progress' => ['Berjalan', 'bg-blue-100 text-blue-700'],
            'done'     => ['Selesai',     'bg-green-100 text-green-700'],
            'cancel'   => ['Dibatalkan',   'bg-red-100 text-red-700'],
        ];
        $userStatuses = ['planning', 'progress', 'done'];
        $details = [
            'project_name'         => ['label' => 'Nama proyek', 'span' => true],
            'end_user'             => ['label' => 'Pelanggan'],
            'pic'                  => ['label' => 'PIC'],
            'po_number'            => ['label' => 'Nomor PO'],
            'quotation_number'     => ['label' => 'Nomor penawaran'],
            'deadline_date'        => ['label' => 'Tenggat'],
            'status'               => ['label' => 'Status'],
        ];
        $endUserFields = [
            'nama' => 'Nama pelanggan',
            'industri' => 'Industri',
            'contact' => 'Narahubung',
            'telepon' => 'Telepon',
            'email' => 'Email',
            'kota' => 'Kota',
        ];
        $formatDate    = fn ($d) => $d ? \Illuminate\Support\Carbon::parse($d)->translatedFormat('d M Y') : null;
        $formType = old('form_type');
    @endphp

    <div class="space-y-7 sm:space-y-8">
        {{-- HEADER --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="workspace-kicker">Ruang kerja saya</p>
                <h2 class="mt-1 text-2xl font-bold tracking-tight text-slate-950 sm:text-3xl">Proyek Saya</h2>
                <p class="mt-2 text-sm text-slate-500">Pantau detail project dan lanjutkan progres pekerjaanmu.</p>
            </div>
            <div class="inline-flex w-fit items-center gap-2 rounded-xl border border-indigo-100 bg-indigo-50 px-3 py-2 text-xs font-semibold text-indigo-700">
                <span class="h-2 w-2 rounded-full bg-indigo-500"></span> {{ $totalProject }} proyek ditugaskan
            </div>
        </div>

        {{-- ALERT --}}
        @if (session('success'))
            <div id="flashAlert" class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ session('success') }}
            </div>
        @endif

        {{-- SUMMARY --}}
        <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 sm:gap-6">
            @foreach ([
                ['Total proyek', $totalProject, 'text-slate-900', 'bg-slate-50'],
                ['Proyek aktif',   $active,     'text-blue-600',  'bg-blue-50'],
                ['Selesai',       $done,         'text-green-600', 'bg-green-50'],
            ] as [$title, $count, $color, $bg])
                <div class="workspace-stat {{ $bg }}">
                    <p class="text-xs sm:text-sm text-slate-600">{{ $title }}</p>
                    <p class="mt-2 text-2xl sm:text-3xl font-bold {{ $color }}">{{ $count }}</p>
                </div>
            @endforeach
        </div>

        {{-- TABLE CONTAINER --}}
        <div class="workspace-panel overflow-hidden">
            <form method="GET" action="{{ route('user.projects.index') }}" data-live-filter data-results-target="#projectResults" class="flex flex-col gap-3 border-b border-slate-100 bg-white p-4 sm:flex-row sm:items-center sm:p-5">
                <div class="relative min-w-0 flex-1">
                    <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path stroke-linecap="round" d="m16.5 16.5 4 4"/></svg>
                    <input name="q" type="search" value="{{ $filters['q'] ?? '' }}" placeholder="Cari proyek atau pelanggan..." class="workspace-input pl-10">
                </div>
                <select name="status" class="workspace-input sm:max-w-xs">
                    <option value="">Semua status</option>
                    @foreach ($statuses as $value => [$statusLabel])
                        <option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>{{ $statusLabel }}</option>
                    @endforeach
                </select>
                <a href="{{ route('user.projects.index') }}" data-filter-reset class="shrink-0 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-center text-sm font-semibold text-slate-600 transition hover:border-indigo-200 hover:bg-indigo-50 hover:text-indigo-700">Reset</a>
            </form>
            <p data-filter-status role="status" aria-live="polite" class="sr-only"></p>
            <div id="projectResults" data-live-results aria-live="polite">

            {{-- DESKTOP: Tabel --}}
            <div class="hidden overflow-x-auto md:block">
                <table class="workspace-table w-full text-left text-sm">
                    <thead class="bg-slate-50 text-xs uppercase text-slate-500">
                        <tr>
                            <th class="px-6 py-3 font-semibold">No</th>
                            <th class="px-6 py-3 font-semibold">Pelanggan</th>
                            <th class="px-6 py-3 font-semibold">Nama proyek</th>
                            <th class="px-6 py-3 font-semibold">Tenggat</th>
                            <th class="px-6 py-3 font-semibold">Status</th>
                            <th class="px-6 py-3 text-right font-semibold">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="projectTable" class="divide-y divide-slate-100">
                        @forelse ($projects as $project)
                            @php
                                [$statusLabel, $statusClass] = $statuses[$project->status]
                                    ?? [ucfirst($project->status), 'bg-slate-100 text-slate-700'];
                                $deadline = $project->deadline_date;
                                $overdue  = $deadline
                                    && $deadline->lt(today())
                                    && ! in_array($project->status, ['done', 'cancel']);
                                $rowData = [
                                    'id'           => $project->id,
                                    'update_url'   => url("user/projects/{$project->id}"),
                                    'status'       => $project->status,
                                    'allowed_statuses' => match ($project->status) {
                                        'planning' => ['planning', 'progress'],
                                        'progress' => ['progress', 'done'],
                                        default => [$project->status],
                                    },
                                    'status_class' => $statusClass,
                                    'end_user_details' => $project->endUser?->only(array_keys($endUserFields)),
                                    'labels'       => [
                                        'project_name'         => $project->project_name,
                                        'end_user'             => $project->endUser?->nama,
                                        'pic'                  => $project->pic?->nama,
                                        'po_number'            => $project->po_number,
                                        'quotation_number'     => $project->quotation_number,
                                        'deadline_date'        => $formatDate($deadline),
                                        'status'               => $statusLabel,
                                    ],
                                    'history'      => $project->statusHistories->map(fn ($history) => [
                                        'from' => $history->from_status ? ($statuses[$history->from_status][0] ?? $history->from_status) : null,
                                        'to' => $statuses[$history->to_status][0] ?? $history->to_status,
                                        'actor' => $history->actor_name,
                                        'date' => $history->created_at->translatedFormat('d M Y · H:i'),
                                    ])->values(),
                                    'activities' => $project->activities->map(fn ($activity) => [
                                        'field' => $activity->field,
                                        'old' => $activity->old_value,
                                        'new' => $activity->new_value,
                                        'actor' => $activity->actor_name,
                                        'date' => $activity->created_at->translatedFormat('d M Y · H:i'),
                                    ])->values(),
                                ];
                            @endphp
                            <tr class="data-row transition hover:bg-slate-50" data-project="{{ json_encode($rowData) }}">
                                <td class="px-6 py-4 text-slate-500">{{ $projects->firstItem() + $loop->index }}</td>
                                <td class="px-6 py-4 text-slate-700">
                                    @if ($project->endUser)
                                        <button type="button" onclick="openEndUserDetail(this)" aria-haspopup="dialog" aria-controls="endUserDetailModal" class="rounded text-left text-indigo-600 underline-offset-4 hover:text-indigo-800 hover:underline focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500">{{ $project->endUser->nama }}</button>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="px-6 py-4 font-medium text-slate-900">{{ $project->project_name }}</td>
                                <td class="whitespace-nowrap px-6 py-4 {{ $overdue ? 'font-semibold text-red-600' : 'text-slate-700' }}">
                                    {{ $formatDate($deadline) ?? '-' }}
                                    @if ($overdue)
                                        <span class="ml-1 rounded-full bg-red-100 px-2 py-0.5 text-xs font-medium text-red-700">Terlambat</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <span class="rounded-full px-3 py-1 text-xs font-medium {{ $statusClass }}">{{ $statusLabel }}</span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex justify-end gap-2">
                                        <button type="button" onclick="openDetail(this)" class="{{ $btnSmall }}">Detail</button>
                                        @if ($project->status !== 'cancel')
                                            <button type="button" onclick="openProgress(this)"
                                                class="rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-indigo-700">
                                                Perbarui status
                                            </button>
                                        @else
                                            <button type="button" disabled
                                                class="cursor-not-allowed rounded-lg bg-slate-100 px-3 py-1.5 text-xs font-medium text-slate-400">
                                                Perbarui status
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-10 text-center text-slate-500">Belum ada proyek yang ditugaskan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- MOBILE: Card View --}}
            <div id="projectCards" class="space-y-4 p-4 md:hidden">
                @forelse ($projects as $project)
                    @php
                        [$statusLabel, $statusClass] = $statuses[$project->status]
                            ?? [ucfirst($project->status), 'bg-slate-100 text-slate-700'];
                        $deadline = $project->deadline_date;
                        $overdue  = $deadline
                            && $deadline->lt(today())
                            && ! in_array($project->status, ['done', 'cancel']);
                        $rowData = [
                            'id'           => $project->id,
                            'update_url'   => url("user/projects/{$project->id}"),
                            'status'       => $project->status,
                            'allowed_statuses' => match ($project->status) {
                                'planning' => ['planning', 'progress'],
                                'progress' => ['progress', 'done'],
                                default => [$project->status],
                            },
                            'status_class' => $statusClass,
                            'end_user_details' => $project->endUser?->only(array_keys($endUserFields)),
                            'labels'       => [
                                'project_name'         => $project->project_name,
                                'end_user'             => $project->endUser?->nama,
                                'pic'                  => $project->pic?->nama,
                                'po_number'            => $project->po_number,
                                'quotation_number'     => $project->quotation_number,
                                'deadline_date'        => $formatDate($deadline),
                                'status'               => $statusLabel,
                            ],
                        ];
                    @endphp
                    <div class="data-row rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm shadow-slate-900/[0.02] transition duration-200 hover:-translate-y-0.5 hover:border-indigo-200 hover:shadow-md" data-project="{{ json_encode($rowData) }}">
                        {{-- Header Card --}}
                        <div class="mb-4 flex items-start justify-between gap-3">
                            <div class="min-w-0 flex-1">
                                <p class="truncate font-semibold text-slate-900">{{ $project->project_name }}</p>
                                @if ($project->endUser)
                                    <button type="button" onclick="openEndUserDetail(this)" aria-haspopup="dialog" aria-controls="endUserDetailModal" class="block max-w-full truncate rounded text-left text-sm text-indigo-600 underline-offset-4 hover:text-indigo-800 hover:underline focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500">{{ $project->endUser->nama }}</button>
                                @else
                                    <p class="truncate text-sm text-slate-600">-</p>
                                @endif
                            </div>
                            <span class="shrink-0 rounded-full px-2.5 py-1 text-xs font-medium {{ $statusClass }}">
                                {{ $statusLabel }}
                            </span>
                        </div>

                        {{-- Content Card --}}
                        <div class="mb-4 space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-slate-500">PIC:</span>
                                <span class="font-medium text-slate-900">{{ $project->pic?->nama ?? '-' }}</span>
                            </div>
                            <div class="flex justify-between {{ $overdue ? 'text-red-600' : '' }}">
                                <span class="text-slate-500">Tenggat:</span>
                                <span class="font-medium">
                                    {{ $formatDate($deadline) ?? '-' }}
                                    @if ($overdue)
                                        <span class="ml-1">(Terlambat)</span>
                                    @endif
                                </span>
                            </div>
                        </div>

                        {{-- Action Buttons --}}
                        <div class="flex gap-2">
                            <button type="button" onclick="openDetail(this)"
                                class="flex-1 rounded-lg border border-indigo-300 bg-indigo-50 px-3 py-2 text-sm font-medium text-indigo-700 transition hover:bg-indigo-100">
                                Detail
                            </button>
                            @if ($project->status !== 'cancel')
                                <button type="button" onclick="openProgress(this)"
                                    class="flex-1 rounded-lg bg-indigo-600 px-3 py-2 text-sm font-semibold text-white transition hover:bg-indigo-700">
                                    Perbarui status
                                </button>
                            @else
                                <button type="button" disabled
                                    class="flex-1 cursor-not-allowed rounded-lg bg-slate-100 px-3 py-2 text-sm font-medium text-slate-400">
                                    Perbarui status
                                </button>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="rounded-lg border border-dashed border-slate-300 bg-slate-50 px-6 py-12 text-center text-slate-500">
                        <p class="font-medium">Belum ada proyek</p>
                        <p class="mt-1 text-sm">Tunggu admin untuk menugaskan project ke kamu</p>
                    </div>
                @endforelse

                <div id="noResultCards" class="hidden rounded-lg border border-dashed border-slate-300 bg-slate-50 px-6 py-12 text-center text-slate-500">
                    <p class="font-medium">Tidak ada hasil</p>
                </div>
            </div>

            @if ($projects->hasPages())
                <div class="border-t border-slate-200 p-4">
                    {{ $projects->links() }}
                </div>
            @endif
            </div>
        </div>
    </div>

    {{-- ================= MODAL DETAIL ================= --}}
    <div id="detailModal" role="dialog" aria-modal="true" aria-labelledby="projectDetailTitle" class="modal fixed inset-0 z-50 hidden items-center justify-center bg-slate-950/60 p-4 backdrop-blur-sm">
        <div class="scrollbar-hidden max-h-[90vh] w-full max-w-3xl overflow-y-auto rounded-3xl bg-white shadow-2xl ring-1 ring-black/5">
            <div class="sticky top-0 z-10 flex items-start justify-between border-b border-slate-100 bg-white/95 px-6 py-5 backdrop-blur sm:px-8">
                <div class="flex items-start gap-4">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75A2.25 2.25 0 016 4.5h4.19c.6 0 1.17.24 1.59.66l1.06 1.06c.42.42.99.66 1.59.66H18a2.25 2.25 0 012.25 2.25v8.25A2.25 2.25 0 0118 19.63H6a2.25 2.25 0 01-2.25-2.25V6.75z" /></svg>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-indigo-600">Ringkasan</p>
                        <h3 id="projectDetailTitle" class="mt-1 text-xl font-bold tracking-tight text-slate-900">Detail proyek</h3>
                        <p class="mt-1 text-sm text-slate-500">Informasi lengkap project yang dipilih.</p>
                    </div>
                </div>
                <button type="button" onclick="closeModal('detailModal')" aria-label="Tutup detail project" class="rounded-xl p-2 text-slate-400 transition hover:bg-slate-100 hover:text-slate-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500">&times;</button>
            </div>
            <dl class="grid grid-cols-1 gap-3 bg-slate-50/70 px-6 py-6 sm:grid-cols-2 sm:gap-4 sm:px-8">
                @foreach ($details as $key => $detail)
                    <div class="{{ !empty($detail['span']) ? 'sm:col-span-2' : '' }} rounded-2xl border border-slate-200/80 bg-white px-4 py-3.5 shadow-sm shadow-slate-200/40 transition-all duration-200 hover:-translate-y-0.5 hover:border-indigo-200 hover:shadow-md">
                        <dt class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">{{ $detail['label'] }}</dt>
                        <dd class="mt-1.5 break-words text-sm font-semibold text-slate-900">
                            @if ($key === 'status')
                                <span data-detail="status" class="inline-flex rounded-full px-3 py-1 text-xs font-semibold">-</span>
                            @else
                                <span data-detail="{{ $key }}">-</span>
                            @endif
                        </dd>
                    </div>
                @endforeach
            </dl>
            <section class="border-t border-slate-100 px-6 py-5 sm:px-8">
                <h4 class="text-sm font-semibold text-slate-900">Riwayat Status</h4>
                <ol data-status-history class="mt-3 space-y-3"></ol>
            </section>
            <section class="border-t border-slate-100 px-6 py-5 sm:px-8">
                <h4 class="text-sm font-semibold text-slate-900">Perubahan detail proyek</h4>
                <ol data-project-activities class="mt-3 space-y-3"></ol>
            </section>
            <div class="flex justify-end border-t border-slate-100 px-6 py-4 sm:px-8">
                <button type="button" onclick="closeModal('detailModal')"
                    class="rounded-xl bg-slate-900 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-500 focus-visible:ring-offset-2">
                    Tutup
                </button>
            </div>
        </div>
    </div>

    {{-- ================= MODAL DETAIL END USER ================= --}}
    <div id="endUserDetailModal" role="dialog" aria-modal="true" aria-labelledby="endUserDetailTitle" class="modal fixed inset-0 z-50 hidden items-center justify-center bg-slate-950/60 p-4 backdrop-blur-sm">
        <div class="scrollbar-hidden max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-3xl bg-white shadow-2xl ring-1 ring-black/5">
            <div class="sticky top-0 z-10 flex items-start justify-between border-b border-slate-100 bg-white/95 px-6 py-5 backdrop-blur sm:px-8">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-indigo-600">Informasi Kontak</p>
                    <h3 id="endUserDetailTitle" class="mt-1 text-xl font-bold tracking-tight text-slate-900">Detail pelanggan</h3>
                    <p class="mt-1 text-sm text-slate-500">Informasi pelanggan dari proyek yang ditugaskan kepadamu.</p>
                </div>
                <button type="button" onclick="closeModal('endUserDetailModal')" aria-label="Tutup detail pelanggan" class="rounded-xl p-2 text-2xl leading-none text-slate-400 transition hover:bg-slate-100 hover:text-slate-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500">&times;</button>
            </div>
            <dl class="grid grid-cols-1 gap-3 bg-slate-50/70 px-6 py-6 sm:grid-cols-2 sm:gap-4 sm:px-8">
                @foreach ($endUserFields as $key => $label)
                    <div class="{{ $key === 'nama' ? 'sm:col-span-2' : '' }} rounded-2xl border border-slate-200/80 bg-white px-4 py-3.5 shadow-sm shadow-slate-200/40 transition-all duration-200 hover:-translate-y-0.5 hover:border-indigo-200 hover:shadow-md">
                        <dt class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">{{ $label }}</dt>
                        <dd data-end-user-detail="{{ $key }}" class="mt-1.5 break-words text-sm font-semibold text-slate-900">-</dd>
                    </div>
                @endforeach
            </dl>
            <div class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 px-6 py-4 sm:px-8">
                <div class="flex flex-wrap gap-2">
                    <a data-contact-phone href="#" class="hidden rounded-lg border border-slate-300 px-3 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50">Telepon</a>
                    <a data-contact-email href="#" class="hidden rounded-lg border border-slate-300 px-3 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50">Kirim Email</a>
                </div>
                <button type="button" onclick="closeModal('endUserDetailModal')" class="rounded-xl bg-slate-900 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-500 focus-visible:ring-offset-2">Tutup</button>
            </div>
        </div>
    </div>

    {{-- ================= MODAL UPDATE PROGRESS ================= --}}
    <div id="progressModal"
        class="modal fixed inset-0 z-50 {{ $formType === 'progress' ? 'flex' : 'hidden' }} items-center justify-center bg-slate-900/50 p-4">
        <div class="w-full max-w-md rounded-2xl bg-white shadow-xl">
            <div class="flex items-center justify-between border-b border-slate-200 px-6 py-4">
                        <h3 class="text-lg font-semibold text-slate-900">Perbarui status proyek</h3>
                <button type="button" onclick="closeModal('progressModal')" class="text-2xl leading-none text-slate-400 hover:text-slate-600">&times;</button>
            </div>
            <form id="progressForm" method="POST"
                action="{{ $formType === 'progress' && old('project_id') ? url('user/projects/' . old('project_id')) : '' }}">
                @csrf
                @method('PUT')
                <input type="hidden" name="form_type" value="progress">
                <input type="hidden" name="project_id" value="{{ old('project_id') }}">
                <div class="px-6 py-5 space-y-4">
                    <div>
                        <p id="progress_project" class="text-sm font-medium text-slate-900">-</p>
                        <p class="text-xs text-slate-500">Proyek</p>
                    </div>
                    <div>
                        <label for="progress_status" class="mb-1.5 block text-sm font-medium text-slate-700">
                            Status <span class="text-red-500">*</span>
                        </label>
                        <select id="progress_status" name="status" class="{{ $input }}" required>
                            @foreach ($userStatuses as $key)
                                <option value="{{ $key }}" @selected(old('status') === $key)>{{ $statuses[$key][0] }}</option>
                            @endforeach
                        </select>
                        @error('status')
                            <p class="field-error mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                <div class="flex justify-end gap-2 border-t border-slate-200 px-6 py-4">
                    <button type="button" onclick="closeModal('progressModal')"
                        class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                        Batal
                    </button>
                    <button type="submit"
                        class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openModal(id) {
            const modal = document.getElementById(id);
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.body.classList.add('overflow-hidden');
        }

        function closeModal(id) {
            const modal = document.getElementById(id);
            if (!modal || modal.classList.contains('hidden')) return;

            window.closeModalAnimated(modal, () => {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
                if (!document.querySelector('.modal.flex')) {
                    document.body.classList.remove('overflow-hidden');
                }
            });
        }

        function rowData(btn) {
            return JSON.parse(btn.closest('.data-row').dataset.project);
        }

        function openDetail(btn) {
            const data = rowData(btn);
            document.querySelectorAll('#detailModal [data-detail]').forEach((el) => {
                el.textContent = data.labels[el.dataset.detail] || '-';
            });
            const badge = document.querySelector('#detailModal [data-detail="status"]');
            badge.className = 'rounded-full px-3 py-1 text-xs font-medium ' + data.status_class;
            renderStatusHistory(data.history || []);
            renderProjectActivities(data.activities || []);
            openModal('detailModal');
        }

        function renderStatusHistory(history) {
            const list = document.querySelector('#detailModal [data-status-history]');
            list.replaceChildren();

            if (!history.length) {
                const empty = document.createElement('li');
                empty.className = 'text-sm text-slate-500';
                empty.textContent = 'Belum ada riwayat status.';
                list.append(empty);
                return;
            }

            history.forEach((item) => {
                const entry = document.createElement('li');
                entry.className = 'rounded-xl border border-slate-200 bg-slate-50 px-4 py-3';
                const change = document.createElement('p');
                change.className = 'text-sm font-medium text-slate-800';
                change.textContent = item.from ? `${item.from} → ${item.to}` : `Proyek dibuat dengan status ${item.to}`;
                const meta = document.createElement('p');
                meta.className = 'mt-1 text-xs text-slate-500';
                meta.textContent = `${item.actor} · ${item.date}`;
                entry.append(change, meta);
                list.append(entry);
            });
        }

        function renderProjectActivities(activities) {
            const list = document.querySelector('#detailModal [data-project-activities]');
            list.replaceChildren();

            if (!activities.length) {
                const empty = document.createElement('li');
                empty.className = 'text-sm text-slate-500';
                empty.textContent = 'Belum ada perubahan detail proyek.';
                list.append(empty);
                return;
            }

            activities.forEach((item) => {
                const entry = document.createElement('li');
                entry.className = 'rounded-xl border border-slate-200 bg-slate-50 px-4 py-3';
                const change = document.createElement('p');
                change.className = 'text-sm font-medium text-slate-800';
                change.textContent = `${item.field}: ${item.old ?? '-'} → ${item.new ?? '-'}`;
                const meta = document.createElement('p');
                meta.className = 'mt-1 text-xs text-slate-500';
                meta.textContent = `${item.actor} · ${item.date}`;
                entry.append(change, meta);
                list.append(entry);
            });
        }

        function openEndUserDetail(btn) {
            const endUser = rowData(btn).end_user_details || {};

            document.querySelectorAll('#endUserDetailModal [data-end-user-detail]').forEach((el) => {
                el.textContent = endUser[el.dataset.endUserDetail] || '-';
            });

            const phoneLink = document.querySelector('#endUserDetailModal [data-contact-phone]');
            phoneLink.href = endUser.telepon ? `tel:${endUser.telepon}` : '#';
            phoneLink.classList.toggle('hidden', !endUser.telepon);

            const emailLink = document.querySelector('#endUserDetailModal [data-contact-email]');
            emailLink.href = endUser.email ? `mailto:${endUser.email}` : '#';
            emailLink.classList.toggle('hidden', !endUser.email);

            openModal('endUserDetailModal');
        }

        function openProgress(btn) {
            const data = rowData(btn);
            const form = document.getElementById('progressForm');
            form.action = data.update_url;
            form.elements['project_id'].value = data.id;
            form.elements['status'].value = data.status;
            [...form.elements['status'].options].forEach((option) => {
                option.disabled = !data.allowed_statuses.includes(option.value);
            });
            document.getElementById('progress_project').textContent = data.labels.project_name;
            form.querySelectorAll('.field-error').forEach((el) => el.remove());
            openModal('progressModal');
        }

        // Tutup modal lewat klik overlay / tombol Esc
        document.querySelectorAll('.modal').forEach((modal) => {
            modal.addEventListener('click', (e) => {
                if (e.target === modal) closeModal(modal.id);
            });
        });

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                document.querySelectorAll('.modal.flex').forEach((m) => closeModal(m.id));
            }
        });

        // Flash message hilang otomatis
        const flash = document.getElementById('flashAlert');
        if (flash) setTimeout(() => flash.remove(), 4000);

        // Kunci scroll jika modal error validasi terbuka
        if (document.querySelector('.modal.flex')) {
            document.body.classList.add('overflow-hidden');
        }
    </script>
</x-admin-layout>
