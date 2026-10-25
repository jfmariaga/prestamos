import './bootstrap';

import flatpickr from 'flatpickr';
import { Spanish } from 'flatpickr/dist/l10n/es.js';
import Chart from 'chart.js/auto';

flatpickr.localize(Spanish);

window.flatpickr = flatpickr;
window.Chart = Chart;
