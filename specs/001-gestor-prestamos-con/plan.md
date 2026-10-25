# Implementation Plan: Gestor de Préstamos con Intereses

**Branch**: `001-gestor-prestamos-con` | **Date**: 2026-10-25 | **Spec**: `specs/001-gestor-prestamos-con/spec.md`

**Input**: Feature specification from `specs/001-gestor-prestamos-con/spec.md`

## Summary

Aplicación web Laravel + Livewire (monolito, MySQL) para gestionar préstamos personales con interés mensual simple. Permite crear préstamos, registrar abonos que priorizan el pago de interés sobre capital, recalcular automáticamente el interés del siguiente corte, visualizar un dashboard de indicadores, y notificar a los administradores por WhatsApp cuando un préstamo se acerca a su fecha de pago.

## Technical Context

**Language/Version**: PHP 8.2, Laravel 11.x

**Primary Dependencies**: Livewire 3, Laravel Breeze (auth con Livewire stack), Tailwind CSS (vía Breeze), Laravel Scheduler, cliente HTTP (`Illuminate\Support\Facades\Http`) para integración WhatsApp Cloud API

**Storage**: MySQL 8 (base de datos `gestor_prestamos`)

**Testing**: PHPUnit/Pest (feature tests sobre el servicio de abonos y el job de notificaciones)

**Target Platform**: Servidor local Laragon (Apache/Nginx + PHP-FPM), cron de Windows/Task Scheduler o `php artisan schedule:work` para tareas programadas

**Project Type**: Web application (monolito Laravel con Livewire, sin frontend separado)

**Performance Goals**: Operación de abono y creación de préstamo responde en <300ms en entorno local; dashboard calcula agregados en <500ms con hasta ~5,000 préstamos

**Constraints**: Cálculo financiero debe ser exacto (uso de `decimal` en BD, no `float`); un solo rol "administrador" protegido por autenticación

**Scale/Scope**: Uso interno, pocos administradores concurrentes (<10), cientos a miles de préstamos

## Constitution Check

No existe constitución de proyecto con reglas estrictas más allá del template por defecto (principios genéricos de calidad/tests/simplicidad). Se aplican como guía: mantener la lógica financiera centralizada en un único servicio testeable (`LoanPaymentService`), evitar lógica de negocio en los componentes Livewire, y cubrir con tests el cálculo de interés/abonos antes de dar por cerrada la User Story 2.

## Project Structure

### Documentation (this feature)

```text
specs/001-gestor-prestamos-con/
├── plan.md              # Este archivo
├── data-model.md        # Modelo de datos (Fase 1)
├── quickstart.md        # Guía de instalación/uso (Fase 1)
└── tasks.md             # Lista de tareas (Fase 2)
```

### Source Code (repository root = C:\laragon\www\gestor-prestamos)

```text
app/
├── Models/
│   ├── Loan.php                # Préstamo
│   ├── LoanPayment.php         # Abono
│   ├── User.php                # Administrador (modelo de Breeze)
│   └── WhatsappNotificationLog.php
├── Services/
│   └── LoanPaymentService.php  # Lógica: aplicar abono (interés primero), recalcular próximo corte
├── Services/Whatsapp/
│   └── WhatsappNotifier.php    # Cliente delgado sobre la API de WhatsApp (Meta Cloud API)
├── Console/Commands/
│   ├── AccrueLoanInterest.php      # Corte mensual de interés (scheduler)
│   └── NotifyUpcomingLoanDueDates.php # Notificaciones WhatsApp (scheduler diario)
├── Livewire/
│   ├── Dashboard.php
│   ├── Loans/LoanIndex.php
│   ├── Loans/LoanForm.php
│   └── Loans/LoanShow.php      # Detalle + formulario de abono + historial
└── Http/Controllers/           # Solo controladores mínimos (auth de Breeze)

database/
├── migrations/
│   ├── create_loans_table.php
│   ├── create_loan_payments_table.php
│   ├── add_whatsapp_number_to_users_table.php
│   └── create_whatsapp_notification_logs_table.php
└── factories/ + seeders/ (datos de prueba)

resources/views/livewire/...    # Vistas Blade de cada componente Livewire
tests/
├── Feature/LoanPaymentServiceTest.php
├── Feature/LoanCreationTest.php
└── Feature/NotifyUpcomingLoanDueDatesTest.php
```

**Structure Decision**: Monolito Laravel estándar con Livewire para toda la interfaz (sin SPA/API separada). Toda la lógica de cálculo financiero vive en `app/Services/LoanPaymentService.php` para mantenerla aislada y testeable fuera de los componentes Livewire.

## Complexity Tracking

Sin violaciones; no se requiere justificar complejidad adicional.
