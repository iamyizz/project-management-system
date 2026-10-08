<x-admin-layout title="Projects">
    @php
        $input    = 'w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20';
        $label    = 'mb-1.5 block text-sm font-medium text-slate-700';
        $btnSmall = 'rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50';
        $formType = old('form_type');
        $statuses = [
            'planning' => ['Planning', 'bg-yellow-100 text-yellow-700'],
            'progress' => ['Progress', 'bg-blue-100 text-blue-700'],
            'done'     => ['Done',     'bg-green-100 text-green-700'],
            'cancel'   => ['Cancel',   'bg-red-100 text-red-700'],
        ];
        $fields = [
            'project_name'         => ['label' => 'Project Name', 'required' => true, 'span' => true],
            'end_user_id'          => ['label' => 'End User', 'type' => 'select', 'required' => true, 'options' => $endUsers->pluck('nama', 'id')],
            'pic_id'               => ['label' => 'PIC', 'type' => 'select', 'options' => $pics->pluck('nama', 'id')],
            'account'              => ['label' => 'Account', 'type' => 'money'],
            'po_number'            => ['label' => 'PO Number'],
            'quotation_number'     => ['label' => 'Quotation Number'],
            'quotation_distribusi' => ['label' => 'Quotation Distribusi', 'type' => 'money'],
            'margin'               => ['label' => 'Margin', 'type' => 'money', 'readonly' => true],
            'percentage'           => ['label' => 'Percentage', 'type' => 'percent', 'readonly' => true],
            'deadline_date'        => ['label' => 'Deadline', 'type' => 'date'],
            'status'               => ['label' => 'Status', 'type' => 'select', 'required' => true, 'span' => true,
                                       'options' => collect($statuses)->map(fn ($s) => $s[0])],
        ];

        $endUserFields = [
            'nama'     => 'Nama End User',
            'industri' => 'Industri',
            'contact'  => 'Contact Person',
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
    <div class="space-y-6">
        {{-- HEADER --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-2xl font-bold text-slate-900">Project Management</h2>
                <p class="mt-1 text-sm text-slate-500">Kelola seluruh project perusahaan</p>
            </div>
            <button type="button" onclick="openModal('createModal')"
                class="rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700">
                + Tambah Project
            </button>
        </div>
        {{-- ALERT --}}
        @if (session('success'))
            <div id="flashAlert" class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ session('success') }}
            </div>
        @endif

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
                            <th class="px-6 py-3 font-semibold">PIC</th>
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
                                $overdue = $deadline
                                    && $deadline->lt(today())
                                    && ! in_array($project->status, ['done', 'cancel']);
                                $rowData = [
                                    'id'         => $project->id,
                                    'update_url' => route('admin.projects.update', $project),
                                    'end_user'   => $project->endUser ? $project->endUser->only(array_keys($endUserFields)) : null,
                                    'values'     => array_merge(
                                        $project->only(array_keys($fields)),
                                        ['deadline_date' => $deadline?->format('Y-m-d')]
                                    ),
                                    'labels'     => collect($fields)->map(fn ($f, $name) => $formatValue($f, $project->$name)),
                                ];
                            @endphp
                            <tr class="data-row transition hover:bg-slate-50" data-project="{{ json_encode($rowData) }}">
                                <td class="px-6 py-4 text-slate-500">
                                    {{ $isPaginated ? $projects->firstItem() + $loop->index : $loop->iteration }}
                                </td>
                                <td class="px-6 py-4 font-medium text-slate-900">
                                    @if ($project->endUser)
                                        <button
                                            type="button"
                                            onclick="openEndUserDetail(this)"
                                            aria-haspopup="dialog"
                                            aria-controls="endUserDetailModal"
                                            class="rounded text-left text-indigo-600 underline-offset-4 hover:text-indigo-800 hover:underline focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500"
                                        >
                                            {{ $project->endUser->nama }}
                                        </button>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-slate-700">{{ $project->project_name }}</td>
                                <td class="px-6 py-4 text-slate-700">{{ $project->pic?->nama ?? '-' }}</td>
                                <td class="whitespace-nowrap px-6 py-4 {{ $overdue ? 'font-semibold text-red-600' : 'text-slate-700' }}">
                                    {{ $formatDate($deadline) ?? '-' }}
                                    @if ($overdue)
                                        <span class="ml-1 rounded-full bg-red-100 px-2 py-0.5 text-xs font-medium text-red-700">Overdue</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <span class="rounded-full px-3 py-1 text-xs font-medium {{ $statusClass }}">
                                        {{ $statusLabel }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex justify-end gap-2">
                                        <button type="button" onclick="openDetail(this)" class="{{ $btnSmall }}">Detail</button>
                                        <button type="button" onclick="openEdit(this)" class="{{ $btnSmall }}">Edit</button>
                                        <form method="POST" action="{{ route('admin.projects.destroy', $project) }}"
                                            class="inline" onsubmit="return confirm('Hapus project ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rounded-lg border border-red-200 px-3 py-1.5 text-xs font-medium text-red-600 hover:bg-red-50">Hapus</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-10 text-center text-slate-500">Belum ada project.</td>
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
                        $overdue = $deadline
                            && $deadline->lt(today())
                            && ! in_array($project->status, ['done', 'cancel']);
                        $rowData = [
                            'id'         => $project->id,
                            'update_url' => route('admin.projects.update', $project),

                            'end_user' => $project->endUser
                                ? $project->endUser->only(array_keys($endUserFields))
                                : null,

                            'values' => array_merge(
                                $project->only(array_keys($fields)),
                                ['deadline_date' => $deadline?->format('Y-m-d')]
                            ),

                            'labels' => collect($fields)->map(
                                fn ($f, $name) => $formatValue($f, $project->$name)
                            ),
                        ];
                    @endphp
                    <div class="data-row rounded-lg border border-slate-200 bg-slate-50 p-4 transition hover:shadow-md" data-project="{{ json_encode($rowData) }}">
                        {{-- Header Card --}}
                        <div class="mb-4 flex items-start justify-between gap-3">
                            <div class="flex-1 min-w-0">
                                <p class="truncate font-semibold text-slate-900">{{ $project->project_name }}</p>
                                <div class="text-sm">
                                    @if ($project->endUser)
                                        <button
                                            type="button"
                                            onclick="openEndUserDetail(this)"
                                            aria-haspopup="dialog"
                                            aria-controls="endUserDetailModal"
                                            class="block max-w-full truncate rounded text-left font-medium text-indigo-600 underline-offset-4 hover:text-indigo-800 hover:underline focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500"
                                        >
                                            {{ $project->endUser->nama }}
                                        </button>
                                    @else
                                        <span class="text-slate-600">-</span>
                                    @endif
                                </div>

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
                            <div class="flex justify-between">
                                <span class="text-slate-500">Deadline:</span>
                                <span class="font-medium {{ $overdue ? 'text-red-600' : 'text-slate-900' }}">
                                    {{ $formatDate($deadline) ?? '-' }}
                                    @if ($overdue)
                                        <span class="ml-1">(Overdue)</span>
                                    @endif
                                </span>
                            </div>
                            @if ($project->account)
                                <div class="flex justify-between">
                                    <span class="text-slate-500">Account:</span>
                                    <span class="font-medium text-slate-900">Rp {{ number_format($project->account, 2, ',', '.') }}</span>
                                </div>
                            @endif
                        </div>

                        {{-- Action Buttons --}}
                        <div class="flex gap-2">
                            <button type="button" onclick="openDetail(this)"
                                class="flex-1 rounded-lg border border-indigo-300 bg-indigo-50 px-3 py-2 text-sm font-medium text-indigo-700 transition hover:bg-indigo-100">
                                Detail
                            </button>
                            <button type="button" onclick="openEdit(this)"
                                class="flex-1 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-700 transition hover:border-indigo-200 hover:bg-indigo-50 hover:text-indigo-700">
                                Edit
                            </button>
                            <form method="POST" action="{{ route('admin.projects.destroy', $project) }}"
                                class="flex-1" onsubmit="return confirm('Hapus project ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="w-full rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm font-medium text-red-600 transition hover:bg-red-100">
                                    Hapus
                                </button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="rounded-lg border border-dashed border-slate-300 bg-slate-50 px-6 py-12 text-center text-slate-500">
                        <p class="font-medium">Belum ada project</p>
                        <p class="mt-1 text-sm">Klik tombol "Tambah Project" untuk menambahkan project baru</p>
                    </div>
                @endforelse

                <div id="noResultCards" class="hidden rounded-lg border border-dashed border-slate-300 bg-slate-50 px-6 py-12 text-center text-slate-500">
                    <p class="font-medium">Tidak ada hasil</p>
                </div>
            </div>

            @if ($isPaginated && $projects->hasPages())
                <div class="border-t border-slate-200 p-4">
                    {{ $projects->links() }}
                </div>
            @endif
        </div>
    </div>

    {{-- ================= MODAL CREATE & EDIT ================= --}}
    @foreach (['create' => 'Tambah Project', 'edit' => 'Edit Project'] as $mode => $title)
        <div id="{{ $mode }}Modal"
            class="modal fixed inset-0 z-50 {{ $formType === $mode ? 'flex' : 'hidden' }} items-center justify-center bg-slate-900/50 p-4">
            <div class="max-h-screen w-full max-w-2xl overflow-y-auto rounded-2xl bg-white shadow-xl">
                <div class="flex items-center justify-between border-b border-slate-200 px-6 py-4">
                    <h3 class="text-lg font-semibold text-slate-900">{{ $title }}</h3>
                    <button type="button" onclick="closeModal('{{ $mode }}Modal')" class="text-2xl leading-none text-slate-400 hover:text-slate-600">&times;</button>
                </div>
                <form id="{{ $mode }}Form" method="POST"
                    action="{{ $mode === 'create'
                        ? route('admin.projects.store')
                        : ($formType === 'edit' && old('edit_id') ? route('admin.projects.update', old('edit_id')) : '') }}">
                    @csrf
                    @if ($mode === 'edit')
                        @method('PUT')
                        <input type="hidden" name="edit_id" value="{{ $formType === 'edit' ? old('edit_id') : '' }}">
                    @endif
                    <input type="hidden" name="form_type" value="{{ $mode }}">
                    <div class="grid grid-cols-1 gap-4 px-6 py-5 sm:grid-cols-2">
                        @foreach ($fields as $name => $field)
                            @php
                                $type       = $field['type'] ?? 'text';
                                $id         = $mode . '_' . $name;
                                $required   = $field['required'] ?? false;
                                $readonly   = $field['readonly'] ?? false;
                                $value      = $formType === $mode
                                    ? old($name)
                                    : ($mode === 'create' && $name === 'status' ? 'planning' : '');
                            @endphp
                            <div class="{{ !empty($field['span']) ? 'sm:col-span-2' : '' }}">
                                <label for="{{ $id }}" class="{{ $label }}">
                                    {{ $field['label'] }}
                                    @if ($required)<span class="text-red-500">*</span>@endif
                                </label>
                                @if ($type === 'select')
                                    <select id="{{ $id }}" name="{{ $name }}" class="{{ $input }}" @required($required) @if($readonly) disabled @endif>
                                        <option value="">Pilih {{ $field['label'] }}</option>
                                        @foreach ($field['options'] as $key => $text)
                                            <option value="{{ $key }}" @selected((string) $value === (string) $key)>{{ $text }}</option>
                                        @endforeach
                                    </select>
                                @elseif ($type === 'money' || $type === 'percent')
                                    <div class="relative">
                                        @if ($type === 'money')
                                            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-sm text-slate-500">Rp</span>
                                        @endif
                                        <input
                                            id="{{ $id }}"
                                            type="number"
                                            name="{{ $name }}"
                                            value="{{ $value }}"
                                            step="0.01"
                                            @if ($name === 'quotation_distribusi') min="0" @endif
                                            @if ($type === 'percent') min="-999.99" max="999.99" @endif
                                            class="{{ $input }} {{ $type === 'money' ? 'pl-10' : 'pr-10' }} {{ $readonly ? 'bg-slate-100 cursor-not-allowed' : '' }}"
                                            @if ($readonly) readonly @endif
                                            data-calculate="{{ in_array($name, ['account', 'quotation_distribusi']) ? 'true' : 'false' }}">
                                        @if ($type === 'percent')
                                            <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-sm text-slate-500">%</span>
                                        @endif
                                    </div>
                                @elseif ($type === 'date')
                                    <input id="{{ $id }}" type="date" name="{{ $name }}" value="{{ $value }}"
                                        class="{{ $input }}" @required($required)>
                                @else
                                    <input id="{{ $id }}" type="text" name="{{ $name }}" value="{{ $value }}" maxlength="255"
                                        class="{{ $input }}" @required($required)>
                                @endif
                                @if ($formType === $mode)
                                    @error($name)
                                        <p class="field-error mt-1 text-xs text-red-600">{{ $message }}</p>
                                    @enderror
                                @endif
                            </div>
                        @endforeach
                    </div>
                    <div class="flex justify-end gap-2 border-t border-slate-200 px-6 py-4">
                        <button type="button" onclick="closeModal('{{ $mode }}Modal')"
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
    @endforeach

    {{-- ================= MODAL DETAIL ================= --}}
    <div id="detailModal" class="modal fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/50 p-4">
        <div class="max-h-screen w-full max-w-2xl overflow-y-auto rounded-2xl bg-white shadow-xl">
            <div class="flex items-center justify-between border-b border-slate-200 px-6 py-4">
                <h3 class="text-lg font-semibold text-slate-900">Detail Project</h3>
                <button type="button" onclick="closeModal('detailModal')" class="text-2xl leading-none text-slate-400 hover:text-slate-600">&times;</button>
            </div>
            <dl class="grid grid-cols-1 gap-4 px-6 py-5 sm:grid-cols-2">
                @foreach ($fields as $name => $field)
                    <div class="{{ !empty($field['span']) ? 'sm:col-span-2' : '' }}">
                        <dt class="text-xs font-medium uppercase text-slate-500">{{ $field['label'] }}</dt>
                        <dd data-detail="{{ $name }}" class="mt-1 text-sm text-slate-900">-</dd>
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

    {{-- ================= MODAL DETAIL END USER ================= --}}
    <div
        id="endUserDetailModal"
        role="dialog"
        aria-modal="true"
        aria-labelledby="endUserDetailTitle"
        class="modal fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/50 p-4"
    >
        <div class="max-h-[90vh] w-full max-w-xl overflow-y-auto rounded-2xl bg-white shadow-xl">
            <div class="flex items-center justify-between border-b border-slate-200 px-6 py-4">
                <h3
                    id="endUserDetailTitle"
                    class="text-lg font-semibold text-slate-900"
                >
                    Detail End User
                </h3>

                <button
                    type="button"
                    onclick="closeModal('endUserDetailModal')"
                    aria-label="Tutup detail end user"
                    class="rounded text-2xl leading-none text-slate-400 hover:text-slate-600 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500"
                >
                    &times;
                </button>
            </div>

            <dl class="grid grid-cols-1 gap-4 px-6 py-5 sm:grid-cols-2">
                @foreach ($endUserFields as $name => $fieldLabel)
                    <div class="{{ $name === 'nama' ? 'sm:col-span-2' : '' }}">
                        <dt class="text-xs font-medium uppercase text-slate-500">
                            {{ $fieldLabel }}
                        </dt>

                        <dd
                            data-end-user-detail="{{ $name }}"
                            class="mt-1 whitespace-pre-wrap break-words text-sm text-slate-900"
                        >-</dd>
                    </div>
                @endforeach
            </dl>

            <div class="flex justify-end border-t border-slate-200 px-6 py-4">
                <button
                    type="button"
                    onclick="closeModal('endUserDetailModal')"
                    class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50"
                >
                    Tutup
                </button>
            </div>
        </div>
    </div>

    <script>
        // Format Rupiah
        function formatRupiah(value) {
            if (!value) return '';
            const num = parseFloat(value);
            if (isNaN(num)) return '';
            return num.toLocaleString('id-ID', {
                minimumFractionDigits: 0,
                maximumFractionDigits: 2
            });
        }

        // Auto-calculate margin & percentage
        function calculateFinance(form) {
            const accountInput = form.querySelector('[name="account"]');
            const quotationInput = form.querySelector('[name="quotation_distribusi"]');
            const marginInput = form.querySelector('[name="margin"]');
            const percentageInput = form.querySelector('[name="percentage"]');

            if (!accountInput || !quotationInput || !marginInput || !percentageInput) return;

            const account = parseFloat(accountInput.value) || 0;
            const quotation = parseFloat(quotationInput.value) || 0;
            const margin = account - quotation;
            const percentage = account > 0 ? (margin / account) * 100 : 0;

            marginInput.value = margin.toFixed(2);
            percentageInput.value = percentage.toFixed(2);
        }

        // Setup money input formatting
        document.querySelectorAll('input[type="number"][step="0.01"]').forEach(input => {
            if (!input.readOnly) {
                input.addEventListener('blur', function() {
                    if (this.value) {
                        this.placeholder = formatRupiah(this.value);
                    }
                });

                input.addEventListener('focus', function() {
                    this.placeholder = '';
                });

                if (this.value) {
                    this.placeholder = formatRupiah(this.value);
                }
            }
        });

        // Setup auto-calculate listener untuk form
        document.querySelectorAll('form').forEach(form => {
            const accountInput = form.querySelector('[name="account"]');
            const quotationInput = form.querySelector('[name="quotation_distribusi"]');

            if (accountInput && quotationInput) {
                accountInput.addEventListener('input', () => calculateFinance(form));
                accountInput.addEventListener('change', () => calculateFinance(form));
                quotationInput.addEventListener('input', () => calculateFinance(form));
                quotationInput.addEventListener('change', () => calculateFinance(form));

                calculateFinance(form);
            }
        });

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
            const { labels } = rowData(btn);
            document.querySelectorAll('#detailModal [data-detail]').forEach((el) => {
                el.textContent = labels[el.dataset.detail] || '-';
            });
            openModal('detailModal');
        }

        function openEdit(btn) {
            const data = rowData(btn);
            const form = document.getElementById('editForm');
            form.action = data.update_url;
            form.elements['edit_id'].value = data.id;
            Object.entries(data.values).forEach(([name, value]) => {
                if (form.elements[name]) {
                    form.elements[name].value = value ?? '';
                    if (!form.elements[name].readOnly && form.elements[name].type === 'number' && value) {
                        form.elements[name].placeholder = formatRupiah(value);
                    }
                }
            });
            form.querySelectorAll('.field-error').forEach((el) => el.remove());

            setTimeout(() => {
                const accountInput = form.querySelector('[name="account"]');
                const quotationInput = form.querySelector('[name="quotation_distribusi"]');

                if (accountInput && quotationInput) {
                    accountInput.addEventListener('input', () => calculateFinance(form));
                    accountInput.addEventListener('change', () => calculateFinance(form));
                    quotationInput.addEventListener('input', () => calculateFinance(form));
                    quotationInput.addEventListener('change', () => calculateFinance(form));

                    calculateFinance(form);
                }
            }, 100);

            openModal('editModal');
        }

        // Tutup modal lewat overlay/Esc
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

        function openEndUserDetail(btn) {
            const { end_user } = rowData(btn);

            if (!end_user) return;

            const modal = document.getElementById('endUserDetailModal');

            modal.querySelectorAll('[data-end-user-detail]').forEach((el) => {
                const value = end_user[el.dataset.endUserDetail];

                // Gunakan textContent supaya data ditampilkan sebagai teks,
                // bukan diproses sebagai HTML.
                el.textContent =
                    value === null || value === undefined || value === ''
                        ? '-'
                        : String(value);
            });

            openModal('endUserDetailModal');
        }

    </script>
</x-admin-layout>
