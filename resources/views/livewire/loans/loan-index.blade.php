<div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200 text-sm">
        <thead>
            <tr class="text-left text-gray-500">
                <th class="px-3 py-2">Deudor</th>
                <th class="px-3 py-2">Responsable</th>
                <th class="px-3 py-2">Teléfono</th>
                <th class="px-3 py-2">Capital inicial</th>
                <th class="px-3 py-2">Capital pendiente</th>
                <th class="px-3 py-2">Interés pendiente</th>
                <th class="px-3 py-2">Tasa</th>
                <th class="px-3 py-2">Próximo pago</th>
                <th class="px-3 py-2">Estado</th>
                <th class="px-3 py-2"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse ($loans as $loan)
                <tr>
                    <td class="px-3 py-2">{{ $loan->borrower->name }}</td>
                    <td class="px-3 py-2">{{ $loan->user?->name ?? '—' }}</td>
                    <td class="px-3 py-2">{{ $loan->borrower->phone }}</td>
                    <td class="px-3 py-2">${{ number_format($loan->principal_amount, 2) }}</td>
                    <td class="px-3 py-2">${{ number_format($loan->outstanding_principal, 2) }}</td>
                    <td class="px-3 py-2">${{ number_format($loan->outstanding_interest, 2) }}</td>
                    <td class="px-3 py-2">{{ rtrim(rtrim(number_format($loan->interest_rate, 2), '0'), '.') }}%</td>
                    <td class="px-3 py-2">{{ $loan->next_interest_due_date->format('d/m/Y') }}</td>
                    <td class="px-3 py-2">
                        <div class="flex items-center gap-1 flex-wrap">
                            @if ($loan->status === 'paid')
                                <span class="inline-flex items-center rounded-full bg-green-100 px-2 py-1 text-xs font-medium text-green-800">Pagado</span>
                            @else
                                <span class="inline-flex items-center rounded-full bg-yellow-100 px-2 py-1 text-xs font-medium text-yellow-800">Activo</span>
                            @endif
                            @if ($loan->hasBrokenCommitment())
                                <span class="inline-flex items-center rounded-full bg-red-100 px-2 py-1 text-xs font-medium text-red-800">Incumplió</span>
                            @endif
                        </div>
                    </td>
                    <td class="px-3 py-2">
                        <a href="{{ route('loans.show', $loan) }}" class="text-brand-600 hover:text-brand-800 hover:underline">Ver</a>
                        <a href="{{ route('loans.edit', $loan) }}" class="ms-2 text-brand-600 hover:text-brand-800 hover:underline">Editar</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" class="px-3 py-4 text-center text-gray-500">No hay préstamos registrados.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="mt-4">
        {{ $loans->links() }}
    </div>
</div>
