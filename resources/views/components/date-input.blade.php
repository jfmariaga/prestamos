@props(['disabled' => false])

@php
    $wireModel = $attributes->wire('model');
@endphp

<div
    wire:ignore
    x-data="{
        value: @entangle($wireModel),
        picker: null,
        init() {
            this.picker = flatpickr(this.$refs.input, {
                dateFormat: 'Y-m-d',
                altInput: true,
                altFormat: 'd/m/Y',
                allowInput: true,
                disableMobile: true,
                defaultDate: this.value || null,
                onChange: (selectedDates, dateStr) => { this.value = dateStr; },
            });
        },
    }"
>
    <input
        type="text"
        x-ref="input"
        autocomplete="off"
        @disabled($disabled)
        {{ $attributes->whereDoesntStartWith('wire:model')->merge(['class' => 'border-gray-300 text-base sm:text-sm focus:border-brand-500 focus:ring-brand-500 rounded-md shadow-sm w-full']) }}
    >
</div>
