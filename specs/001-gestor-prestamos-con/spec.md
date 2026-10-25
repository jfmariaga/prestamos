# Feature Specification: Gestor de Préstamos con Intereses

**Feature Branch**: `001-gestor-prestamos-con`

**Created**: 2026-10-25

**Status**: Draft

**Input**: User description: "Gestor de préstamos personales: permite crear préstamos (nombre, teléfono, referencia del deudor, valor prestado, % interés mensual, fecha del préstamo), registrar abonos que se aplican primero a intereses pendientes y el resto a capital, recalcular el próximo pago de interés según el capital restante, y un dashboard con capital prestado total, intereses del mes en curso, nuevos préstamos del mes y notificaciones a WhatsApp para administradores cuando un préstamo esté próximo a su fecha de pago."

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Registrar un nuevo préstamo (Priority: P1)

Un administrador registra un nuevo préstamo capturando los datos mínimos del deudor (nombre, teléfono, referencia), el valor prestado, el porcentaje de interés mensual y la fecha del préstamo.

**Why this priority**: Es la operación base del sistema; sin ella no existe nada que gestionar.

**Independent Test**: Se puede probar creando un préstamo desde el formulario y verificando que aparece en el listado con el capital, la tasa y la fecha de próximo pago de interés calculada automáticamente (fecha del préstamo + 1 mes).

**Acceptance Scenarios**:

1. **Given** el formulario de nuevo préstamo, **When** el administrador ingresa nombre, teléfono, referencia, valor prestado, % de interés y fecha, **Then** el sistema crea el préstamo con estado "activo", capital pendiente = valor prestado, e interés mensual calculado = valor prestado × % interés.
2. **Given** un formulario de préstamo incompleto (falta nombre o valor prestado ≤ 0), **When** el administrador intenta guardar, **Then** el sistema muestra errores de validación y no crea el registro.

---

### User Story 2 - Registrar un abono con prioridad a intereses (Priority: P1)

Un administrador registra un abono (pago) sobre un préstamo existente. El monto del abono se aplica primero a los intereses pendientes acumulados y el excedente se aplica a capital.

**Why this priority**: Es la operación financiera central: sin un cálculo correcto de abonos, el sistema no sirve como gestor de cobros.

**Independent Test**: Se puede probar registrando un préstamo con interés pendiente conocido (p. ej. capital 100, interés generado 20 → deuda total 120) y aplicando un abono de 50, verificando que 20 se asignan a interés y 30 a capital, dejando capital pendiente en 70 e interés pendiente en 0.

**Acceptance Scenarios**:

1. **Given** un préstamo con capital pendiente 100 y 20 de interés pendiente (deuda total 120), **When** se registra un abono de 50, **Then** el interés pendiente queda en 0, se registran 20 como "pago de interés" y 30 como "abono a capital", y el nuevo capital pendiente es 70.
2. **Given** un préstamo con capital pendiente 100 y 20 de interés pendiente, **When** se registra un abono de 15 (menor al interés pendiente), **Then** los 15 se aplican completos a interés, el interés pendiente queda en 5 y el capital pendiente no cambia.
3. **Given** un préstamo con capital pendiente 100 y 0 de interés pendiente, **When** se registra un abono de 100, **Then** el capital pendiente queda en 0 y el préstamo cambia a estado "pagado".
4. **Given** un abono registrado, **When** se consulta el historial del préstamo, **Then** se muestra el desglose (fecha, monto total, parte a interés, parte a capital, saldos resultantes).

---

### User Story 3 - Recalcular el próximo pago de interés según el capital restante (Priority: P2)

Después de cada abono que reduce el capital, el sistema debe recalcular el interés mensual a generar en el siguiente corte, en función del nuevo capital pendiente.

**Why this priority**: Garantiza que los cobros futuros sean correctos tras abonos a capital; depende de que P1/P2 anteriores existan.

**Independent Test**: Reducir el capital de un préstamo mediante un abono y verificar que el interés mensual proyectado del siguiente periodo se recalcula como nuevo_capital × % interés, y que la fecha de próximo pago de interés avanza un mes desde el último corte.

