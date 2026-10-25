<div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200 text-sm">
        <thead>
            <tr class="text-left text-gray-500">
                <th class="px-3 py-2">Nombre</th>
                <th class="px-3 py-2">Correo</th>
                <th class="px-3 py-2">WhatsApp</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse ($users as $user)
                <tr>
                    <td class="px-3 py-2">{{ $user->name }}</td>
                    <td class="px-3 py-2">{{ $user->email }}</td>
                    <td class="px-3 py-2">{{ $user->whatsapp_number ?? '—' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" class="px-3 py-4 text-center text-gray-500">No hay usuarios registrados.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
