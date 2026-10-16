<x-admin-layout :title="__('Pelanggan')">
@php
    $input        = 'w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20';
    $label        = 'mb-1.5 block text-sm font-medium text-slate-700';
    $btnPrimary   = 'action-btn action-btn--primary px-4 py-2.5 text-sm font-semibold';
    $btnSecondary = 'action-btn action-btn--secondary px-4 py-2.5 text-sm';
    $btnSmall     = 'action-btn text-sm';

    $formType = old('form_type');

    $fields = [
        'nama'     => ['label' => __('Nama'), 'type' => 'text', 'required' => true],
        'industri' => ['label' => __('Industri'), 'type' => 'text'],
        'contact'  => ['label' => __('Narahubung'), 'type' => 'text'],
        'telepon'  => ['label' => __('Telepon'), 'type' => 'tel'],
        'email'    => ['label' => __('Email'), 'type' => 'email'],
        'kota'     => ['label' => __('Kota'), 'type' => 'text'],
        'npwp'     => ['label' => __('NPWP'), 'type' => 'text'],
    ];

    $total = method_exists($endUsers, 'total') ? $endUsers->total() : $endUsers->count();
@endphp

<div class="space-y-7 sm:space-y-8">

    {{-- Header --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="workspace-kicker">{{ __('Relasi bisnis') }}</p>
            <h2 class="mt-1 text-2xl font-bold tracking-tight text-slate-950 sm:text-3xl">{{ __('Manajemen Pelanggan') }}</h2>
            <p class="mt-2 text-sm text-slate-500">{{ __('Simpan informasi pelanggan dan narahubung yang terhubung ke proyek.') }}</p>
        </div>

        <button type="button" onclick="openModal('createModal')" class="{{ $btnPrimary }}">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.5v15m7.5-7.5h-15"/>
            </svg>
            {{ __('Tambah pelanggan') }}
        </button>
    </div>

    {{-- Alert --}}
    @if (session('success'))
        <div id="flashAlert" class="flex items-center justify-between rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
            <span>{{ session('success') }}</span>
            <button type="button" onclick="this.parentElement.remove()" class="text-lg font-bold leading-none">&times;</button>
        </div>
    @endif
    @if (session('error'))
        <div class="rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">{{ session('error') }}</div>
    @endif

    {{-- Table Container --}}
        <div class="workspace-panel overflow-hidden">
        <div class="flex flex-col gap-3 border-b border-slate-100 bg-white p-4 sm:flex-row sm:items-center sm:justify-between sm:p-5">
            <div class="relative w-full sm:max-w-md">
                <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path stroke-linecap="round" d="m16.5 16.5 4 4"/></svg>
                <input type="search" id="search" placeholder="{{ __('Cari nama, industri, atau kota...') }}" class="workspace-input pl-10">
            </div>
            <span class="text-xs font-medium text-slate-500">{{ $total }} customer terdaftar</span>
        </div>

        {{-- Desktop: Tabel --}}
        <div class="hidden overflow-x-auto md:block">
            <table class="workspace-table w-full text-left text-sm">
                <thead class="bg-slate-50 text-xs uppercase text-slate-500">
                    <tr>
                        <th class="px-6 py-3 font-semibold">{{ __('Nama') }}</th>
                        <th class="px-6 py-3 font-semibold">{{ __('Industri') }}</th>
                        <th class="px-6 py-3 font-semibold">{{ __('Narahubung') }}</th>
                        <th class="px-6 py-3 font-semibold">{{ __('Kota') }}</th>
                        <th class="px-6 py-3 text-right font-semibold">{{ __('Aksi') }}</th>
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
                                        class="{{ $btnSmall }} action-btn--detail">
                                        {{ __('Detail') }}
                                    </button>
                                    <button type="button" onclick="openEdit(this)"
                                        class="{{ $btnSmall }} action-btn--secondary">
                                        {{ __('Edit') }}
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-slate-500">Belum ada pelanggan.</td>
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
                                <span class="text-slate-500">Narahubung:</span>
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
                            class="action-btn action-btn--detail flex-1 px-3 py-2 text-sm">
                            {{ __('Detail') }}
                        </button>
                        <button type="button" onclick="openEdit(this)"
                            class="action-btn action-btn--secondary flex-1 px-3 py-2 text-sm">
                            {{ __('Edit') }}
                        </button>
                    </div>
                </div>
            @empty
                <div class="rounded-lg border border-dashed border-slate-300 bg-slate-50 px-6 py-12 text-center text-slate-500">
                    <p class="font-medium">Belum ada pelanggan</p>
                    <p class="mt-1 text-sm">Klik tombol "Tambah pelanggan" untuk menambahkan pelanggan baru</p>
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
                <h3 class="text-lg font-semibold text-slate-900">{{ __('Tambah pelanggan') }}</h3>
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
                <button type="button" onclick="closeModal('createModal')" class="{{ $btnSecondary }}">{{ __('Batal') }}</button>
                <button type="submit" class="{{ $btnPrimary }}">{{ __('Simpan') }}</button>
            </div>
        </form>
    </div>
</div>

{{-- ================= DETAIL MODAL ================= --}}
<div id="detailModal" role="dialog" aria-modal="true" aria-labelledby="endUserDetailTitle" class="modal fixed inset-0 z-50 hidden items-center justify-center bg-slate-950/60 p-4 backdrop-blur-sm">
    <div class="flex max-h-[90vh] w-full max-w-2xl flex-col overflow-hidden rounded-3xl bg-white shadow-2xl ring-1 ring-black/5">
        <div class="flex items-start justify-between border-b border-slate-100 bg-white px-6 py-5">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.16em] text-indigo-600">Ringkasan customer</p>
                <h3 id="endUserDetailTitle" class="mt-1 text-xl font-bold tracking-tight text-slate-900">Detail pelanggan</h3>
                <p class="mt-1 text-sm text-slate-500">Informasi customer yang terdaftar.</p>
            </div>
            <button type="button" onclick="closeModal('detailModal')" aria-label="Tutup detail pelanggan" class="rounded-xl p-2 text-2xl leading-none text-slate-400 transition hover:bg-slate-100 hover:text-slate-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500">&times;</button>
        </div>

        <div class="scrollbar-hidden grid grid-cols-1 gap-3 overflow-y-auto bg-slate-50/70 px-6 py-6 sm:grid-cols-2 sm:gap-4">
            @foreach ($fields as $field => $meta)
                <div class="{{ $loop->first ? 'sm:col-span-2' : '' }} rounded-2xl border border-slate-200/80 bg-white px-4 py-3.5 shadow-sm shadow-slate-200/40 transition-all duration-200 hover:-translate-y-0.5 hover:border-indigo-200 hover:shadow-md">
                    <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">{{ $meta['label'] }}</p>
                    <p id="detail_{{ $field }}" class="mt-1.5 break-words text-sm font-semibold text-slate-900">-</p>
                </div>
            @endforeach
        </div>

        <div class="flex justify-end border-t border-slate-100 bg-white px-6 py-4">
            <button type="button" onclick="closeModal('detailModal')" class="rounded-xl bg-slate-900 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-500 focus-visible:ring-offset-2">{{ __('Tutup') }}</button>
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
                <h3 class="text-lg font-semibold text-slate-900">Ubah pelanggan</h3>
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
                <button type="button" onclick="closeModal('editModal')" class="{{ $btnSecondary }}">{{ __('Batal') }}</button>
                <button type="submit" class="{{ $btnPrimary }}">{{ __('Simpan perubahan') }}</button>
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
        if (!modal || modal.classList.contains('hidden')) return;

        window.closeModalAnimated(modal, () => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            if (!document.querySelector('.modal.flex')) {
                document.body.classList.remove('overflow-hidden');
            }
        });
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
