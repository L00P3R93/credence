# CR3D3NC3 Project Memory

## Project
- **Name:** CR3D3NC3 — Loan Management System (LMS)
- **Stack:** Laravel 12, PHP 8.2, FilamentPHP 4, Tailwind CSS 4, Vite 7, Pest PHP 3.8
- **Admin panel:** `/admin` (FilamentPHP) — no public routes
- **CLAUDE.md:** Written and present at project root

## Key Packages
- Spatie Permission 6 (RBAC), Spatie Media Library, Spatie Settings, Spatie Translatable
- Laravel DomPDF, Maatwebsite Excel, Laravel Trend, Larastan 3, Laravel Pint

## Architecture Patterns
- No custom controllers — all CRUD via Filament Resources
- Resources split into Form/Table/Infolist sub-classes
- All models use `Auditable` trait + `SoftDeletes`
- Side effects (status transitions, auto-clearance) in Observers
- Permissions via `hasPermissionTo()` in Policies (Spatie gates)

## Important Business Rules
- `PaymentObserver`: auto-sets loan status to CLEARED when balance ≤ 0
- Refinance eligible when `(customer_loan_limit - loan_balance) >= 1000`
- `FaIcon` enum is auto-generated — never edit manually
- Kenya-specific Faker providers in `app/Faker/Providers/`

## Commands
- `composer dev` — runs serve + queue + vite concurrently
- `composer test` — config:clear + pest
- `./vendor/bin/phpstan analyse` — Larastan level 5
- `./vendor/bin/pint` — code style

## User Preferences
- Wants to review generated files in chat before applying to codebase
