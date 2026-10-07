<x-admin-layout title="Manage Users">
    @php
        $input = 'w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20';
        $label = 'mb-1.5 block text-sm font-medium text-slate-700';
        $formType = old('form_type');
    @endphp

    <div class="space-y-6">

        {{-- Header --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-2xl font-bold text-slate-900">User Management</h2>
                <p class="mt-1 text-sm text-slate-500">
                    Total {{ method_exists($users, 'total') ? $users->total() : $users->count() }} user terdaftar
                </p>
            </div>
            <button type="button" onclick="openModal('createModal')"
                    class="inline-flex items-center justify-center gap-2 rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Tambah User
            </button>
        </div>

        {{-- Alert sukses --}}
        @if (session('success'))
            <div id="flash" class="flex items-center justify-between rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                <span>{{ session('success') }}</span>
                <button type="button" onclick="this.parentElement.remove()" class="font-bold text-emerald-600 hover:text-emerald-800">&times;</button>
            </div>
        @endif

        {{-- Search --}}
        <div class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
            <div class="border-b border-slate-200 p-4">
                <div class="relative max-w-sm">
                    <svg class="pointer-events-none absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                    </svg>
                    <input type="text" id="search" placeholder="Cari nama atau email..." class="{{ $input }} pl-10">
                </div>
            </div>

            {{-- Desktop: Tabel --}}
            <div class="hidden overflow-x-auto md:block">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 text-xs uppercase tracking-wider text-slate-500">
                        <tr>
                            <th class="px-6 py-3 font-semibold">User</th>
                            <th class="px-6 py-3 font-semibold">Role</th>
                            <th class="px-6 py-3 font-semibold">Status</th>
                            <th class="px-6 py-3 text-right font-semibold">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="userTable" class="divide-y divide-slate-100">
                        @forelse ($users as $user)
                            <tr class="transition hover:bg-slate-50">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-indigo-100 font-semibold text-indigo-700">
                                            {{ strtoupper(substr($user->nama, 0, 1)) }}
                                        </div>
                                        <div class="min-w-0">
                                            <p class="truncate font-medium text-slate-900">{{ $user->nama }}</p>
                                            <p class="truncate text-slate-500">{{ $user->email }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $user->role === 'admin' ? 'bg-violet-50 text-violet-700' : 'bg-slate-100 text-slate-700' }}">
                                        {{ ucfirst($user->role) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    @if ($user->is_active)
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700">
                                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span> Aktif
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600">
                                            <span class="h-1.5 w-1.5 rounded-full bg-slate-400"></span> Nonaktif
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <button type="button"
                                            onclick="openEdit(this)"
                                            data-url="{{ route('admin.users.update', $user->id) }}"
                                            data-id="{{ $user->id }}"
                                            data-nama="{{ $user->nama }}"
                                            data-email="{{ $user->email }}"
                                            data-role="{{ $user->role }}"
                                            data-active="{{ $user->is_active ? 1 : 0 }}"
                                            class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 px-3 py-1.5 text-sm font-medium text-slate-700 transition hover:border-indigo-300 hover:bg-indigo-50 hover:text-indigo-700">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z" />
                                        </svg>
                                        Edit
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-12 text-center text-slate-500">Belum ada user.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Mobile: Card View --}}
            <div id="userCards" class="space-y-4 p-4 md:hidden">
                @forelse ($users as $user)
                    <div class="rounded-lg border border-slate-200 bg-slate-50 p-4 transition hover:shadow-md">
                        {{-- Header Card --}}
                        <div class="flex items-start justify-between gap-3 mb-4">
                            <div class="flex items-center gap-3 flex-1 min-w-0">
                                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-indigo-100 font-semibold text-indigo-700">
                                    {{ strtoupper(substr($user->nama, 0, 1)) }}
                                </div>
                                <div class="min-w-0">
                                    <p class="truncate font-semibold text-slate-900">{{ $user->nama }}</p>
                                    <p class="truncate text-sm text-slate-600">{{ $user->email }}</p>
                                </div>
                            </div>
                        </div>

                        {{-- Role & Status --}}
                        <div class="mb-4 flex flex-wrap gap-2">
                            <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $user->role === 'admin' ? 'bg-violet-50 text-violet-700' : 'bg-slate-100 text-slate-700' }}">
                                {{ ucfirst($user->role) }}
                            </span>
                            @if ($user->is_active)
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700">
                                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span> Aktif
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600">
                                    <span class="h-1.5 w-1.5 rounded-full bg-slate-400"></span> Nonaktif
                                </span>
                            @endif
                        </div>

                        {{-- Edit Button --}}
                        <button type="button"
                                onclick="openEdit(this)"
                                data-url="{{ route('admin.users.update', $user->id) }}"
                                data-id="{{ $user->id }}"
                                data-nama="{{ $user->nama }}"
                                data-email="{{ $user->email }}"
                                data-role="{{ $user->role }}"
                                data-active="{{ $user->is_active ? 1 : 0 }}"
                                class="w-full inline-flex items-center justify-center gap-2 rounded-lg border border-indigo-300 bg-indigo-50 px-4 py-2 text-sm font-medium text-indigo-700 transition hover:bg-indigo-100">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z" />
                            </svg>
                            Edit User
                        </button>
                    </div>
                @empty
                    <div class="rounded-lg border border-dashed border-slate-300 bg-slate-50 px-6 py-12 text-center text-slate-500">
                        <p class="font-medium">Belum ada user</p>
                        <p class="mt-1 text-sm">Klik tombol "Tambah User" untuk menambahkan user baru</p>
                    </div>
                @endforelse
            </div>

            @if (method_exists($users, 'links'))
                <div class="border-t border-slate-200 px-6 py-4">{{ $users->links() }}</div>
            @endif
        </div>
    </div>

    {{-- ================= MODAL TAMBAH ================= --}}
    <div id="createModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/50 p-4" onclick="if (event.target === this) closeModal('createModal')">
        <div class="w-full max-w-lg rounded-2xl bg-white shadow-xl">
            <div class="flex items-center justify-between border-b border-slate-200 px-6 py-4">
                <h3 class="text-lg font-semibold text-slate-900">Tambah User</h3>
                <button type="button" onclick="closeModal('createModal')" class="text-2xl leading-none text-slate-400 hover:text-slate-600">&times;</button>
            </div>

            <form method="POST" action="{{ route('admin.users.store') }}">
                @csrf
                <input type="hidden" name="form_type" value="create">

                <div class="space-y-4 px-6 py-5">
                    @if ($errors->any() && $formType === 'create')
                        <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                            <ul class="list-inside list-disc space-y-1">
                                @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach
                            </ul>
                        </div>
                    @endif

                    <div>
                        <label class="{{ $label }}">Nama</label>
                        <input type="text" name="nama" value="{{ $formType === 'create' ? old('nama') : '' }}" placeholder="Nama lengkap" class="{{ $input }}" required>
                    </div>
                    <div>
                        <label class="{{ $label }}">Email</label>
                        <input type="email" name="email" value="{{ $formType === 'create' ? old('email') : '' }}" placeholder="nama@email.com" class="{{ $input }}" required>
                    </div>
                    <div>
                        <label class="{{ $label }}">Password</label>
                        <input type="password" name="password" placeholder="Minimal 8 karakter" class="{{ $input }}" required>
                    </div>
                    <div>
                        <label class="{{ $label }}">Role</label>
                        <select name="role" class="{{ $input }}">
                            <option value="user" @selected($formType === 'create' && old('role') === 'user')>User</option>
                            <option value="admin" @selected($formType === 'create' && old('role') === 'admin')>Admin</option>
                        </select>
                    </div>
                </div>

                <div class="flex justify-end gap-3 rounded-b-2xl border-t border-slate-200 bg-slate-50 px-6 py-4">
                    <button type="button" onclick="closeModal('createModal')" class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100">Batal</button>
                    <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    {{-- ================= MODAL EDIT ================= --}}
    <div id="editModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/50 p-4" onclick="if (event.target === this) closeModal('editModal')">
        <div class="w-full max-w-lg rounded-2xl bg-white shadow-xl">
            <div class="flex items-center justify-between border-b border-slate-200 px-6 py-4">
                <h3 class="text-lg font-semibold text-slate-900">Edit User</h3>
                <button type="button" onclick="closeModal('editModal')" class="text-2xl leading-none text-slate-400 hover:text-slate-600">&times;</button>
            </div>

            <form id="editForm" method="POST"
                  action="{{ $formType === 'edit' && old('user_id') ? route('admin.users.update', old('user_id')) : '#' }}">
                @csrf
                @method('PUT')
                <input type="hidden" name="form_type" value="edit">
                <input type="hidden" name="user_id" id="edit_id" value="{{ $formType === 'edit' ? old('user_id') : '' }}">

                <div class="space-y-4 px-6 py-5">
                    @if ($errors->any() && $formType === 'edit')
                        <div id="editErrors" class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                            <ul class="list-inside list-disc space-y-1">
                                @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach
                            </ul>
                        </div>
                    @endif

                    <div>
                        <label class="{{ $label }}">Nama</label>
                        <input type="text" name="nama" id="edit_nama" value="{{ $formType === 'edit' ? old('nama') : '' }}" class="{{ $input }}" required>
                    </div>
                    <div>
                        <label class="{{ $label }}">Email</label>
                        <input type="email" name="email" id="edit_email" value="{{ $formType === 'edit' ? old('email') : '' }}" class="{{ $input }}" required>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="{{ $label }}">Role</label>
                            <select name="role" id="edit_role" class="{{ $input }}">
                                <option value="user" @selected($formType === 'edit' && old('role') === 'user')>User</option>
                                <option value="admin" @selected($formType === 'edit' && old('role') === 'admin')>Admin</option>
                            </select>
                        </div>
                        <div>
                            <label class="{{ $label }}">Status</label>
                            <select name="is_active" id="edit_active" class="{{ $input }}">
                                <option value="1" @selected($formType === 'edit' && old('is_active') === '1')>Aktif</option>
                                <option value="0" @selected($formType === 'edit' && old('is_active') === '0')>Nonaktif</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="{{ $label }}">Password Baru</label>
                        <input type="password" name="password" placeholder="Kosongkan kalau nggak mau ganti" class="{{ $input }}">
                    </div>
                </div>

                <div class="flex justify-end gap-3 rounded-b-2xl border-t border-slate-200 bg-slate-50 px-6 py-4">
                    <button type="button" onclick="closeModal('editModal')" class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100">Batal</button>
                    <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">Update</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openModal(id) {
            const m = document.getElementById(id);
            m.classList.remove('hidden');
            m.classList.add('flex');
            document.body.classList.add('overflow-hidden');
        }

        function closeModal(id) {
            const m = document.getElementById(id);
            m.classList.add('hidden');
            m.classList.remove('flex');
            document.body.classList.remove('overflow-hidden');
        }

        function openEdit(btn) {
            const d = btn.dataset;
            document.getElementById('editForm').action = d.url;
            document.getElementById('edit_id').value = d.id;
            document.getElementById('edit_nama').value = d.nama;
            document.getElementById('edit_email').value = d.email;
            document.getElementById('edit_role').value = d.role;
            document.getElementById('edit_active').value = d.active;
            document.getElementById('editErrors')?.remove();
            openModal('editModal');
        }

        // Tutup modal pakai tombol Esc
        document.addEventListener('keydown', e => {
            if (e.key === 'Escape') {
                closeModal('createModal');
                closeModal('editModal');
            }
        });

        // Pencarian di tabel dan card
        document.getElementById('search').addEventListener('input', function () {
            const q = this.value.toLowerCase();

            // Cari di tabel (desktop)
            document.querySelectorAll('#userTable tr').forEach(row => {
                row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
            });

            // Cari di card (mobile)
            document.querySelectorAll('#userCards > div').forEach(card => {
                card.style.display = card.textContent.toLowerCase().includes(q) ? '' : 'none';
            });
        });

        // Kalau validasi gagal, buka lagi modal yang tadi dipakai
        @if ($errors->any())
            openModal('{{ $formType === 'edit' ? 'editModal' : 'createModal' }}');
        @endif
    </script>
</x-admin-layout>
