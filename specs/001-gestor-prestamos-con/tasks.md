# Tasks: Gestor de Préstamos con Intereses

**Input**: plan.md, spec.md de `specs/001-gestor-prestamos-con/`

## Fase 1: Setup
- [ ] T001 Crear proyecto Laravel 11 en `C:\laragon\www\gestor-prestamos` (sin sobrescribir `specs/` ni `.specify/`)
- [ ] T002 Configurar `.env` con conexión MySQL (`gestor_prestamos`) y crear la base de datos
- [ ] T003 Instalar Laravel Breeze con stack Livewire + instalar Livewire 3
- [ ] T004 Compilar assets (`npm install && npm run build`) y verificar arranque (`php artisan serve` o vhost Laragon)

## Fase 2: Modelo de datos (bloquea todo lo demás)
- [ ] T010 Migración `loans` (nombre, telefono, referencia, capital_inicial, capital_pendiente, tasa_interes, interes_pendiente, fecha_prestamo, fecha_proximo_pago, estado)
- [ ] T011 Migración `loan_payments` (loan_id, fecha, monto_total, monto_interes, monto_capital, capital_resultante, interes_resultante)
- [ ] T012 Migración: agregar `whatsapp_number` a `users`
- [ ] T013 Migración `whatsapp_notification_logs` (loan_id, due_date, sent_at)
- [ ] T014 Modelos Eloquent `Loan`, `LoanPayment`, `WhatsappNotificationLog` con relaciones y casts `decimal`
- [ ] T015 Factories + seeder de datos de prueba (préstamos y abonos variados)

## Fase 3: User Story 1 - Registrar préstamo (P1) 🎯 MVP
- [ ] T020 `LoanForm` (Livewire) con validación (capital > 0, tasa >= 0, campos obligatorios)
- [ ] T021 Al crear: capital_pendiente = capital_inicial, interes_pendiente = capital_inicial * tasa/100, fecha_proximo_pago = fecha_prestamo + 1 mes, estado = activo
- [ ] T022 `LoanIndex` (Livewire) listado con estado, capital pendiente, próxima fecha de pago
- [ ] T023 Vistas Blade + rutas protegidas por `auth` para index/create

**Checkpoint**: se puede crear y listar préstamos.

## Fase 4: User Story 2 - Registrar abono con prioridad a interés (P1)
- [ ] T030 `LoanPaymentService::applyPayment(Loan $loan, float $amount)`: aplica primero a interés_pendiente, luego a capital_pendiente, actualiza saldos, crea `LoanPayment`, marca "pagado" si ambos llegan a 0
- [ ] T031 Rechazar abono si `loan.estado === 'pagado'` o si `amount <= 0`; limitar el abono al saldo total si lo excede
- [ ] T032 Tests feature para los 4 escenarios de aceptación de la User Story 2 (120→50: 20 interés/30 capital; abono parcial menor al interés; pago total; rechazo sobre préstamo pagado)
- [ ] T033 `LoanShow` (Livewire): detalle del préstamo + formulario de abono + historial de abonos (tabla con fecha, monto, interés, capital, saldos)

**Checkpoint**: flujo completo de cobro funcionando con cálculo correcto.

## Fase 5: User Story 3 - Recalcular interés del próximo corte (P2)
- [ ] T040 Comando `AccrueLoanInterest` (artisan): para préstamos activos cuya `fecha_proximo_pago` ya venció, suma `capital_pendiente * tasa/100` a `interes_pendiente` y avanza `fecha_proximo_pago` un mes
- [ ] T041 Registrar el comando en el scheduler (`routes/console.php`) con ejecución diaria
- [ ] T042 Test: préstamo con capital 70 y tasa 20% genera 14 de interés en el corte; préstamo pagado no genera interés

## Fase 6: User Story 4 - Dashboard (P2)
- [ ] T050 `Dashboard` (Livewire): tarjeta capital prestado = suma `capital_pendiente` de préstamos activos
- [ ] T051 Tarjeta "Intereses del mes" = suma `monto_interes` de `loan_payments` del mes en curso
- [ ] T052 Tarjeta "Nuevos préstamos del mes" = conteo y suma de `capital_inicial` de préstamos con `fecha_prestamo` en el mes en curso
- [ ] T053 Vista Blade del dashboard como página de inicio tras login

## Fase 7: User Story 5 - Notificaciones WhatsApp (P3)
- [ ] T060 `WhatsappNotifier` (servicio): envía mensaje vía API HTTP (Meta WhatsApp Cloud API) usando credenciales en `.env`
- [ ] T061 Comando `NotifyUpcomingLoanDueDates`: busca préstamos activos con `fecha_proximo_pago` dentro de la ventana configurable (`config('loans.notification_days_before')`, default 2), evita duplicados vía `whatsapp_notification_logs`, envía a todos los `users.whatsapp_number` configurados
- [ ] T062 Registrar el comando en el scheduler con ejecución diaria
- [ ] T063 Test: genera notificación dentro de la ventana, no duplica en segunda ejecución, no notifica préstamos pagados

## Fase 8: Pulido
- [ ] T070 Manejo de errores de la API de WhatsApp sin interrumpir el resto del job (log + continuar)
- [ ] T071 Revisión de autorización (todas las rutas Livewire requieren `auth`)
- [ ] T072 README/quickstart con pasos de instalación y configuración de `.env`

## Dependencias
- Fase 2 bloquea todas las demás.
- Fase 3 antes de Fase 4 (se necesita un préstamo para abonar).
- Fase 4 antes de Fase 5 (recalculo depende del servicio de abonos).
- Fase 6 depende de datos generados por Fases 3-5.
- Fase 7 es independiente de Fase 6 pero depende del campo `fecha_proximo_pago` de Fase 2/5.
