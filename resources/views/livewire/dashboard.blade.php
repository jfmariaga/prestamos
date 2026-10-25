<div class="space-y-6">
    {{-- Filtro de rango de fechas --}}
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
        <div class="flex flex-col sm:flex-row sm:items-end gap-4">
            <div>
                <h3 class="text-lg font-medium text-gray-900">Ganancias esperadas</h3>
                <p class="text-sm text-gray-500">Selecciona un rango de fechas para ver lo cobrado y lo que vence en ese periodo.</p>
            </div>
            <div class="flex items-end gap-3 sm:ml-auto">
                <div>
                    <x-input-label for="rangeStart" value="Desde" />
                    <x-date-input id="rangeStart" class="mt-1" wire:model.live="rangeStart" />
                </div>
                <div>
                    <x-input-label for="rangeEnd" value="Hasta" />
                    <x-date-input id="rangeEnd" class="mt-1" wire:model.live="rangeEnd" />
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mt-6">
            <a
                href="{{ route('reports.interest-collected', ['start' => $rangeStart, 'end' => $rangeEnd]) }}"
                class="rounded-lg border border-gray-200 p-3 hover:border-green-300 hover:shadow-sm transition block"
            >
                <div class="text-xs font-medium text-gray-500">Interés cobrado en el rango</div>
                <div class="mt-1 text-lg font-semibold text-green-700">${{ number_format($interesCobradoRango, 2) }}</div>
                <div class="text-xs text-gray-400 mt-1">Abonos de interés ya recibidos · ver detalle</div>
            </a>
            <a
                href="{{ route('reports.interest-due', ['start' => $rangeStart, 'end' => $rangeEnd]) }}"
                class="rounded-lg border border-gray-200 p-3 hover:border-brand-300 hover:shadow-sm transition block"
            >
                <div class="text-xs font-medium text-gray-500">Interés por cobrar que vence en el rango</div>
                <div class="mt-1 text-lg font-semibold text-brand-700">${{ number_format($interesPorCobrarRango, 2) }}</div>
                <div class="text-xs text-gray-400 mt-1">Pendiente, proyectado para ese periodo · ver detalle</div>
            </a>
            <div class="rounded-lg border border-gray-200 p-3">
                <div class="text-xs font-medium text-gray-500">Saldo disponible para prestar</div>
                <div class="mt-1 text-lg font-semibold text-gray-900">${{ number_format($saldoDisponibleParaPrestar, 2) }}</div>
                <div class="text-xs text-gray-400 mt-1">Capital recuperado por abonos</div>
            </div>
        </div>
    </div>

    {{-- Indicadores clave --}}
    <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-3">
            <div class="text-xs font-medium text-gray-500">Cartera activa (capital)</div>
            <div class="mt-1 text-lg font-semibold text-gray-900">${{ number_format($capitalPrestado, 2) }}</div>
        </div>

        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-3">
            <div class="text-xs font-medium text-gray-500">Interés pendiente total</div>
            <div class="mt-1 text-lg font-semibold text-gray-900">${{ number_format($interesPendienteTotal, 2) }}</div>
            @if ($interesPendienteEnMora > 0)
                <a href="{{ route('reports.overdue') }}" class="text-xs text-red-600 hover:text-red-800 hover:underline mt-1 inline-block">
                    ${{ number_format($interesPendienteEnMora, 2) }} en mora · ver quiénes
                </a>
            @endif
        </div>

        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-3">
            <div class="text-xs font-medium text-gray-500">Tasa de morosidad</div>
            <div class="mt-1 flex items-baseline gap-2">
                <span @class([
                    'text-lg font-semibold',
                    'text-green-700' => $tasaMorosidad < 10,
                    'text-yellow-700' => $tasaMorosidad >= 10 && $tasaMorosidad < 25,
                    'text-red-700' => $tasaMorosidad >= 25,
                ])>{{ $tasaMorosidad }}%</span>
            </div>
            <div class="text-xs text-gray-400 mt-1">{{ $prestamosEnMora }} de {{ $totalPrestamosActivos }} préstamos activos</div>
        </div>

        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-3">
            <div class="text-xs font-medium text-gray-500">Tasa promedio ponderada</div>
            <div class="mt-1 text-lg font-semibold text-gray-900">{{ rtrim(rtrim(number_format($tasaPromedioPonderada, 2), '0'), '.') }}%</div>
            <div class="text-xs text-gray-400 mt-1">Sobre la cartera activa</div>
        </div>
    </div>

    {{-- Alerta de compromisos incumplidos --}}
    @if ($compromisosIncumplidos['cantidad'] > 0)
        <div class="bg-red-50 border border-red-200 rounded-lg p-4 flex flex-col sm:flex-row sm:items-center gap-2 justify-between">
            <div class="text-sm text-red-800">
                <span class="font-semibold">{{ $compromisosIncumplidos['cantidad'] }}</span>
                préstamo(s) activo(s) incumplieron su fecha de compromiso, por un total pendiente de
                <span class="font-semibold">${{ number_format($compromisosIncumplidos['monto'], 2) }}</span>.
            </div>
        </div>
    @endif

    {{-- Gráficas --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
            <h3 class="text-sm font-medium text-gray-700 mb-4">Capital prestado vs. recuperado (últimos 6 meses)</h3>
            <div
                wire:ignore
                x-data="{
                    init() {
                        new Chart(this.$refs.canvas, {
                            type: 'bar',
                            data: {
                                labels: @js($tendenciaCartera['labels']),
                                datasets: [
                                    {
                                        label: 'Prestado',
                                        data: @js($tendenciaCartera['prestado']),
                                        backgroundColor: '#1b355a',
                                        borderRadius: 4,
                                        maxBarThickness: 28,
                                    },
                                    {
                                        label: 'Recuperado',
                                        data: @js($tendenciaCartera['recuperado']),
                                        backgroundColor: '#d99812',
                                        borderRadius: 4,
                                        maxBarThickness: 28,
                                    },
                                ],
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                scales: {
                                    y: {
                                        beginAtZero: true,
                                        ticks: { callback: (v) => '$' + Number(v).toLocaleString('es') },
                                    },
                                },
                                plugins: {
                                    legend: { position: 'bottom', labels: { boxWidth: 12 } },
                                    tooltip: {
                                        callbacks: {
                                            label: (ctx) => ctx.dataset.label + ': $' + Number(ctx.parsed.y).toLocaleString('es'),
                                        },
                                    },
                                },
                            },
                        });
                    },
                }"
                class="h-64"
            >
                <canvas x-ref="canvas"></canvas>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
            <h3 class="text-sm font-medium text-gray-700 mb-4">Interés cobrado por mes (últimos 6 meses)</h3>
            <div
                wire:ignore
                x-data="{
                    init() {
                        new Chart(this.$refs.canvas, {
                            type: 'bar',
                            data: {
                                labels: @js($tendenciaInteresCobrado['labels']),
                                datasets: [
                                    {
                                        label: 'Interés cobrado',
                                        data: @js($tendenciaInteresCobrado['cobrado']),
                                        backgroundColor: '#395f8c',
                                        borderRadius: 4,
                                        maxBarThickness: 28,
                                    },
                                ],
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                scales: {
                                    y: {
                                        beginAtZero: true,
                                        ticks: { callback: (v) => '$' + Number(v).toLocaleString('es') },
                                    },
                                },
                                plugins: {
                                    legend: { display: false },
                                    tooltip: {
                                        callbacks: {
                                            label: (ctx) => 'Cobrado: $' + Number(ctx.parsed.y).toLocaleString('es'),
                                        },
                                    },
                                },
                            },
                        });
                    },
                }"
                class="h-64"
            >
                <canvas x-ref="canvas"></canvas>
            </div>
        </div>
    </div>

    {{-- Concentración de cartera y próximos vencimientos --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
            <h3 class="text-sm font-medium text-gray-700 mb-4">Top 5 deudores por saldo pendiente</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead>
                        <tr class="text-left text-gray-500">
                            <th class="px-3 py-2">Deudor</th>
                            <th class="px-3 py-2">Saldo pendiente</th>
                            <th class="px-3 py-2">% de la cartera</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($topDeudores as $deudor)
                            <tr>
                                <td class="px-3 py-2">{{ $deudor['borrower_name'] }}</td>
                                <td class="px-3 py-2">${{ number_format($deudor['outstanding'], 2) }}</td>
                                <td class="px-3 py-2">{{ $deudor['porcentaje'] }}%</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-3 py-4 text-center text-gray-500">No hay préstamos activos.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
            <h3 class="text-sm font-medium text-gray-700 mb-4">Próximos vencimientos (15 días)</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead>
                        <tr class="text-left text-gray-500">
                            <th class="px-3 py-2">Deudor</th>
                            <th class="px-3 py-2">Vence</th>
                            <th class="px-3 py-2">Saldo pendiente</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($proximosVencimientos as $loan)
                            <tr>
                                <td class="px-3 py-2">
                                    <a href="{{ route('loans.show', $loan) }}" class="text-brand-600 hover:text-brand-800 hover:underline">{{ $loan->borrower->name }}</a>
                                </td>
                                <td class="px-3 py-2">{{ $loan->next_interest_due_date->format('d/m/Y') }}</td>
                                <td class="px-3 py-2">${{ number_format($loan->totalOutstanding(), 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-3 py-4 text-center text-gray-500">No hay vencimientos próximos.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Listado de préstamos --}}
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
            <h3 class="text-lg font-medium text-gray-900">Préstamos</h3>
            <a href="{{ route('loans.create') }}" class="inline-flex items-center justify-center px-4 py-2.5 bg-brand-700 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-brand-600">
                Nuevo préstamo
            </a>
        </div>
        @livewire('loans.loan-index')
    </div>
</div>
