# CLAUDE.md

This file provides guidance to Claude Code when working with the CR3D3NC3 codebase.

## Project Overview

**CR3D3NC3** is a Loan Management System (LMS) built with Laravel 12 and FilamentPHP 4. It manages the full lending lifecycle: lead capture → customer onboarding → loan origination → payment collection → refinancing. All functionality is behind a Filament admin panel at `/admin` — there are no public-facing routes.

## Tech Stack

| Layer | Technology |
|---|---|
| Backend | Laravel 12, PHP ^8.2 |
| Admin UI | FilamentPHP 4 (with Livewire) |
| Frontend | Blade, Tailwind CSS 4, Vite 7 |
| Auth & RBAC | Spatie Laravel Permission 6 |
| Media | Spatie Media Library (via Filament plugin) |
| Settings | Spatie Laravel Settings (via Filament plugin) |
| i18n | Spatie Laravel Translatable |
| PDF | Laravel DomPDF |
| Excel | Maatwebsite Excel 3.1 |
| Analytics | Laravel Trend |
| Testing | Pest PHP 3.8 |
| Static Analysis | Larastan 3 (level 5) |
| Code Style | Laravel Pint |
| Dev Server | Laravel Sail (Docker) |

## Commands

```bash
# Development
composer dev          # Concurrent: php artisan serve + queue:listen + npm run dev
npm run dev           # Vite dev server with hot reload

# Setup
composer setup        # Full project initialisation
php artisan migrate --seed

# Build
npm run build

# Testing
composer test         # php artisan config:clear && pest
php artisan test      # Laravel test runner
./vendor/bin/pest --coverage

# Static Analysis
./vendor/bin/phpstan analyse   # Larastan level 5 (paths: app/, config/, database/, routes/)

# Code Style
./vendor/bin/pint     # Laravel Pint formatter
```

## Architecture

### No Custom Controllers
All CRUD is handled by Filament Resources. The `app/Http/Controllers/` directory holds only a base controller. Do not add new controllers for admin features — create Filament Resources instead.

### Filament Resources (`app/Filament/Resources/`)
Resources follow a clean separation of concerns pattern:
- `{Resource}Resource.php` — registers nav, delegates to sub-classes
- `{Resource}Resource/Form/{Resource}Form.php` — form schema
- `{Resource}Resource/Tables/{Resource}sTable.php` — table columns, filters, actions
- `{Resource}Resource/Infolist/{Resource}Infolist.php` — view-only display schema
- `{Resource}Resource/Pages/` — Create, Edit, List, View pages

When adding a new resource, follow this same pattern. Do not embed large form/table schemas directly inside the Resource class.

### Models (`app/Models/`)
All models use:
- **`Auditable` trait** — auto-creates `AuditLog` entries on every CRUD operation (do not bypass)
- **`SoftDeletes`** — no hard deletes on core business models
- **Enum casts** — statuses use typed enums from `app/Enums/`, never raw strings

Key models: `Loan`, `Payment`, `Customer`, `Lead`, `Refinance`, `Product`, `Bank`, `BankBranch`, `User`, `AuditLog`, `Address`, `County`, `Town`.

### Observers (`app/Observers/`)
Side effects (audit logging, auto-clearance, status transitions) live in observers, not in models or controllers:
- `LoanObserver` — loan status transitions, financial calculations
- `PaymentObserver` — **auto-clears loan when balance ≤ 0** (sets status to `CLEARED`)
- `RefinanceObserver` — eligibility and status tracking
- `LeadObserver`, `UserObserver` — audit trail

### Policies (`app/Policies/`)
Authorization is checked via `hasPermissionTo()` (Spatie permission gates), not role checks. One policy per model. Restore and force-delete are disabled on most models.

### Traits (`app/Traits/`)
- `Auditable` — bootable trait, registers `creating/updating/deleting` model events. All models must use this.

## Business Logic

### Loan Financial Calculations
```
Monthly Interest    = product_rate × loan_amount
Processing Fee      = loan_amount × 5%
Monthly Installment = monthly_principal + monthly_interest
Loan Total          = installment × period
Loan Balance        = loan_total − sum(approved payments)
```

### Loan Status Workflow
```
pending_verification → pending_confirmation → pending_approval
  → pending_disbursement → disbursed → overdue / past_overdue
  → cleared / written_off / fraud / cancelled
```

### Refinance Eligibility
```
eligible = (customer_loan_limit - loan_balance) >= 1000
```

### Due Date Logic
Calculated from bank payday with weekend adjustments (Kenya payroll cycles).

### Auto-Classification
Loans are auto-classified as **New Loan / Old Loan / Top-up** based on customer history.

## Roles & Permissions (Spatie)
| Role | Access |
|---|---|
| Super Admin | Full access + system logs (Log Viewer) |
| Admin | Full operational access |
| Sales Agent | Leads + loan origination |
| Collection Agent | Payments + overdue management |
| Collection Officer | Oversight + reporting |

## Testing
- Framework: **Pest PHP 3.8**
- Database: **SQLite in-memory** (`:memory:`) — no external DB needed
- Drivers: cache=array, mail=array, queue=sync, session=array
- Test suites: `tests/Feature/` and `tests/Unit/`
- Always run `composer test` (clears config cache before running Pest)

## Static Analysis
- **Larastan level 5** across `app/`, `config/`, `database/`, `routes/`
- `phpstan.neon` — do not raise the level without verifying all paths pass
- Keep `treatPhpDocTypesAsCertain: false`

## Code Style
- **Laravel Pint** — run before committing
- **Enums over strings** — always use `app/Enums/` types for statuses
- **No magic numbers** — financial rates, thresholds, and limits must be derived from model/product config

## Frontend
- **No JavaScript framework** — pure Blade + Tailwind CSS + Livewire (via Filament)
- **Tailwind CSS 4** — configured via `@import 'tailwindcss'` in `resources/css/app.css`
- Custom font: **Instrument Sans** (defined in `@theme` block)
- Icons: Heroicons (default), Font Awesome (`owenvoke/blade-fontawesome`), Hugeicons (`afatmustafa/blade-hugeicons`)
- Vite entry points: `resources/css/app.css`, `resources/js/app.js`

## Key Conventions
- **Audit everything** — the `Auditable` trait must remain on all core models
- **Soft deletes only** — never hard-delete business data (Leads, Customers, Loans, Payments, etc.)
- **Filament for UI** — add new admin features as Filament Resources/Pages/Widgets, not custom Blade views
- **Permissions are granular** — check `hasPermissionTo()` in policies, avoid role-based shortcuts
- **Kenya-specific data** — `app/Faker/Providers/` contains local data providers; use them in factories
- **`FaIcon` enum** — auto-generated at `app/Enums/FaIcon.php` via `php artisan icons:generate`; do not edit manually

## Entity Relationships (High Level)
```
Lead ──(convert)──► Customer ──► Loans ──► Payments
                                    └──► Refinances
Customer / Loan ──► Bank ──► BankBranch
Customer / Loan ──► Product
Loan ──► User (sales_agent, temp_agent, collection_agent, collection_officer)
All models ──► AuditLog
```
## Git Workflow
- NEVER commit automatically
- NEVER create pull requests automatically
- NEVER run `git commit` or `gh pr create` without my explicit instruction
- Only stage and commit when I say "commit this" or "create a PR"
- Always show me the diff before any git operation
```

## 💡 Recommended Workflow for Laravel Projects
```
1. Ask Claude to make changes
2. Review diffs in PhpStorm diff viewer
3. Test locally (php artisan test)
4. Then manually tell Claude: "commit and push"
5. Then separately: "open a PR to main"
