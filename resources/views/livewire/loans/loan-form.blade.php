<div class="bg-white shadow-sm sm:rounded-lg p-6 max-w-2xl">
    <form wire:submit="save" class="space-y-4">
        <div>
            <x-input-label for="borrower_phone" value="Teléfono del deudor" />
            <x-text-input id="borrower_phone" type="text" class="mt-1 block w-full" wire:model.live.debounce.500ms="borrower_phone" placeholder="Ej: 3001234567" />
            <x-input-error :messages="$errors->get('borrower_phone')" class="mt-1" />
            <p class="mt-1 text-xs text-gray-500">El teléfono identifica al cliente en el sistema. Si ya tiene préstamos previos, sus datos se autocompletan.</p>
        </div>

        @if ($existingBorrower)
            <div class="rounded-md bg-brand-50 border border-brand-100 px-3 py-2 text-sm text-brand-800">
                Cliente existente — {{ $existingBorrower->loans_count }} préstamo(s) previo(s) registrado(s).
            </div>
        @endif

        <div>
            <x-input-label for="borrower_name" value="Nombre del deudor" />
            <x-text-input id="borrower_name" type="text" class="mt-1 block w-full" wire:model="borrower_name" />
            <x-input-error :messages="$errors->get('borrower_name')" class="mt-1" />
        </div>

        <div>
            <x-input-label for="borrower_reference" value="Referencia" />
            <x-text-input id="borrower_reference" type="text" class="mt-1 block w-full" wire:model="borrower_reference" />
            <x-input-error :messages="$errors->get('borrower_reference')" class="mt-1" />
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <x-input-label for="principal_amount" value="Valor prestado" />
                <x-money-input id="principal_amount" class="mt-1" wire:model="principal_amount" />
                <x-input-error :messages="$errors->get('principal_amount')" class="mt-1" />
            </div>

            <div>
                <x-input-label for="interest_rate" :value="$has_commitment_date ? '% Interés mensual si incumple el plazo' : '% Interés mensual'" />
                <x-text-input id="interest_rate" type="number" step="0.01" class="mt-1 block w-full" wire:model="interest_rate" />
                <x-input-error :messages="$errors->get('interest_rate')" class="mt-1" />
            </div>
        </div>

        <div>
            <x-input-label for="loan_date" value="Fecha del préstamo" />
            <x-date-input id="loan_date" class="mt-1" wire:model="loan_date" />
            <x-input-error :messages="$errors->get('loan_date')" class="mt-1" />
        </div>

        <div class="border-t border-gray-200 pt-4">
            <label class="inline-flex items-center gap-2">
                <input type="checkbox" wire:model.live="has_commitment_date" class="rounded border-gray-300 text-brand-600 shadow-sm focus:ring-brand-500">
                <span class="text-sm font-medium text-gray-700">Es un préstamo con fecha de compromiso (plazo fijo)</span>
            </label>

            @if ($has_commitment_date)
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">
                    <div>
                        <x-input-label for="commitment_date" value="Fecha de compromiso de pago" />
                        <x-date-input id="commitment_date" class="mt-1" wire:model="commitment_date" />
                        <x-input-error :messages="$errors->get('commitment_date')" class="mt-1" />
                    </div>

                    <div>
                        <x-input-label for="agreed_interest_rate" value="% Interés pactado para el plazo" />
                        <x-text-input id="agreed_interest_rate" type="number" step="0.01" class="mt-1 block w-full" wire:model.live="agreed_interest_rate" />
                        <x-input-error :messages="$errors->get('agreed_interest_rate')" class="mt-1" />
                        @if (! is_null($this->computedAgreedInterestAmount()))
                            <p class="mt-1 text-xs text-gray-500">
                                Interés pactado calculado: <span class="font-medium text-gray-700">${{ $this->computedAgreedInterestAmount() }}</span>
                            </p>
                        @endif
                    </div>
                </div>

                <p class="mt-2 text-xs text-gray-500">
                    Si el deudor no paga en la fecha de compromiso, el préstamo pasa a cobrarse con el % de interés mensual indicado arriba, y queda marcado como incumplido.
                </p>
            @endif
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ $loan ? 'Actualizar préstamo' : 'Guardar préstamo' }}</x-primary-button>
            <a href="{{ $loan ? route('loans.show', $loan) : route('dashboard') }}" class="text-sm text-gray-600 hover:underline">Cancelar</a>
        </div>
    </form>
</div>
