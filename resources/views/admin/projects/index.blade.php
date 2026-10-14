<x-admin-layout title="Proyek">
    @php
        $input    = 'w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20';
        $label    = 'mb-1.5 block text-sm font-medium text-slate-700';
        $btnSmall = 'rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50';
        $formType = old('form_type');
        $statuses = [
            'planning' => ['Direncanakan', 'bg-yellow-100 text-yellow-700'],
            'progress' => ['Berjalan', 'bg-blue-100 text-blue-700'],
            'done'     => ['Selesai',     'bg-green-100 text-green-700'],
            'cancel'   => ['Dibatalkan',   'bg-red-100 text-red-700'],
        ];
        $fields = [
            'project_name'         => ['label' => 'Nama proyek', 'required' => true, 'span' => true],
            'end_user_id'          => ['label' => 'Pelanggan', 'type' => 'select', 'required' => true, 'options' => $endUsers->pluck('nama', 'id')],
            'pic_id'               => ['label' => 'PIC', 'type' => 'select', 'options' => $pics->pluck('nama', 'id')],
            'project_value'        => ['label' => 'Nilai proyek', 'type' => 'money'],
            'po_number'            => ['label' => 'Nomor PO'],
            'quotation_number'     => ['label' => 'Nomor penawaran'],
            'internal_cost'        => ['label' => 'Biaya internal', 'type' => 'money'],
            'margin'               => ['label' => 'Margin', 'type' => 'money', 'readonly' => true],
            'percentage'           => ['label' => 'Persentase margin', 'type' => 'percent', 'readonly' => true],
            'deadline_date'        => ['label' => 'Tenggat', 'type' => 'date'],
            'status'               => ['label' => 'Status', 'type' => 'select', 'required' => true, 'span' => true,
                                       'options' => collect($statuses)->map(fn ($s) => $s[0])],
        ];

        $endUserFields = [
            'nama'     => 'Nama pelanggan',
            'industri' => 'Industri',
            'contact'  => 'Narahubung',
            'telepon'  => 'Telepon',
            'email'    => 'Email',
            'kota'     => 'Kota',
            'npwp'     => 'NPWP',
        ];

        $formatDate = fn ($date) => $date
            ? \Illuminate\Support\Carbon::parse($date)->translatedFormat('d M Y')
            : null;
        $formatValue = function ($field, $value) use ($formatDate) {
            if ($value === null || $value === '') return null;
            return match ($field['type'] ?? 'text') {
                'select'  => $field['options'][$value] ?? null,
                'money'   => 'Rp ' . number_format((float) $value, 2, ',', '.'),
                'percent' => number_format((float) $value, 2, ',', '.') . ' %',
                'date'    => $formatDate($value),
                default   => $value,
            };
        };
        $isPaginated = $projects instanceof \Illuminate\Pagination\AbstractPaginator;
    @endphp
    <div class="space-y-7 sm:space-y-8">
        {{-- HEADER --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="workspace-kicker">Operasional</p>
                <h2 class="mt-1 text-2xl font-bold tracking-tight text-slate-950 sm:text-3xl">Manajemen Proyek</h2>
                <p class="mt-2 text-sm text-slate-500">Kelola proyek, PIC, nilai kontrak, dan target penyelesaian.</p>
            </div>
            <button type="button" onclick="openModal('createModal')"
                class="inline-flex items-center justify-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700 focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-indigo-500/20">
                <span class="text-lg leading-none">+</span> Tambah proyek
            </button>
        </div>
        {{-- ALERT --}}
        @if (session('success'))
            <div id="flashAlert" class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ session('success') }}
            </div>
        @endif

        {{-- TABLE CONTAINER --}}
        <div class="workspace-panel overflow-hidden">
            <form method="GET" action="{{ route('admin.projects.index') }}" data-live-filter data-results-target="#projectResults" class="grid gap-3 border-b border-slate-100 bg-white p-4 sm:grid-cols-2 sm:p-5 lg:grid-cols-5">
                <div class="relative lg:col-span-2">
                    <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path stroke-linecap="round" d="m16.5 16.5 4 4"/></svg>
                    <input name="q" type="search" value="{{ $filters['q'] ?? '' }}" placeholder="Cari proyek, pelanggan, atau PIC..." class="workspace-input pl-10">
                </div>
                <select name="status" class="workspace-input">
                    <option value="">Semua status</option>
                    @foreach ($statuses as $value => [$statusLabel])
                        <option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>{{ $statusLabel }}</option>
                    @endforeach
                </select>
                <select name="pic_id" class="workspace-input">
                    <option value="">Semua PIC</option>
                    @foreach ($pics as $pic)
                        <option value="{{ $pic->id }}" @selected((string) ($filters['pic_id'] ?? '') === (string) $pic->id)>{{ $pic->nama }}</option>
                    @endforeach
                </select>
                <select name="end_user_id" class="workspace-input">
                    <option value="">Semua pelanggan</option>
                    @foreach ($endUsers as $endUser)
                        <option value="{{ $endUser->id }}" @selected((string) ($filters['end_user_id'] ?? '') === (string) $endUser->id)>{{ $endUser->nama }}</option>
                    @endforeach
                </select>
                <div class="flex gap-2 sm:col-span-2 lg:col-span-5">
                    <a href="{{ route('admin.projects.index') }}" data-filter-reset class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-600 transition hover:border-indigo-200 hover:bg-indigo-50 hover:text-indigo-700">Atur ulang filter</a>
                </div>
            </form>
            <p data-filter-status role="status" aria-live="polite" class="sr-only"></p>
            <div id="projectResults" data-live-results aria-live="polite">

            @include('admin.projects.partials.desktop-table')
            @include('admin.projects.partials.mobile-cards')
    @include('admin.projects.partials.project-form-modals')
    @include('admin.projects.partials.project-detail-modal')
    @include('admin.projects.partials.end-user-detail-modal')
    @include('admin.projects.partials.scripts')
</x-admin-layout>
