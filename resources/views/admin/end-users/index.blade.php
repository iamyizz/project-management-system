<x-admin-layout title="End Users">
@php
    $input        = 'w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20';
    $label        = 'mb-1.5 block text-sm font-medium text-slate-700';
    $btnPrimary   = 'inline-flex items-center justify-center gap-2 rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700';
    $btnSecondary = 'rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-100';
    $btnSmall     = 'rounded-lg border px-3 py-1.5 text-sm font-medium transition';

    $formType = old('form_type');

    $fields = [
        'nama'     => ['label' => 'Nama',     'type' => 'text',  'required' => true],
        'industri' => ['label' => 'Industri', 'type' => 'text'],
        'contact'  => ['label' => 'Contact',  'type' => 'text'],
        'telepon'  => ['label' => 'Telepon',  'type' => 'tel'],
        'email'    => ['label' => 'Email',    'type' => 'email'],
        'kota'     => ['label' => 'Kota',     'type' => 'text'],
        'npwp'     => ['label' => 'NPWP',     'type' => 'text'],
    ];

    $total = method_exists($endUsers, 'total') ? $endUsers->total() : $endUsers->count();
@endphp

<div class="space-y-6">

    {{-- Header --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-2xl font-bold text-slate-900">End User Management</h2>
            <p class="mt-1 text-sm text-slate-500">Total {{ $total }} customer terdaftar</p>
        </div>

        <button type="button" onclick="openModal('createModal')" class="{{ $btnPrimary }}">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.5v15m7.5-7.5h-15"/>
            </svg>
            Tambah End User
        </button>
    </div>

    {{-- Alert --}}
    @if (session('success'))
        <div id="flashAlert" class="flex items-center justify-between rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
            <span>{{ session('success') }}</span>
            <button type="button" onclick="this.parentElement.remove()" class="text-lg font-bold leading-none">&times;</button>
        </div>
    @endif

    {{-- Table Container --}}
    <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
        <div class="border-b border-slate-200 p-4">
            <input type="text" id="search" placeholder="Cari customer..." class="{{ $input }}">
        </div>

        {{-- Desktop: Tabel --}}
        <div class="hidden overflow-x-auto md:block">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-xs uppercase text-slate-500">
                    <tr>
                        <th class="px-6 py-3 font-semibold">Nama</th>
                        <th class="px-6 py-3 font-semibold">Industri</th>
                        <th class="px-6 py-3 font-semibold">Contact</th>
                        <th class="px-6 py-3 font-semibold">Kota</th>
                        <th class="px-6 py-3 text-right font-semibold">Aksi</th>
                    </tr>
                </thead>

                <tbody id="endUserTable" class="divide-y divide-slate-100">
                    @forelse ($endUsers as $endUser)
                        <tr
                            class="data-row transition hover:bg-slate-50"
                            data-id="{{ $endUser->id }}"
                            @foreach (array_keys($fields) as $field)
                                data-{{ $field }}="{{ $endUser->$field }}"
                            @endforeach>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-indigo-100 font-semibold text-indigo-700">
                                        {{ strtoupper(substr($endUser->nama, 0, 1)) }}
                                    </div>
                                    <div class="min-w-0">
                                        <p class="truncate font-medium text-slate-900">{{ $endUser->nama }}</p>
                                        <p class="truncate text-slate-500">{{ $endUser->email ?? '-' }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-slate-700">{{ $endUser->industri ?? '-' }}</td>
                            <td class="px-6 py-4 text-slate-700">{{ $endUser->contact ?? '-' }}</td>
                            <td class="px-6 py-4 text-slate-700">{{ $endUser->kota ?? '-' }}</td>
                            <td class="px-6 py-4">
                                <div class="flex justify-end gap-2 whitespace-nowrap">
                                    <button type="button" onclick="openDetail(this)"
                                        class="{{ $btnSmall }} border-indigo-200 text-indigo-700 hover:bg-indigo-50">
                                        Detail
                                    </button>
                                    <button type="button" onclick="openEdit(this)"
                                        class="{{ $btnSmall }} border-slate-200 text-slate-700 hover:border-indigo-200 hover:bg-indigo-50 hover:text-indigo-700">
                                        Edit
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-slate-500">Belum ada End User.</td>
                        </tr>
                    @endforelse

                    <tr id="noResultTable" class="hidden">
                        <td colspan="5" class="px-6 py-12 text-center text-slate-500">Tidak ada hasil.</td>
                    </tr>
                </tbody>
            </table>
        </div>

        {{-- Mobile: Card View --}}
        <div id="endUserCards" class="space-y-4 p-4 md:hidden">
            @forelse ($endUsers as $endUser)
                <div
                    class="data-row rounded-lg border border-slate-200 bg-slate-50 p-4 transition hover:shadow-md"
                    data-id="{{ $endUser->id }}"
                    @foreach (array_keys($fields) as $field)
                        data-{{ $field }}="{{ $endUser->$field }}"
                    @endforeach>
                    {{-- Header Card --}}
                    <div class="mb-4 flex items-start justify-between gap-3">
                        <div class="flex items-center gap-3 flex-1 min-w-0">
                            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-indigo-100 font-semibold text-indigo-700">
                                {{ strtoupper(substr($endUser->nama, 0, 1)) }}
                            </div>
                            <div class="min-w-0">
                                <p class="truncate font-semibold text-slate-900">{{ $endUser->nama }}</p>
                                <p class="truncate text-sm text-slate-600">{{ $endUser->email ?? '-' }}</p>
                            </div>
                        </div>
                    </div>

                    {{-- Content Card --}}
                    <div class="mb-4 space-y-2 text-sm">
                        @if ($endUser->industri)
                            <div class="flex justify-between">
                                <span class="text-slate-500">Industri:</span>
                                <span class="font-medium text-slate-900">{{ $endUser->industri }}</span>
                            </div>
                        @endif
                        @if ($endUser->contact)
                            <div class="flex justify-between">
                                <span class="text-slate-500">Contact:</span>
                                <span class="font-medium text-slate-900">{{ $endUser->contact }}</span>
                            </div>
                        @endif
                        @if ($endUser->kota)
                            <div class="flex justify-between">
                                <span class="text-slate-500">Kota:</span>
                                <span class="font-medium text-slate-900">{{ $endUser->kota }}</span>
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
                    </div>
                </div>
            @empty
                <div class="rounded-lg border border-dashed border-slate-300 bg-slate-50 px-6 py-12 text-center text-slate-500">
                    <p class="font-medium">Belum ada End User</p>
                    <p class="mt-1 text-sm">Klik tombol "Tambah End User" untuk menambahkan customer baru</p>
                </div>
            @endforelse

            <div id="noResultCards" class="hidden rounded-lg border border-dashed border-slate-300 bg-slate-50 px-6 py-12 text-center text-slate-500">
                <p class="font-medium">Tidak ada hasil</p>
            </div>
        </div>

        @if (method_exists($endUsers, 'links'))
            <div class="border-t border-slate-200 px-6 py-4">
                {{ $endUsers->links() }}
            </div>
        @endif
    </div>
</div>

{{-- ================= CREATE MODAL ================= --}}
<div id="createModal" class="modal fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/50 p-4">
    <div class="flex max-h-[90vh] w-full max-w-lg flex-col overflow-hidden rounded-2xl bg-white shadow-xl">
        <form method="POST" action="{{ route('admin.end-users.store') }}" class="flex min-h-0 flex-col">
            @csrf
            <input type="hidden" name="form_type" value="create">

            <div class="border-b border-slate-200 px-6 py-4">
                <h3 class="text-lg font-semibold text-slate-900">Tambah End User</h3>
            </div>

            <div class="space-y-4 overflow-y-auto px-6 py-5">
                @if ($errors->any() && $formType === 'create')
                    <div class="rounded-lg bg-red-50 p-3 text-sm text-red-700">
                        @foreach ($errors->all() as $error)
                            <p>{{ $error }}</p>
                        @endforeach
                    </div>
                @endif

                @foreach ($fields as $field => $meta)
                    <div>
                        <label for="create_{{ $field }}" class="{{ $label }}">
                            {{ $meta['label'] }}
                            @if (!empty($meta['required'])) <span class="text-red-500">*</span> @endif
                        </label>
                        <input
                            id="create_{{ $field }}"
                            type="{{ $meta['type'] }}"
                            name="{{ $field }}"
                            value="{{ $formType === 'create' ? old($field) : '' }}"
                            class="{{ $input }}"
                            @required(!empty($meta['required']))>
                    </div>
                @endforeach
            </div>

            <div class="flex justify-end gap-3 border-t border-slate-200 bg-slate-50 px-6 py-4">
                <button type="button" onclick="closeModal('createModal')" class="{{ $btnSecondary }}">Batal</button>
                <button type="submit" class="{{ $btnPrimary }}">Simpan</button>
            </div>
        </form>
    </div>
</div>

{{-- ================= DETAIL MODAL ================= --}}
<div id="detailModal" class="modal fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/50 p-4">
    <div class="flex max-h-[90vh] w-full max-w-lg flex-col overflow-hidden rounded-2xl bg-white shadow-xl">
        <div class="flex items-center justify-between border-b border-slate-200 px-6 py-4">
            <h3 class="text-lg font-semibold text-slate-900">Detail End User</h3>
            <button type="button" onclick="closeModal('detailModal')" class="text-2xl leading-none text-slate-400 transition hover:text-slate-700">&times;</button>
        </div>

        <div class="grid grid-cols-1 gap-5 overflow-y-auto px-6 py-5 sm:grid-cols-2">
            @foreach ($fields as $field => $meta)
                <div class="{{ $loop->last ? 'sm:col-span-2' : '' }}">
                    <p class="text-xs font-medium uppercase tracking-wide text-slate-500">{{ $meta['label'] }}</p>
                    <p id="detail_{{ $field }}" class="mt-1 break-words font-medium text-slate-900">-</p>
                </div>
            @endforeach
        </div>

        <div class="flex justify-end border-t border-slate-200 bg-slate-50 px-6 py-4">
            <button type="button" onclick="closeModal('detailModal')" class="{{ $btnSecondary }}">Tutup</button>
        </div>
    </div>
</div>

{{-- ================= EDIT MODAL ================= --}}
<div id="editModal" class="modal fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/50 p-4">
    <div class="flex max-h-[90vh] w-full max-w-lg flex-col overflow-hidden rounded-2xl bg-white shadow-xl">
        <form
            id="editForm"
            method="POST"
            action="{{ $formType === 'edit' && old('edit_id') ? route('admin.end-users.update', old('edit_id')) : '' }}"
            class="flex min-h-0 flex-col">
            @csrf
            @method('PUT')
            <input type="hidden" name="form_type" value="edit">
            <input type="hidden" name="edit_id" id="edit_id" value="{{ $formType === 'edit' ? old('edit_id') : '' }}">

            <div class="border-b border-slate-200 px-6 py-4">
                <h3 class="text-lg font-semibold text-slate-900">Edit End User</h3>
            </div>

            <div class="space-y-4 overflow-y-auto px-6 py-5">
                @if ($errors->any() && $formType === 'edit')
                    <div class="rounded-lg bg-red-50 p-3 text-sm text-red-700">
                        @foreach ($errors->all() as $error)
                            <p>{{ $error }}</p>
                        @endforeach
                    </div>
                @endif

                @foreach ($fields as $field => $meta)
                    <div>
                        <label for="edit_{{ $field }}" class="{{ $label }}">
                            {{ $meta['label'] }}
                            @if (!empty($meta['required'])) <span class="text-red-500">*</span> @endif
                        </label>
                        <input
                            id="edit_{{ $field }}"
                            type="{{ $meta['type'] }}"
                            name="{{ $field }}"
                            value="{{ $formType === 'edit' ? old($field) : '' }}"
                            class="{{ $input }}"
                            @required(!empty($meta['required']))>
                    </div>
                @endforeach
            </div>

            <div class="flex justify-end gap-3 border-t border-slate-200 bg-slate-50 px-6 py-4">
                <button type="button" onclick="closeModal('editModal')" class="{{ $btnSecondary }}">Batal</button>
                <button type="submit" class="{{ $btnPrimary }}">Update</button>
            </div>
        </form>
    </div>
</div>

<script>
    const FIELDS     = @json(array_keys($fields));
    const UPDATE_URL = @json(route('admin.end-users.update', '__ID__'));

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
        document.body.classList.remove('overflow-hidden');
    }

    // Ambil data dari elemen yang klik
    const rowData = btn => btn.closest('.data-row').dataset;

    function openDetail(btn) {
        const data = rowData(btn);
        FIELDS.forEach(field => {
            document.getElementById('detail_' + field).textContent = data[field] || '-';
        });
        openModal('detailModal');
    }

    function openEdit(btn) {
        const data = rowData(btn);
        document.getElementById('editForm').action = UPDATE_URL.replace('__ID__', data.id);
        document.getElementById('edit_id').value = data.id;
        FIELDS.forEach(field => {
            document.getElementById('edit_' + field).value = data[field] || '';
        });
        openModal('editModal');
    }

    // Tutup modal: klik overlay
    document.querySelectorAll('.modal').forEach(modal => {
        modal.addEventListener('click', e => {
            if (e.target === modal) closeModal(modal.id);
        });
    });

    // Tutup modal: tombol Esc
    document.addEventListener('keydown', e => {
        if (e.key !== 'Escape') return;
        document.querySelectorAll('.modal.flex').forEach(modal => closeModal(modal.id));
    });

    // Pencarian di kedua view (tabel & card)
    document.getElementById('search').addEventListener('input', function () {
        const q = this.value.toLowerCase().trim();
        const allRows = document.querySelectorAll('.data-row');
        let visible = 0;

        allRows.forEach(row => {
            const match = row.textContent.toLowerCase().includes(q);
            row.style.display = match ? '' : 'none';
            if (match) visible++;
        });

        // Tampilkan pesan "tidak ada hasil" jika perlu
        document.getElementById('noResultTable')?.classList.toggle('hidden', visible > 0 || allRows.length === 0);
        document.getElementById('noResultCards')?.classList.toggle('hidden', visible > 0 || allRows.length === 0);
    });

    // Flash alert hilang otomatis
    setTimeout(() => document.getElementById('flashAlert')?.remove(), 4000);

    // Buka ulang modal jika validasi gagal
    @if ($errors->any())
        openModal(@json($formType === 'edit' ? 'editModal' : 'createModal'));
    @endif
</script>
</x-admin-layout>
