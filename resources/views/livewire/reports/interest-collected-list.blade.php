<div>
    <div class="mb-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3">
        <div class="text-sm text-green-800">Total interés cobrado en el rango</div>
        <div class="text-xl font-semibold text-green-700">${{ number_format($total, 2) }}</div>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead>
                <tr class="text-left text-gray-500">
                    <th class="px-3 py-2">Fecha</th>
                    <th class="px-3 py-2">Deudor</th>
                    <th class="px-3 py-2">Teléfono</th>
                    <th class="px-3 py-2">Interés cobrado</th>
                    <th class="px-3 py-2"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($payments as $payment)
                    <tr>
                        <td class="px-3 py-2">{{ $payment->paid_at->format('d/m/Y') }}</td>
                        <td class="px-3 py-2">{{ $payment->loan->borrower->name }}</td>
                        <td class="px-3 py-2">{{ $payment->loan->borrower->phone }}</td>
                        <td class="px-3 py-2">${{ number_format($payment->interest_amount, 2) }}</td>
                        <td class="px-3 py-2">
                            <a href="{{ route('loans.show', $payment->loan) }}" class="text-brand-600 hover:text-brand-800 hover:underline">Ver préstamo</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-3 py-4 text-center text-gray-500">No hay interés cobrado en este rango.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $payments->links() }}
    </div>
</div>