**Acceptance Scenarios**:

1. **Given** un préstamo con capital pendiente 70 y tasa 20%, **When** se genera el corte mensual de interés, **Then** el interés generado es 14 (70 × 20%) y se suma al interés pendiente.
2. **Given** un préstamo cuyo capital llegó a 0, **When** se evalúa el corte mensual, **Then** no se genera nuevo interés y el préstamo permanece en estado "pagado".

---

### User Story 4 - Dashboard de indicadores (Priority: P2)

Un administrador visualiza un dashboard con el capital total prestado (activo), los intereses generados/cobrados en el mes en curso, y la cantidad/valor de préstamos nuevos del mes.

**Why this priority**: Da visibilidad gerencial del negocio; depende de que existan préstamos y abonos para calcular.

**Independent Test**: Con datos de prueba (préstamos y abonos en distintas fechas), verificar que las tarjetas del dashboard muestran los totales correctos filtrados al mes en curso.

**Acceptance Scenarios**:

1. **Given** varios préstamos activos, **When** se abre el dashboard, **Then** la tarjeta "Capital prestado" muestra la suma de capital pendiente de todos los préstamos activos.
2. **Given** abonos registrados con parte de interés en el mes actual, **When** se abre el dashboard, **Then** la tarjeta "Intereses del mes" muestra la suma de los montos aplicados a interés en ese mes.
3. **Given** préstamos creados en el mes actual, **When** se abre el dashboard, **Then** la tarjeta "Nuevos préstamos" muestra el conteo y el valor total desembolsado en el mes.

---

### User Story 5 - Notificación por WhatsApp próxima fecha de pago (Priority: P3)

El sistema notifica a los administradores por WhatsApp cuando un préstamo está próximo a su fecha de pago de interés (p. ej. N días antes).

**Why this priority**: Es una mejora operativa (recordatorio proactivo); el sistema es funcional sin ella, por lo que se prioriza al final.

**Independent Test**: Con un préstamo cuya próxima fecha de pago cae dentro de la ventana configurada (p. ej. 2 días), ejecutar el proceso programado y verificar que se envía/registra una notificación de WhatsApp a los números de los administradores configurados, una sola vez por vencimiento.

**Acceptance Scenarios**:

1. **Given** un préstamo cuya fecha de próximo pago de interés es en 2 días, **When** corre el job diario de notificaciones, **Then** se envía un mensaje de WhatsApp a cada administrador con los datos del préstamo (deudor, monto adeudado, fecha).
2. **Given** una notificación ya enviada para un vencimiento concreto, **When** el job vuelve a ejecutarse el mismo día, **Then** no se duplica el envío.
3. **Given** un préstamo en estado "pagado", **When** corre el job de notificaciones, **Then** no se genera ninguna alerta para ese préstamo.

### Edge Cases

