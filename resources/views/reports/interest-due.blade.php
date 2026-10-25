<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Interés por cobrar que vence en el rango') }}
            </h2>
            <a href="{{ route('dashboard') }}" class="text-sm text-brand-600 hover:text-brand-800 hover:underline">&larr; Volver al dashboard</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <p class="text-sm text-gray-500 mb-4">
                    Del {{ \Illuminate\Support\Carbon::parse($start)->format('d/m/Y') }} al {{ \Illuminate\Support\Carbon::parse($end)->format('d/m/Y') }}
                </p>
                @livewire('reports.interest-due-list', ['start' => $start, 'end' => $end])
            </div>
        </div>
    </div>
</x-app-layout>
