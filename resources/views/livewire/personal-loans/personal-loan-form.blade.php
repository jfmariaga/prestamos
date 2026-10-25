<div class="bg-white shadow-sm sm:rounded-lg p-6 max-w-2xl">
    <form wire:submit="save" class="space-y-4">
        <div>
            <x-input-label for="person_name" value="Persona a la que se le prestó" />
            <x-text-input id="person_name" type="text" class="mt-1 block w-full" wire:model="person_name" />
            <x-input-error :messages="$errors->get('person_name')" class="mt-1" />
        </div>

        <div>
            <x-input-label for="amount" value="Valor prestado" />
            <x-money-input id="amount" class="mt-1" wire:model="amount" />
            <x-input-error :messages="$errors->get('amount')" class="mt-1" />
        </div>

        <div>
            <x-input-label for="loan_date" value="Fecha del préstamo" />
            <x-date-input id="loan_date" class="mt-1" wire:model="loan_date" />
            <x-input-error :messages="$errors->get('loan_date')" class="mt-1" />
        </div>

        <div>
            <x-input-label for="notes" value="Notas (opcional)" />
            <textarea id="notes" rows="3" wire:model="notes" class="mt-1 block w-full border-gray-300 text-base sm:text-sm focus:border-brand-500 focus:ring-brand-500 rounded-md shadow-sm"></textarea>
            <x-input-error :messages="$errors->get('notes')" class="mt-1" />
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ $personalLoan ? 'Actualizar' : 'Guardar' }}</x-primary-button>
            <a href="{{ route('personal-loans.index') }}" class="text-sm text-gray-600 hover:underline">Cancelar</a>
        </div>
    </form>
</div>
