<div>
    <div class="mb-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div class="max-w-sm w-full">
            <x-text-input
                type="text"
                class="w-full"
                placeholder="Buscar por persona..."
                wire:model.live.debounce.400ms="search"
            />
        </div>
        <div class="rounded-lg border border-gray-200 px-4 py-2 text-sm">
            <span class="text-gray-500">Total pendiente por devolver:</span>
            <span class="font-semibold text-gray-900">${{ number_format($totalPendiente, 2) }}</span>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead>
                <tr class="text-left text-gray-500">
                    <th class="px-3 py-2">Fecha</th>
                    <th class="px-3 py-2">Persona</th>
                    <th class="px-3 py-2">Valor prestado</th>
                    <th class="px-3 py-2">Notas</th>
                    <th class="px-3 py-2">Estado</th>
                    <th class="px-3 py-2"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($loans as $loan)
                    <tr>
                        <td class="px-3 py-2">{{ $loan->loan_date->format('d/m/Y') }}</td>
                        <td class="px-3 py-2">{{ $loan->person_name }}</td>
                        <td class="px-3 py-2">${{ number_format($loan->amount, 2) }}</td>
                        <td class="px-3 py-2 text-gray-500">{{ $loan->notes ?: '—' }}</td>
                        <td class="px-3 py-2">
                            <button
                                wire:click="toggleReturned({{ $loan->id }})"
                                @class([
                                    'inline-flex items-center rounded-full px-2 py-1 text-xs font-medium',
                                    'bg-green-100 text-green-800' => $loan->is_returned,
                                    'bg-yellow-100 text-yellow-800' => ! $loan->is_returned,
                                ])
                            >
                                {{ $loan->is_returned ? 'Devuelto' : 'Pendiente' }}
                            </button>
                        </td>
                        <td class="px-3 py-2">
                            <a href="{{ route('personal-loans.edit', $loan) }}" class="text-brand-600 hover:text-brand-800 hover:underline">Editar</a>
                            <button
                                type="button"
                                class="ms-2 text-red-600 hover:text-red-800 hover:underline"
                                x-on:click="
                                    Swal.fire({
                                        title: '¿Eliminar este préstamo personal?',
                                        text: 'Esta acción no se puede deshacer.',
                                        icon: 'warning',
                                        showCancelButton: true,
                                        confirmButtonText: 'Sí, eliminar',
                                        cancelButtonText: 'Cancelar',
                                    }).then((result) => { if (result.isConfirmed) $wire.delete({{ $loan->id }}) })
                                "
                            >
                                Eliminar
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-3 py-4 text-center text-gray-500">No hay préstamos personales registrados.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $loans->links() }}
    </div>
</div>
