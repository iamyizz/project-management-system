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
                        <p class="mt-1 text-sm text-slate-500">Informasi lengkap proyek yang dipilih.</p>
                    </div>
                </div>
                <button type="button" onclick="closeModal('detailModal')" aria-label="Tutup detail proyek" class="rounded-xl p-2 text-slate-400 transition hover:bg-slate-100 hover:text-slate-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500">&times;</button>
            </div>
            <dl class="grid grid-cols-1 gap-3 bg-slate-50/70 px-6 py-6 sm:grid-cols-2 sm:gap-4 sm:px-8">
                @foreach ($fields as $name => $field)
                    <div class="{{ !empty($field['span']) ? 'sm:col-span-2' : '' }} rounded-2xl border border-slate-200/80 bg-white px-4 py-3.5 shadow-sm shadow-slate-200/40 transition-all duration-200 hover:-translate-y-0.5 hover:border-indigo-200 hover:shadow-md">
                        <dt class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">{{ $field['label'] }}</dt>
                        <dd data-detail="{{ $name }}" class="mt-1.5 break-words text-sm font-semibold text-slate-900">-</dd>
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

