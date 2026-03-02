# CR3D3NC3
<<<<<<< Updated upstream

A comprehensive CRM & Credit Lending Management System built with **Laravel 12** and **FilamentPHP 4**. CR3D3NC3 manages the full lifecycle of a loan — from lead capture and customer onboarding through disbursement, repayment tracking, refinancing, and collections.

---

## Table of Contents

- [Features](#features)
- [Tech Stack](#tech-stack)
- [Requirements](#requirements)
- [Installation](#installation)
- [Configuration](#configuration)
- [Usage](#usage)
- [Data Model](#data-model)
- [Roles & Permissions](#roles--permissions)
- [Business Logic](#business-logic)
- [File Structure](#file-structure)
- [Testing](#testing)
- [License](#license)

---

## Features

### Core Modules

| Module | Description |
|---|---|
| **Lead Management** | Capture and track potential borrowers, manage lead status, convert leads to customers |
| **Customer Management** | Full borrower profiles with loan eligibility, limits, and status (Active / Blocked / Blacklisted) |
| **Loan Management** | End-to-end loan origination with a multi-stage approval workflow |
| **Payment Processing** | Record payments via M-Pesa, bank transfer, or cash; auto-clears loans on full repayment |
| **Refinance Management** | Eligibility checking and request tracking for loan refinancing |
| **Bank & Product Config** | Configure loan products with interest rates and banks with payday/weekend rules |
| **User & Role Management** | Role-based access control with granular permissions |
| **Audit Logging** | Immutable audit trail recording every change across all core entities |

### Supporting Features

- PDF report generation (Laravel DomPDF)
- Excel export/import (Maatwebsite Excel)
- Document & media uploads with MIME validation (Spatie Media Library)
- Application settings management (Spatie Settings)
- System log viewer for Super Admins
- Multi-language support (Spatie Translatable)
- Gravatar / avatar support for users
- Soft deletes on all core models for data preservation
- Database notifications via Filament

---

## Tech Stack

**Backend**
- [Laravel 12](https://laravel.com) — PHP web framework
- [FilamentPHP 4](https://filamentphp.com) — Admin panel framework
- [Spatie Laravel Permission](https://spatie.be/docs/laravel-permission) — Role & permission management
- [Spatie Media Library](https://spatie.be/docs/laravel-medialibrary) — File/media management
- [Spatie Laravel Settings](https://github.com/spatie/laravel-settings) — Application settings
- [Spatie Laravel Translatable](https://github.com/spatie/laravel-translatable) — Multi-language support
- [Maatwebsite Excel](https://laravel-excel.com) — Excel export & import
- [Laravel DomPDF](https://github.com/barryvdh/laravel-dompdf) — PDF generation
- [Laravel Trend](https://github.com/flowframe/laravel-trend) — Analytics & trending data

**Frontend**
- [Tailwind CSS 4](https://tailwindcss.com) — Utility-first CSS framework
- [Vite 7](https://vitejs.dev) — Frontend build tool
- Heroicons & Font Awesome — Icon libraries
- Blade — Laravel templating engine

**Development & Testing**
- [Pest PHP](https://pestphp.com) — Testing framework
- [Larastan](https://github.com/larastan/larastan) — Static analysis
- [Laravel Pint](https://laravel.com/docs/pint) — Code style fixer
- [Laravel Sail](https://laravel.com/docs/sail) — Docker development environment
- [Log Viewer](https://log-viewer.opcodes.io) — Runtime log inspection

---

## Requirements

- PHP 8.2+
- Composer 2.x
- Node.js 20+ & npm
- MySQL / PostgreSQL / SQLite

---

## Installation

```bash
# 1. Clone the repository
git clone <repository-url> cr3d3nc3
cd cr3d3nc3

# 2. Install PHP dependencies
composer install

# 3. Install Node dependencies
npm install

# 4. Copy the environment file and configure it
cp .env.example .env

# 5. Generate the application key
php artisan key:generate

# 6. Run database migrations and seed initial data
php artisan migrate --seed

# 7. Create the storage symlink
php artisan storage:link

# 8. Build frontend assets
npm run build
```

For local development with hot-reload:

```bash
php artisan serve
npm run dev
```

---

## Configuration

Key values to set in your `.env` file:

```env
APP_NAME=CR3D3NC3
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=cr3d3nc3
DB_USERNAME=root
DB_PASSWORD=

MAIL_MAILER=smtp
MAIL_HOST=...
MAIL_PORT=587
MAIL_USERNAME=...
MAIL_PASSWORD=...

FILESYSTEM_DISK=public
```

---

## Usage

Access the admin panel at `/admin` after running the application. Log in with the credentials created during seeding.

### Navigation Groups

| Group | Resources |
|---|---|
| **Leads & Customers** | Leads, Customers |
| **Loan Management** | Loans |
| **Payments** | Payments, Refinances |
| **Banks** | Banks, Bank Branches |
| **User Management** | Users, Roles, Permissions |
| **System Management** | Audit Logs, Log Viewer (Super Admin only) |

---

## Data Model

### Entity Relationships

```
Lead ──(convert)──► Customer ──► Loans ──► Payments
                                       └──► Refinances
Customer / Loan ──► Bank ──► Bank Branch
Customer / Loan ──► Product
Loan ──► User (as sales agent, temp agent, collection agent, collection officer)
All models ──► AuditLog
```

### Core Entities

| Model | Key Fields |
|---|---|
| **User** | name, email, status, roles |
| **Lead** | name, phone, email, gender, dob, status, product, bank |
| **Customer** | name, phone, id_no, gender, dob, status, loan_limit, has_loan |
| **Loan** | loan_amount, interest, processing_fee, loan_total, period, due_date, status |
| **Payment** | amount, payment_method, receipt_no, date_received, status |
| **Refinance** | amount, due_date, status |
| **Product** | rate (interest), frequency, rolls_over, is_active |
| **Bank** | payday, weekend_pushes, is_active |
| **BankBranch** | name, code |
| **Address** | street, town, county, postal_code |
| **AuditLog** | event, old_values, new_values, user, ip_address |

---

## Roles & Permissions

Access is managed via [Spatie Laravel Permission](https://spatie.be/docs/laravel-permission). Only active users with an assigned role may access the admin panel.

| Role | Access Level |
|---|---|
| **Super Admin** | Full access including system logs and all configuration |
| **Admin** | Full operational access |
| **Sales Agent** | Lead and customer management, loan origination |
| **Collection Agent** | Payment recording, overdue loan management |
| **Collection Officer** | Collections oversight and reporting |

---

## Business Logic

### Loan Status Workflow

```
pending_verification
  → pending_confirmation
  → pending_approval
  → pending_disbursement
  → disbursed
  → overdue / past_overdue
  → cleared / written_off / fraud / cancelled
```

### Financial Calculations

| Calculation | Formula |
|---|---|
| Monthly Interest | `product_rate × loan_amount` |
| Processing Fee | `loan_amount × 5%` |
| Monthly Installment | `monthly_principal + monthly_interest` |
| Loan Total | `monthly_installment × loan_period` |
| Loan Balance | `loan_total − sum(all payments)` |

### Due Date Logic

Due dates are calculated based on the bank's configured payday with adjustments for weekends (push-forward or push-backward depending on bank settings), ensuring repayment dates align with customer payroll cycles.

### Loan Auto-Classification

A loan is automatically classified as:
- **New Loan** — customer has no prior loans
- **Old Loan** — customer has existing loan history
- **Top-up** — manually designated

### Refinance Eligibility

```
eligible = (customer_loan_limit - current_loan_balance) >= 1000
refinance_amount = max(1000, customer_loan_limit - current_loan_balance)
```

### Payment Auto-Clearance

When a payment is recorded and the resulting loan balance reaches ≤ 0, the loan status is automatically set to `CLEARED` and the payment is marked `CLEARED` via the `PaymentObserver`.

### Lead Conversion

Converting a lead to a customer runs inside a database transaction:
1. Creates a new `Customer` record from lead data
2. Migrates the lead's address to the new customer
3. Updates the lead status to `converted` with a timestamp and reference to the created customer

---

## File Structure

```
app/
├── Enums/               # Status and type enums (CustomerStatus, LoanStatus, PaymentMethod, etc.)
├── Filament/
│   ├── Pages/           # Dashboard, Login
│   └── Resources/       # 14 Filament CRUD resources
├── Http/Controllers/    # Base controller
├── Models/              # 13 Eloquent models
├── Observers/           # Model event listeners (Loan, Payment, Refinance, Lead, User)
├── Policies/            # Authorization policies (10 files)
├── Providers/           # AppServiceProvider, AuthServiceProvider
└── Traits/              # Auditable trait

database/
├── factories/           # Model factories for testing
├── migrations/          # 20+ versioned migrations
└── seeders/             # Database seeders

resources/
├── css/                 # Tailwind styles
├── js/                  # Frontend JavaScript
└── views/               # Blade templates

routes/
├── web.php              # Web routes
└── console.php          # CLI commands

tests/                   # Pest test suite
```

---

## Testing

CR3D3NC3 uses [Pest PHP](https://pestphp.com) for testing.

```bash
# Run all tests
php artisan test

# Or using Pest directly
./vendor/bin/pest

# Run with coverage
./vendor/bin/pest --coverage
```

Static analysis:

```bash
./vendor/bin/phpstan analyse
```

Code style:

```bash
./vendor/bin/pint
```

---

## License

This project is proprietary software. All rights reserved.
=======
`Clean, symmetrical, professional`

CRM & Credit Lending Management System made with Laravel 12 and FilamentPHP 4
>>>>>>> Stashed changes
