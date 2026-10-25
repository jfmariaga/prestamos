import './bootstrap';

import flatpickr from 'flatpickr';
import { Spanish } from 'flatpickr/dist/l10n/es.js';
import Chart from 'chart.js/auto';
import Swal from 'sweetalert2';

flatpickr.localize(Spanish);

window.flatpickr = flatpickr;
window.Chart = Chart;
window.Swal = Swal;

const toast = Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 3000,
    timerProgressBar: true,
    didOpen: (el) => {
        el.addEventListener('mouseenter', Swal.stopTimer);
        el.addEventListener('mouseleave', Swal.resumeTimer);
    },
});

window.notify = (message, icon = 'success') => toast.fire({ icon, title: message });

document.addEventListener('livewire:init', () => {
    Livewire.on('notify', ({ message, icon = 'success' }) => notify(message, icon));
    Livewire.on('profile-updated', () => notify('Perfil actualizado correctamente.'));
    Livewire.on('password-updated', () => notify('Contraseña actualizada correctamente.'));
});
