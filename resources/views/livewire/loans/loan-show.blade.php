<div class="space-y-6">
    <div class="bg-white shadow-sm sm:rounded-lg p-6">
        <div class="flex items-center justify-between mb-4 flex-wrap gap-2">
            <h3 class="text-lg font-medium text-gray-900">{{ $loan->borrower->name }}</h3>
            <div class="flex items-center gap-2">
                @if ($loan->hasBrokenCommitment())
                    <span class="inline-flex items-center rounded-full bg-red-100 px-2 py-1 text-xs font-medium text-red-800">Incumplió acuerdo</span>
                @endif
                @if ($loan->status === 'paid')
                    <span class="inline-flex items-center rounded-full bg-green-100 px-2 py-1 text-xs font-medium text-green-800">Pagado</span>
                @else
                    <span class="inline-flex items-center rounded-full bg-yellow-100 px-2 py-1 text-xs font-medium text-yellow-800">Activo</span>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-sm">
            <div>
                <div class="text-gray-500">Teléfono</div>
                <div class="font-medium">{{ $loan->borrower->phone }}</div>
            </div>
            <div>
                <div class="text-gray-500">Referencia</div>
                <div class="font-medium">{{ $loan->borrower->reference }}</div>
            </div>
            <div>
                <div class="text-gray-500">Responsable</div>
                <div class="font-medium">{{ $loan->user?->name ?? '—' }}</div>
            </div>
            <div>
                <div class="text-gray-500">Capital inicial</div>
                <div class="font-medium">${{ number_format($loan->principal_amount, 2) }}</div>
            </div>
            <div>
                <div class="text-gray-500">Tasa de interés</div>
                <div class="font-medium">{{ rtrim(rtrim(number_format($loan->interest_rate, 2), '0'), '.') }}%</div>
            </div>
            <div>
                <div class="text-gray-500">Capital pendiente</div>
                <div class="font-medium">${{ number_format($loan->outstanding_principal, 2) }}</div>
            </div>
            <div>
                <div class="text-gray-500">Interés pendiente</div>
                <div class="font-medium">${{ number_format($loan->outstanding_interest, 2) }}</div>
            </div>
            <div>
                <div class="text-gray-500">Fecha del préstamo</div>
                <div class="font-medium">{{ $loan->loan_date->format('d/m/Y') }}</div>
            </div>
            <div>
                <div class="text-gray-500">{{ $loan->hasCommitmentDate() && ! $loan->hasBrokenCommitment() ? 'Fecha de compromiso' : 'Próximo pago de interés' }}</div>
                <div class="font-medium">{{ $loan->next_interest_due_date->format('d/m/Y') }}</div>
            </div>
            @if ($loan->hasCommitmentDate())
                <div>
                    <div class="text-gray-500">Interés pactado para el plazo</div>
                    <div class="font-medium">${{ number_format($loan->agreed_interest_amount, 2) }}</div>
                </div>
            @endif
            @if ($loan->hasBrokenCommitment())
                <div>
                    <div class="text-gray-500">Incumplió el acuerdo</div>
                    <div class="font-medium text-red-700">{{ $loan->commitment_broken_at->format('d/m/Y') }}</div>
                </div>
            @endif
        </div>
    </div>

    @if ($loan->status !== 'paid')
        <div class="bg-white shadow-sm sm:rounded-lg p-6 max-w-lg">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Registrar abono</h3>
            <form wire:submit="registerPayment" class="space-y-4">
                <div>
                    <x-input-label for="amount" value="Monto del abono" />
                    <x-text-input id="amount" type="number" step="0.01" class="mt-1 block w-full" wire:model="amount" />
                    <x-input-error :messages="$errors->get('amount')" class="mt-1" />
                </div>
                <p class="text-xs text-gray-500">El abono se aplica primero al interés pendiente (${{ number_format($loan->outstanding_interest, 2) }}) y el excedente al capital.</p>
                <x-primary-button>Registrar abono</x-primary-button>
            </form>
        </div>
    @endif

    <div class="bg-white shadow-sm sm:rounded-lg p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Historial de abonos</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead>
                    <tr class="text-left text-gray-500">
                        <th class="px-3 py-2">Fecha</th>
                        <th class="px-3 py-2">Monto total</th>
                        <th class="px-3 py-2">A interés</th>
                        <th class="px-3 py-2">A capital</th>
                        <th class="px-3 py-2">Capital restante</th>
                        <th class="px-3 py-2">Interés restante</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($payments as $payment)
                        <tr>
                            <td class="px-3 py-2">{{ $payment->paid_at->format('d/m/Y') }}</td>
                            <td class="px-3 py-2">${{ number_format($payment->total_amount, 2) }}</td>
                            <td class="px-3 py-2">${{ number_format($payment->interest_amount, 2) }}</td>
                            <td class="px-3 py-2">${{ number_format($payment->principal_amount, 2) }}</td>
                            <td class="px-3 py-2">${{ number_format($payment->resulting_outstanding_principal, 2) }}</td>
                            <td class="px-3 py-2">${{ number_format($payment->resulting_outstanding_interest, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-3 py-4 text-center text-gray-500">Aún no hay abonos registrados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if ($otherLoans->isNotEmpty())
        <div class="bg-white shadow-sm sm:rounded-lg p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Historial de este cliente ({{ $otherLoans->count() }} préstamo(s) más)</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead>
                        <tr class="text-left text-gray-500">
                            <th class="px-3 py-2">Fecha</th>
                            <th class="px-3 py-2">Capital inicial</th>
                            <th class="px-3 py-2">Estado</th>
                            <th class="px-3 py-2"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($otherLoans as $other)
                            <tr>
                                <td class="px-3 py-2">{{ $other->loan_date->format('d/m/Y') }}</td>
                                <td class="px-3 py-2">${{ number_format($other->principal_amount, 2) }}</td>
                                <td class="px-3 py-2">
                                    <div class="flex items-center gap-1 flex-wrap">
                                        @if ($other->status === 'paid')
                                            <span class="inline-flex items-center rounded-full bg-green-100 px-2 py-1 text-xs font-medium text-green-800">Pagado</span>
                                        @else
                                            <span class="inline-flex items-center rounded-full bg-yellow-100 px-2 py-1 text-xs font-medium text-yellow-800">Activo</span>
                                        @endif
                                        @if ($other->hasBrokenCommitment())
                                            <span class="inline-flex items-center rounded-full bg-red-100 px-2 py-1 text-xs font-medium text-red-800">Incumplió</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-3 py-2">
                                    <a href="{{ route('loans.show', $other) }}" class="text-brand-600 hover:text-brand-800 hover:underline">Ver</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
