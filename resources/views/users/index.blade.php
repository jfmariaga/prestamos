<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Usuarios') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Usuarios del sistema</h3>
                    <a href="{{ route('users.create') }}" class="inline-flex items-center justify-center px-4 py-2.5 bg-brand-700 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-brand-600">
                        Nuevo usuario
                    </a>
                </div>
                @livewire('users.user-index')
            </div>
        </div>
    </div>
</x-app-layout>
