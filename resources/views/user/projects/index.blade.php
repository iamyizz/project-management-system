<x-admin-layout title="Project Saya">
    @php
        $input    = 'w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20';
        $btnSmall = 'rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50';
        $statuses = [
            'planning' => ['Planning', 'bg-yellow-100 text-yellow-700'],
            'progress' => ['Progress', 'bg-blue-100 text-blue-700'],
            'on_hold'  => ['On Hold',  'bg-orange-100 text-orange-700'],
            'done'     => ['Done',     'bg-green-100 text-green-700'],
            'cancel'   => ['Cancel',   'bg-red-100 text-red-700'],
        ];
        $userStatuses = ['planning', 'progress', 'on_hold', 'done'];
        $details = [
            'project_name'         => ['label' => 'Project Name', 'span' => true],
            'end_user'             => ['label' => 'End User'],
            'pic'                  => ['label' => 'PIC'],
            'account'              => ['label' => 'Account'],
            'po_number'            => ['label' => 'PO Number'],
            'quotation_number'     => ['label' => 'Quotation Number'],
            'quotation_distribusi' => ['label' => 'Quotation Distribusi'],
            'margin'               => ['label' => 'Margin'],
            'percentage'           => ['label' => 'Percentage'],
            'deadline_date'        => ['label' => 'Deadline'],
            'status'               => ['label' => 'Status'],
        ];
        $formatDate    = fn ($d) => $d ? \Illuminate\Support\Carbon::parse($d)->translatedFormat('d M Y') : null;
        $formatMoney   = fn ($v) => $v !== null && $v !== '' ? 'Rp ' . number_format((float) $v, 2, ',', '.') : null;
        $formatPercent = fn ($v) => $v !== null && $v !== '' ? number_format((float) $v, 2, ',', '.') . ' %' : null;
        $formType = old('form_type');
    @endphp

    <div class="space-y-6">
        {{-- HEADER --}}
        <div>
            <h2 class="text-2xl font-bold text-slate-900">Project Saya</h2>
            <p class="mt-1 text-sm text-slate-500">Daftar project yang ditugaskan ke kamu</p>
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
                ['Total Project', $totalProject, 'text-slate-900', 'bg-slate-50'],
                ['Progress',      $progress,     'text-blue-600',  'bg-blue-50'],
                ['Selesai',       $done,         'text-green-600', 'bg-green-50'],
            ] as [$title, $count, $color, $bg])
                <div class="rounded-lg sm:rounded-2xl {{ $bg }} p-4 sm:p-5 ring-1 ring-slate-200">
                    <p class="text-xs sm:text-sm text-slate-600">{{ $title }}</p>
                    <p class="mt-2 text-2xl sm:text-3xl font-bold {{ $color }}">{{ $count }}</p>
                </div>
            @endforeach
        </div>

        {{-- TABLE CONTAINER --}}
        <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
            <div class="border-b border-slate-200 p-4">
                <input id="search" type="text" placeholder="Cari project..." class="{{ $input }}">
            </div>

            {{-- DESKTOP: Tabel --}}
            <div class="hidden overflow-x-auto md:block">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 text-xs uppercase text-slate-500">
                        <tr>
                            <th class="px-6 py-3 font-semibold">No</th>
                            <th class="px-6 py-3 font-semibold">End User</th>
                            <th class="px-6 py-3 font-semibold">Project Name</th>
                            <th class="px-6 py-3 font-semibold">Deadline</th>
                            <th class="px-6 py-3 font-semibold">Status</th>
                            <th class="px-6 py-3 text-right font-semibold">Action</th>
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
                                    'status_class' => $statusClass,
                                    'labels'       => [
                                        'project_name'         => $project->project_name,
                                        'end_user'             => $project->endUser?->nama,
                                        'pic'                  => $project->pic?->nama,
                                        'account'              => $formatMoney($project->account),
                                        'po_number'            => $project->po_number,
                                        'quotation_number'     => $project->quotation_number,
                                        'quotation_distribusi' => $formatMoney($project->quotation_distribusi),
                                        'margin'               => $formatMoney($project->margin),
                                        'percentage'           => $formatPercent($project->percentage),
                                        'deadline_date'        => $formatDate($deadline),
                                        'status'               => $statusLabel,
                                    ],
                                ];
                            @endphp
                            <tr class="data-row transition hover:bg-slate-50" data-project="{{ json_encode($rowData) }}">
                                <td class="px-6 py-4 text-slate-500">{{ $projects->firstItem() + $loop->index }}</td>
                                <td class="px-6 py-4 text-slate-700">{{ $project->endUser?->nama ?? '-' }}</td>
                                <td class="px-6 py-4 font-medium text-slate-900">{{ $project->project_name }}</td>
                                <td class="whitespace-nowrap px-6 py-4 {{ $overdue ? 'font-semibold text-red-600' : 'text-slate-700' }}">
                                    {{ $formatDate($deadline) ?? '-' }}
                                    @if ($overdue)
                                        <span class="ml-1 rounded-full bg-red-100 px-2 py-0.5 text-xs font-medium text-red-700">Overdue</span>
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
                                                Update
                                            </button>
                                        @else
                                            <button type="button" disabled
                                                class="cursor-not-allowed rounded-lg bg-slate-100 px-3 py-1.5 text-xs font-medium text-slate-400">
                                                Update
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-10 text-center text-slate-500">Belum ada project yang ditugaskan.</td>
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
                            'status_class' => $statusClass,
                            'labels'       => [
                                'project_name'         => $project->project_name,
                                'end_user'             => $project->endUser?->nama,
                                'pic'                  => $project->pic?->nama,
                                'account'              => $formatMoney($project->account),
                                'po_number'            => $project->po_number,
                                'quotation_number'     => $project->quotation_number,
                                'quotation_distribusi' => $formatMoney($project->quotation_distribusi),
                                'margin'               => $formatMoney($project->margin),
                                'percentage'           => $formatPercent($project->percentage),
                                'deadline_date'        => $formatDate($deadline),
                                'status'               => $statusLabel,
                            ],
                        ];
                    @endphp
                    <div class="data-row rounded-lg border border-slate-200 bg-slate-50 p-4 transition hover:shadow-md" data-project="{{ json_encode($rowData) }}">
                        {{-- Header Card --}}
                        <div class="mb-4 flex items-start justify-between gap-3">
                            <div class="min-w-0 flex-1">
                                <p class="truncate font-semibold text-slate-900">{{ $project->project_name }}</p>
                                <p class="truncate text-sm text-slate-600">{{ $project->endUser?->nama ?? '-' }}</p>
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
                                <span class="text-slate-500">Deadline:</span>
                                <span class="font-medium">
                                    {{ $formatDate($deadline) ?? '-' }}
                                    @if ($overdue)
                                        <span class="ml-1">(Overdue)</span>
                                    @endif
                                </span>
                            </div>
                            @if ($project->account)
                                <div class="flex justify-between">
                                    <span class="text-slate-500">Account:</span>
                                    <span class="font-medium text-slate-900">{{ $formatMoney($project->account) }}</span>
                                </div>
                            @endif
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
                                    Update
                                </button>
                            @else
                                <button type="button" disabled
                                    class="flex-1 cursor-not-allowed rounded-lg bg-slate-100 px-3 py-2 text-sm font-medium text-slate-400">
                                    Update
                                </button>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="rounded-lg border border-dashed border-slate-300 bg-slate-50 px-6 py-12 text-center text-slate-500">
                        <p class="font-medium">Belum ada project</p>
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

    {{-- ================= MODAL DETAIL ================= --}}
    <div id="detailModal" class="modal fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/50 p-4">
        <div class="max-h-screen w-full max-w-2xl overflow-y-auto rounded-2xl bg-white shadow-xl">
            <div class="flex items-center justify-between border-b border-slate-200 px-6 py-4">
                <h3 class="text-lg font-semibold text-slate-900">Detail Project</h3>
                <button type="button" onclick="closeModal('detailModal')" class="text-2xl leading-none text-slate-400 hover:text-slate-600">&times;</button>
            </div>
            <dl class="grid grid-cols-1 gap-4 px-6 py-5 sm:grid-cols-2">
                @foreach ($details as $key => $detail)
                    <div class="{{ !empty($detail['span']) ? 'sm:col-span-2' : '' }}">
                        <dt class="text-xs font-medium uppercase text-slate-500">{{ $detail['label'] }}</dt>
                        <dd class="mt-1 text-sm text-slate-900">
                            @if ($key === 'status')
                                <span data-detail="status" class="rounded-full px-3 py-1 text-xs font-medium">-</span>
                            @else
                                <span data-detail="{{ $key }}">-</span>
                            @endif
                        </dd>
                    </div>
                @endforeach
            </dl>
            <div class="flex justify-end border-t border-slate-200 px-6 py-4">
                <button type="button" onclick="closeModal('detailModal')"
                    class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                    Tutup
                </button>
            </div>
        </div>
    </div>

    {{-- ================= MODAL UPDATE PROGRESS ================= --}}
    <div id="progressModal"
        class="modal fixed inset-0 z-50 {{ $formType === 'progress' ? 'flex' : 'hidden' }} items-center justify-center bg-slate-900/50 p-4">
        <div class="w-full max-w-md rounded-2xl bg-white shadow-xl">
            <div class="flex items-center justify-between border-b border-slate-200 px-6 py-4">
                <h3 class="text-lg font-semibold text-slate-900">Update Progress</h3>
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
                        <p class="text-xs text-slate-500">Project</p>
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
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            if (!document.querySelector('.modal.flex')) {
                document.body.classList.remove('overflow-hidden');
            }
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
            openModal('detailModal');
        }

        function openProgress(btn) {
            const data = rowData(btn);
            const form = document.getElementById('progressForm');
            form.action = data.update_url;
            form.elements['project_id'].value = data.id;
            form.elements['status'].value = data.status;
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

        // Pencarian di kedua view (tabel & card)
        document.getElementById('search').addEventListener('input', function () {
            const keyword = this.value.toLowerCase().trim();
            const allRows = document.querySelectorAll('.data-row');
            let visible = 0;

            allRows.forEach((row) => {
                const match = row.textContent.toLowerCase().includes(keyword);
                row.classList.toggle('hidden', !match);
                if (match) visible++;
            });

            // Tampilkan pesan "tidak ada hasil"
            if (document.getElementById('noResultCards')) {
                document.getElementById('noResultCards').classList.toggle('hidden', visible > 0 || allRows.length === 0);
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
