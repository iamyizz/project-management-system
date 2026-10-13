

import Alpine from 'alpinejs';
import flatpickr from 'flatpickr';
import { Indonesian } from 'flatpickr/dist/l10n/id.js';
import 'flatpickr/dist/flatpickr.css';
import '../css/flatpickr-overrides.css';
import './live-filters.js';

window.Alpine = Alpine;

Alpine.start();

flatpickr('[data-flatpickr]', {
    altInput: true,
    altFormat: 'j M Y',
    dateFormat: 'Y-m-d',
    disableMobile: true,
    locale: Indonesian,
    allowInput: false,
});
