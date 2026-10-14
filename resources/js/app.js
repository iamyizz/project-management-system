

import Alpine from 'alpinejs';
import flatpickr from 'flatpickr';
import { Indonesian } from 'flatpickr/dist/l10n/id.js';
import 'flatpickr/dist/flatpickr.css';
import '../css/flatpickr-overrides.css';
import './live-filters.js';

window.Alpine = Alpine;

window.closeModalAnimated = (modal, onClosed) => {
    if (!modal || modal.dataset.modalClosing === 'true') return;

    if (modal.classList.contains('hidden')) {
        onClosed?.();
        return;
    }

    modal.dataset.modalClosing = 'true';
    modal.classList.add('modal-closing');

    const duration = window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 1 : 150;
    const easing = 'ease-in';
    const animations = [
        modal.animate([{ opacity: 1 }, { opacity: 0 }], { duration, easing, fill: 'both' }),
    ];
    const panel = modal.querySelector(':scope > :first-child');

    if (panel) {
        animations.push(panel.animate(
            [
                { opacity: 1, transform: 'translateY(0) scale(1)' },
                { opacity: 0, transform: 'translateY(6px) scale(0.99)' },
            ],
            { duration, easing, fill: 'both' },
        ));
    }

    Promise.all(animations.map((animation) => animation.finished.catch(() => undefined))).then(() => {
        animations.forEach((animation) => animation.cancel());
        delete modal.dataset.modalClosing;
        modal.classList.remove('modal-closing');
        onClosed?.();
    });
};

Alpine.start();

flatpickr('[data-flatpickr]', {
    altInput: true,
    altFormat: 'j M Y',
    dateFormat: 'Y-m-d',
    disableMobile: true,
    locale: Indonesian,
    allowInput: false,
});