- ¿Qué pasa si el abono registrado es mayor a la deuda total (interés + capital pendiente)? El sistema debe rechazar el excedente o limitarlo al saldo total, dejando el préstamo en "pagado".
- ¿Qué pasa si se intenta registrar un abono sobre un préstamo ya "pagado"? Debe rechazarse con un mensaje claro.
- ¿Qué pasa si el % de interés es 0? El préstamo no genera interés y los abonos se aplican 100% a capital.
- ¿Qué pasa si no hay número de WhatsApp configurado para ningún administrador? El job debe registrar el fallo sin interrumpir el resto del proceso.
- ¿Qué pasa si se borra/edita un préstamo con abonos ya registrados? No se permite eliminar; solo se permite anular/editar datos no financieros (teléfono, referencia).

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: El sistema DEBE permitir crear un préstamo con: nombre del deudor, teléfono, referencia (persona/dato de referencia), valor prestado (capital inicial), % de interés mensual y fecha del préstamo.
- **FR-002**: El sistema DEBE calcular automáticamente el interés mensual generado como capital_pendiente × (% interés / 100) en cada corte mensual.
- **FR-003**: El sistema DEBE permitir registrar abonos sobre un préstamo, aplicando el monto primero a interés pendiente y el remanente a capital pendiente.
- **FR-004**: El sistema DEBE actualizar el saldo de capital pendiente y de interés pendiente del préstamo después de cada abono.
- **FR-005**: El sistema DEBE marcar un préstamo como "pagado" cuando el capital pendiente y el interés pendiente lleguen a 0.
- **FR-006**: El sistema DEBE impedir abonos sobre préstamos en estado "pagado".
- **FR-007**: El sistema DEBE mantener un historial de abonos por préstamo (fecha, monto total, parte a interés, parte a capital, saldos resultantes tras el abono).
- **FR-008**: El sistema DEBE exponer un dashboard con: capital total prestado (activo), interés generado/cobrado en el mes en curso, y préstamos nuevos del mes (cantidad y valor).
- **FR-009**: El sistema DEBE enviar una notificación por WhatsApp a los administradores configurados cuando la fecha de próximo pago de interés de un préstamo esté dentro de una ventana de aviso configurable (por defecto 2 días antes).
- **FR-010**: El sistema NO DEBE enviar notificaciones duplicadas para el mismo vencimiento de un préstamo.
- **FR-011**: El sistema DEBE validar que el valor prestado sea mayor a 0 y el % de interés sea mayor o igual a 0.
- **FR-012**: El sistema DEBE requerir autenticación para acceder a cualquier funcionalidad (solo usuarios administradores).

### Key Entities *(include if feature involves data)*

- **Prestamo**: Representa el préstamo otorgado a una persona. Atributos: nombre del deudor, teléfono, referencia, capital inicial, capital pendiente, % de interés mensual, interés pendiente acumulado, fecha del préstamo, fecha de próximo pago de interés, estado (activo/pagado).
- **Abono**: Representa un pago registrado contra un préstamo. Atributos: préstamo asociado, fecha, monto total, monto aplicado a interés, monto aplicado a capital, capital pendiente resultante, interés pendiente resultante.
- **Administrador (Usuario)**: Usuario del sistema con acceso al gestor. Atributos: nombre, email, número de WhatsApp para notificaciones.
- **NotificacionEnviada**: Registro de control para evitar notificaciones duplicadas. Atributos: préstamo asociado, fecha de vencimiento notificada, fecha de envío.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: Un administrador puede registrar un préstamo nuevo en menos de 1 minuto.
- **SC-002**: El cálculo de interés/capital tras un abono es exacto al 100% frente al criterio "interés primero, luego capital" en todos los casos de prueba definidos en los escenarios de aceptación.
- **SC-003**: El dashboard refleja los totales correctos (capital prestado, interés del mes, préstamos nuevos del mes) con una antigüedad de datos no mayor a 1 minuto tras registrar una operación.
- **SC-004**: El 100% de los préstamos cuya fecha de pago entra en la ventana de aviso configurada generan una notificación de WhatsApp, sin duplicados, dentro de las 24 horas siguientes a la ejecución del job.

## Assumptions

- Solo existe un tipo de usuario con acceso al sistema: "administrador"; no hay rol de cliente/deudor con acceso propio.
- El interés se calcula de forma simple mensual sobre el capital pendiente (no interés compuesto diario), recalculado en cada corte mensual o tras cada abono a capital.
- El envío de WhatsApp se realiza a través de un proveedor de API externo (ej. la API de WhatsApp Business/Twilio o similar) configurado mediante variables de entorno; la integración concreta se define en la fase de planeación técnica.
- El proceso de corte mensual de interés y el job de notificaciones corren mediante el scheduler de Laravel (tareas programadas), por lo que requieren un cron configurado en el servidor.
- No se requiere soporte multi-moneda; todos los montos están en una sola moneda (pesos, sin decimales de centavos obligatorios).
- No está en alcance de esta primera versión: pagarés/firma digital, reportes fiscales, ni gestión de cobranza legal.
