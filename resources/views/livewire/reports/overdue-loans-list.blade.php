<div>
    <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3">
        <div class="text-sm text-red-800">Total interés pendiente en mora</div>
        <div class="text-xl font-semibold text-red-700">${{ number_format($total, 2) }}</div>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead>
                <tr class="text-left text-gray-500">
                    <th class="px-3 py-2">Deudor</th>
                    <th class="px-3 py-2">Teléfono</th>
                    <th class="px-3 py-2">Venció</th>
                    <th class="px-3 py-2">Días en mora</th>
                    <th class="px-3 py-2">Capital pendiente</th>
                    <th class="px-3 py-2">Interés pendiente</th>
                    <th class="px-3 py-2"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($loans as $loan)
                    <tr>
                        <td class="px-3 py-2">{{ $loan->borrower->name }}</td>
                        <td class="px-3 py-2">{{ $loan->borrower->phone }}</td>
                        <td class="px-3 py-2">{{ $loan->next_interest_due_date->format('d/m/Y') }}</td>
                        <td class="px-3 py-2 text-red-700 font-medium">{{ $loan->next_interest_due_date->diffInDays(now()) }}</td>
                        <td class="px-3 py-2">${{ number_format($loan->outstanding_principal, 2) }}</td>
                        <td class="px-3 py-2">${{ number_format($loan->outstanding_interest, 2) }}</td>
                        <td class="px-3 py-2">
                            <a href="{{ route('loans.show', $loan) }}" class="text-brand-600 hover:text-brand-800 hover:underline">Ver préstamo</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-3 py-4 text-center text-gray-500">No hay préstamos en mora.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $loans->links() }}
    </div>
</div>
