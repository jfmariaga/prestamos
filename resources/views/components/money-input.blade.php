@props(['disabled' => false])

@php
    $wireModel = $attributes->wire('model');
@endphp

<div
    wire:ignore
    x-data="{
        raw: @entangle($wireModel),
        display: '',
        format(value) {
            if (value === '' || value === null || value === undefined) return '';
            let [intPart, decPart] = String(value).split('.');
            intPart = intPart.replace(/\D/g, '');
            let formatted = intPart === '' ? '' : Number(intPart).toLocaleString('es-CO');
            return decPart !== undefined ? formatted + ',' + decPart : formatted;
        },
        onInput(e) {
            let clean = e.target.value.replace(/[^0-9,]/g, '');
            let commaIndex = clean.indexOf(',');
            let intPart = commaIndex === -1 ? clean : clean.slice(0, commaIndex);
            let decPart = commaIndex === -1 ? undefined : clean.slice(commaIndex + 1).replace(/,/g, '');
            intPart = intPart.replace(/^0+(?=\d)/, '');

            this.raw = decPart !== undefined ? (intPart || '0') + '.' + decPart : intPart;
            this.display = intPart === '' && decPart === undefined
                ? ''
                : (intPart === '' ? '0' : Number(intPart).toLocaleString('es-CO')) + (decPart !== undefined ? ',' + decPart : '');

            this.$nextTick(() => e.target.value = this.display);
        },
        init() {
            this.display = this.format(this.raw);
        },
    }"
>
    <input
        type="text"
        inputmode="decimal"
        x-model="display"
        x-on:input="onInput($event)"
        @disabled($disabled)
        {{ $attributes->whereDoesntStartWith('wire:model')->merge(['class' => 'border-gray-300 text-base sm:text-sm focus:border-brand-500 focus:ring-brand-500 rounded-md shadow-sm w-full']) }}
    >
</div>
