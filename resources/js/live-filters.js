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

        form.querySelectorAll('[name]').forEach((field) => {
            if (field.type !== 'submit' && field.type !== 'button') {
                field.value = params.get(field.name) ?? '';
            }
        });
    };

    const loadResults = async (url, syncForm = true) => {
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

            if (syncForm) updateFormFromUrl(response.url);
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
        const resetLink = event.target.closest('[data-filter-reset]');
        if (!resetLink) return;

        event.preventDefault();
        form.reset();
        loadResults(new URL(resetLink.href, window.location.href));
    });

    document.addEventListener('click', (event) => {
        const pageLink = event.target.closest(`${resultsSelector} nav[role="navigation"] a[href]`);
        if (!pageLink) return;

        event.preventDefault();
        loadResults(new URL(pageLink.href, window.location.href));
    });
});
