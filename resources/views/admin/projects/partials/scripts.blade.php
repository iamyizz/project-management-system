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
            const projectValueInput = form.querySelector('[name="project_value"]');
            const internalCostInput = form.querySelector('[name="internal_cost"]');
            const marginInput = form.querySelector('[name="margin"]');
            const percentageInput = form.querySelector('[name="percentage"]');

            if (!projectValueInput || !internalCostInput || !marginInput || !percentageInput) return;

            const projectValue = parseFloat(projectValueInput.value) || 0;
            const internalCost = parseFloat(internalCostInput.value) || 0;
            const margin = projectValue - internalCost;
            const percentage = projectValue > 0 ? (margin / projectValue) * 100 : 0;

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

        const createForm = document.getElementById('createForm');
        createForm?.addEventListener('submit', (event) => {
            if (createForm.dataset.submitting === 'true') {
                event.preventDefault();
                return;
            }

            createForm.dataset.submitting = 'true';
            const submitButton = createForm.querySelector('[data-form-submit]');
            submitButton.disabled = true;
            submitButton.textContent = 'Menyimpan...';
            submitButton.classList.add('cursor-wait', 'opacity-70');
        });

        // Setup auto-calculate listener untuk form
        document.querySelectorAll('form').forEach(form => {
            const projectValueInput = form.querySelector('[name="project_value"]');
            const internalCostInput = form.querySelector('[name="internal_cost"]');

            if (projectValueInput && internalCostInput) {
                projectValueInput.addEventListener('input', () => calculateFinance(form));
                projectValueInput.addEventListener('change', () => calculateFinance(form));
                internalCostInput.addEventListener('input', () => calculateFinance(form));
                internalCostInput.addEventListener('change', () => calculateFinance(form));

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
            if (!modal || modal.classList.contains('hidden')) return;

            window.closeModalAnimated(modal, () => {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
                if (!document.querySelector('.modal.flex')) {
                    document.body.classList.remove('overflow-hidden');
                }
            });
        }

        function rowData(btn) {
            return JSON.parse(btn.closest('.data-row').dataset.project);
        }

        function openDetail(btn) {
            const data = rowData(btn);
            const { labels } = data;
            document.querySelectorAll('#detailModal [data-detail]').forEach((el) => {
                el.textContent = labels[el.dataset.detail] || '-';
            });
            renderStatusHistory(data.history || []);
            renderProjectActivities(data.activities || []);
            openModal('detailModal');
        }

        function renderStatusHistory(history) {
            const list = document.querySelector('#detailModal [data-status-history]');
            list.replaceChildren();

            if (!history.length) {
                const empty = document.createElement('li');
                empty.className = 'text-sm text-slate-500';
                empty.textContent = 'Belum ada riwayat status.';
                list.append(empty);
                return;
            }

            history.forEach((item) => {
                const entry = document.createElement('li');
                entry.className = 'rounded-xl border border-slate-200 bg-slate-50 px-4 py-3';
                const change = document.createElement('p');
                change.className = 'text-sm font-medium text-slate-800';
                change.textContent = item.from ? `${item.from} → ${item.to}` : `Proyek dibuat dengan status ${item.to}`;
                const meta = document.createElement('p');
                meta.className = 'mt-1 text-xs text-slate-500';
                meta.textContent = `${item.actor} · ${item.date}`;
                entry.append(change, meta);
                list.append(entry);
            });
        }

        function renderProjectActivities(activities) {
            const list = document.querySelector('#detailModal [data-project-activities]');
            list.replaceChildren();

            if (!activities.length) {
                const empty = document.createElement('li');
                empty.className = 'text-sm text-slate-500';
                empty.textContent = 'Belum ada perubahan detail proyek.';
                list.append(empty);
                return;
            }

            activities.forEach((item) => {
                const entry = document.createElement('li');
                entry.className = 'rounded-xl border border-slate-200 bg-slate-50 px-4 py-3';
                const change = document.createElement('p');
                change.className = 'text-sm font-medium text-slate-800';
                change.textContent = `${item.field}: ${item.old ?? '-'} → ${item.new ?? '-'}`;
                const meta = document.createElement('p');
                meta.className = 'mt-1 text-xs text-slate-500';
                meta.textContent = `${item.actor} · ${item.date}`;
                entry.append(change, meta);
                list.append(entry);
            });
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
                const projectValueInput = form.querySelector('[name="project_value"]');
                const internalCostInput = form.querySelector('[name="internal_cost"]');

                if (projectValueInput && internalCostInput) {
                    projectValueInput.addEventListener('input', () => calculateFinance(form));
                    projectValueInput.addEventListener('change', () => calculateFinance(form));
                    internalCostInput.addEventListener('input', () => calculateFinance(form));
                    internalCostInput.addEventListener('change', () => calculateFinance(form));

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
