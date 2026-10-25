<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ __('Préstamos personales') }}
                </h2>
                <p class="text-sm text-gray-500 mt-1">Control aparte, sin intereses. No afecta la cartera de préstamos ni el dashboard.</p>
            </div>
            <a href="{{ route('personal-loans.create') }}" class="inline-flex items-center justify-center px-4 py-2.5 bg-brand-700 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-brand-600">
                Nuevo préstamo personal
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                @livewire('personal-loans.personal-loan-index')
            </div>
        </div>
    </div>
</x-app-layout>
