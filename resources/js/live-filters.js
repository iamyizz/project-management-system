const liveFilterForms = document.querySelectorAll('[data-live-filter]');

liveFilterForms.forEach((form) => {
    const resultsSelector = form.dataset.resultsTarget;
    const status = form.parentElement.querySelector('[data-filter-status]');
    let results = document.querySelector(resultsSelector);
    let debounceTimer;
    let activeRequest;
    let requestVersion = 0;

    if (!results) return;

    const updateFormFromUrl = (url) => {
        const params = new URL(url).searchParams;

        const fields = [
            ...form.querySelectorAll('[name]'),
            ...document.querySelectorAll(`[form="${form.id}"][name]`),
        ];

        fields.forEach((field) => {
            if (field.type !== 'submit' && field.type !== 'button') {
                field.value = params.get(field.name) ?? '';
            }
        });

        form.querySelectorAll('[data-flatpickr]').forEach((field) => {
            field._flatpickr?.setDate(field.value || null, false);
        });
    };

    const loadResults = async (url, syncForm = true, scrollToTable = false) => {
        activeRequest?.abort();
        activeRequest = new AbortController();
        const currentVersion = ++requestVersion;

        results.setAttribute('aria-busy', 'true');
        results.style.opacity = '0.55';
        if (status) status.textContent = 'Memuat hasil filter';

        try {
            const response = await fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    Accept: 'text/html',
                },
                signal: activeRequest.signal,
            });

            if (!response.ok) throw new Error(`Filter request failed: ${response.status}`);

            const documentFromResponse = new DOMParser().parseFromString(await response.text(), 'text/html');
            const updatedResults = documentFromResponse.querySelector(resultsSelector);

            if (!updatedResults) throw new Error('Filter response is missing its results container.');

            results.replaceWith(updatedResults);
            results = updatedResults;
            history.replaceState({}, '', response.url);

            document.querySelectorAll('[data-report-export]').forEach((link) => {
                const exportUrl = new URL(link.href, window.location.href);
                exportUrl.search = new URL(response.url).search;
                link.href = exportUrl;
            });

            if (syncForm) updateFormFromUrl(response.url);
            if (scrollToTable) {
                updatedResults.querySelector('[data-report-table]')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
            if (status) status.textContent = 'Hasil filter diperbarui';
        } catch (error) {
            if (error.name !== 'AbortError' && currentVersion === requestVersion) {
                console.error(error);
                if (status) status.textContent = 'Filter gagal dimuat. Coba lagi.';
            }
        } finally {
            if (currentVersion === requestVersion) {
                results.removeAttribute('aria-busy');
                results.style.opacity = '';
            }
        }
    };

    const urlFromForm = () => {
        const url = new URL(form.action, window.location.href);

        new FormData(form).forEach((value, name) => {
            const normalizedValue = String(value).trim();
            if (normalizedValue) {
                url.searchParams.set(name, normalizedValue);
            } else {
                url.searchParams.delete(name);
            }
        });

        url.searchParams.delete('page');
        return url;
    };

    form.addEventListener('submit', (event) => {
        event.preventDefault();
        loadResults(urlFromForm());
    });

    form.addEventListener('input', (event) => {
        if (event.target.matches('input[type="search"], input[type="text"]')) {
            window.clearTimeout(debounceTimer);
            debounceTimer = window.setTimeout(() => loadResults(urlFromForm()), 250);
        }
    });

    form.addEventListener('change', () => {
        window.clearTimeout(debounceTimer);
        loadResults(urlFromForm());
    });

    form.addEventListener('click', (event) => {
        const rangePreset = event.target.closest('[data-range-preset]');
        if (rangePreset) {
            const now = new Date();
            let start;
            let end;

            if (rangePreset.dataset.rangePreset === 'month') {
                start = new Date(now.getFullYear(), now.getMonth(), 1);
                end = new Date(now.getFullYear(), now.getMonth() + 1, 0);
            } else if (rangePreset.dataset.rangePreset === 'quarter') {
                const firstQuarterMonth = Math.floor(now.getMonth() / 3) * 3;
                start = new Date(now.getFullYear(), firstQuarterMonth, 1);
                end = new Date(now.getFullYear(), firstQuarterMonth + 3, 0);
            } else {
                start = new Date(now.getFullYear(), 0, 1);
                end = new Date(now.getFullYear(), 11, 31);
            }

            const toDateValue = (date) => {
                const year = date.getFullYear();
                const month = String(date.getMonth() + 1).padStart(2, '0');
                const day = String(date.getDate()).padStart(2, '0');
                return `${year}-${month}-${day}`;
            };

            const dateFrom = form.querySelector('[name="date_from"]');
            const dateTo = form.querySelector('[name="date_to"]');
            dateFrom._flatpickr?.setDate(toDateValue(start), false);
            dateTo._flatpickr?.setDate(toDateValue(end), false);
            if (!dateFrom._flatpickr) dateFrom.value = toDateValue(start);
            if (!dateTo._flatpickr) dateTo.value = toDateValue(end);

            loadResults(urlFromForm());
            return;
        }

        const resetLink = event.target.closest('[data-filter-reset]');
        if (!resetLink) return;

        event.preventDefault();
        form.reset();
        loadResults(new URL(resetLink.href, window.location.href));
    });

    document.addEventListener('click', (event) => {
        const deadlineCard = event.target.closest(`${resultsSelector} [data-deadline-scope]`);
        if (deadlineCard) {
            const scopeInput = form.querySelector('[name="deadline_scope"]');
            const selectedScope = deadlineCard.dataset.deadlineScope;

            if (form.hasAttribute('data-reset-status-on-scope')) {
                const statusInput = form.querySelector('[name="status"]');
                if (statusInput) statusInput.value = '';
            }

            scopeInput.value = selectedScope === 'all' || scopeInput.value === selectedScope ? '' : selectedScope;
            loadResults(urlFromForm(), true, true);
            return;
        }

        const pageLink = event.target.closest(`${resultsSelector} nav[role="navigation"] a[href]`);
        if (!pageLink) return;

        event.preventDefault();
        loadResults(new URL(pageLink.href, window.location.href));
    });

    document.addEventListener('input', (event) => {
        if (event.target.form === form && event.target.matches('input[type="search"], input[type="text"]') && !form.contains(event.target)) {
            window.clearTimeout(debounceTimer);
            debounceTimer = window.setTimeout(() => loadResults(urlFromForm()), 250);
        }
    });
});
