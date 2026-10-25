<div>
    <div class="mb-4 rounded-lg border border-brand-200 bg-brand-50 px-4 py-3">
        <div class="text-sm text-brand-800">Total interés por cobrar que vence en el rango</div>
        <div class="text-xl font-semibold text-brand-700">${{ number_format($total, 2) }}</div>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead>
                <tr class="text-left text-gray-500">
                    <th class="px-3 py-2">Vence</th>
                    <th class="px-3 py-2">Deudor</th>
                    <th class="px-3 py-2">Teléfono</th>
                    <th class="px-3 py-2">Interés pendiente</th>
                    <th class="px-3 py-2"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($loans as $loan)
                    <tr>
                        <td class="px-3 py-2">{{ $loan->next_interest_due_date->format('d/m/Y') }}</td>
                        <td class="px-3 py-2">{{ $loan->borrower->name }}</td>
                        <td class="px-3 py-2">{{ $loan->borrower->phone }}</td>
                        <td class="px-3 py-2">${{ number_format($loan->outstanding_interest, 2) }}</td>
                        <td class="px-3 py-2">
                            <a href="{{ route('loans.show', $loan) }}" class="text-brand-600 hover:text-brand-800 hover:underline">Ver préstamo</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-3 py-4 text-center text-gray-500">No hay interés por cobrar en este rango.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $loans->links() }}
    </div>
</div>
