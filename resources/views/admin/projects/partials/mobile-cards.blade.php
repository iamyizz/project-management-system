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
                                            class="end-user-link group max-w-full"
                                        >
                                            <span class="end-user-link__label max-w-full truncate">{{ $project->endUser->nama }}</span>
                                            <svg class="end-user-link__icon" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m7 4 6 6-6 6"/></svg>
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
                                <span class="text-slate-500">Tenggat:</span>
                                <span class="font-medium {{ $overdue ? 'text-red-600' : 'text-slate-900' }}">
                                    {{ $formatDate($deadline) ?? '-' }}
                                    @if ($overdue)
                                        <span class="ml-1">(Terlambat)</span>
                                    @endif
                                </span>
                            </div>
                            @if ($project->project_value)
                                <div class="flex justify-between">
                                    <span class="text-slate-500">Nilai proyek:</span>
                                    <span class="font-medium text-slate-900">Rp {{ number_format($project->project_value, 2, ',', '.') }}</span>
                                </div>
                            @endif
                        </div>

                        {{-- Action Buttons --}}
                        <div class="flex gap-2">
                            <button type="button" onclick="openDetail(this)"
                                class="action-btn action-btn--detail flex-1 px-3 py-2 text-sm">
                                Detail
                            </button>
                            <button type="button" onclick="openEdit(this)"
                                class="action-btn action-btn--secondary flex-1 px-3 py-2 text-sm">
                                Ubah
                            </button>
                            <form method="POST" action="{{ route('admin.projects.destroy', $project) }}"
                                class="flex-1" onsubmit="return confirm('Hapus proyek ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="action-btn action-btn--danger w-full px-3 py-2 text-sm">
                                    Hapus
                                </button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="rounded-lg border border-dashed border-slate-300 bg-slate-50 px-6 py-12 text-center text-slate-500">
                        <p class="font-medium">Belum ada proyek</p>
                        <p class="mt-1 text-sm">Klik tombol "Tambah proyek" untuk menambahkan proyek baru</p>
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
    </div>

