<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Préstamos en mora') }}
            </h2>
            <a href="{{ route('dashboard') }}" class="text-sm text-brand-600 hover:text-brand-800 hover:underline">&larr; Volver al dashboard</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                @livewire('reports.overdue-loans-list')
            </div>
        </div>
    </div>
</x-app-layout>
