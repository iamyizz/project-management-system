    {{-- ================= MODAL DETAIL END USER ================= --}}
    <div
        id="endUserDetailModal"
        role="dialog"
        aria-modal="true"
        aria-labelledby="endUserDetailTitle"
        class="modal fixed inset-0 z-50 hidden items-center justify-center bg-slate-950/60 p-4 backdrop-blur-sm"
    >
        <div class="scrollbar-hidden max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-3xl bg-white shadow-2xl ring-1 ring-black/5">
            <div class="sticky top-0 z-10 flex items-start justify-between border-b border-slate-100 bg-white/95 px-6 py-5 backdrop-blur">
                <h3
                    id="endUserDetailTitle"
                    class="text-xl font-bold tracking-tight text-slate-900"
                >
                    Detail pelanggan
                </h3>

                <button
                    type="button"
                    onclick="closeModal('endUserDetailModal')"
                    aria-label="Tutup detail end user"
                    class="rounded-xl p-2 text-2xl leading-none text-slate-400 transition hover:bg-slate-100 hover:text-slate-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500"
                >
                    &times;
                </button>
            </div>

            <dl class="grid grid-cols-1 gap-3 bg-slate-50/70 px-6 py-6 sm:grid-cols-2 sm:gap-4">
                @foreach ($endUserFields as $name => $fieldLabel)
                    <div class="{{ $name === 'nama' ? 'sm:col-span-2' : '' }} rounded-2xl border border-slate-200/80 bg-white px-4 py-3.5 shadow-sm shadow-slate-200/40 transition-all duration-200 hover:-translate-y-0.5 hover:border-indigo-200 hover:shadow-md">
                        <dt class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">
                            {{ $fieldLabel }}
                        </dt>

                        <dd
                            data-end-user-detail="{{ $name }}"
                            class="mt-1.5 whitespace-pre-wrap break-words text-sm font-semibold text-slate-900"
                        >-</dd>
                    </div>
                @endforeach
            </dl>

            <div class="flex justify-end border-t border-slate-100 px-6 py-4">
                <button
                    type="button"
                    onclick="closeModal('endUserDetailModal')"
                    class="rounded-xl bg-slate-900 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-500 focus-visible:ring-offset-2"
                >
                    Tutup
                </button>
            </div>
        </div>
    </div>

