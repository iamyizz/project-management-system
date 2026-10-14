            {{-- DESKTOP: Tabel --}}
            <div class="hidden overflow-x-auto md:block">
                <table class="workspace-table w-full text-left text-sm">
                    <thead class="bg-slate-50 text-xs uppercase text-slate-500">
                        <tr>
                            <th class="px-6 py-3 font-semibold">No</th>
                            <th class="px-6 py-3 font-semibold">Pelanggan</th>
                            <th class="px-6 py-3 font-semibold">Nama proyek</th>
                            <th class="px-6 py-3 font-semibold">PIC</th>
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
                                    'history'    => $project->statusHistories->map(fn ($history) => [
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
                                            class="end-user-link group"
                                        >
                                            <span class="end-user-link__label">{{ $project->endUser->nama }}</span>
                                            <svg class="end-user-link__icon" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m7 4 6 6-6 6"/></svg>
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
                                        <span class="ml-1 rounded-full bg-red-100 px-2 py-0.5 text-xs font-medium text-red-700">Terlambat</span>
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
                                        <button type="button" onclick="openEdit(this)" class="{{ $btnSmall }}">Ubah</button>
                                        <form method="POST" action="{{ route('admin.projects.destroy', $project) }}"
                                            class="inline" onsubmit="return confirm('Hapus proyek ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rounded-lg border border-red-200 px-3 py-1.5 text-xs font-medium text-red-600 hover:bg-red-50">Hapus</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-10 text-center text-slate-500">Belum ada proyek.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

