# ITR Tax — Laravel ITR Filing Portal

Income tax e-filing **workspace** built with Laravel. Guides Self or Expert-assisted preparation, documents, regime comparison, payments, tracking, and acknowledgements.

Official upload / e-verify / CPC refund remain on the Income Tax Department portal. This app does not replace ERI/ITD APIs without credentials.

**Repository:** [github.com/1mukeshr/itr-tax](https://github.com/1mukeshr/itr-tax)  
**Project page:** [1mukeshr.github.io/itr-tax](https://1mukeshr.github.io/itr-tax/) (static landing only — not the live Laravel app)

> GitHub Pages cannot run PHP/MySQL. Use the Quick start below to run the real portal locally on ports `8000` / `8001`.

---

## Tech stack

| Layer | Technology |
|-------|------------|
| Backend | **PHP 8.3+**, **Laravel 13** |
| Frontend | **Blade** templates, custom CSS/JS (`public/assets`) |
| Database | **MySQL / MariaDB** (portable MariaDB under `tools/mariadb`) |
| Auth / sessions | Laravel session auth (Admin · User · Tax Expert roles) |
| Payments | Demo checkout + optional **Razorpay** |
| Tooling | Composer, Artisan, PHPUnit, Laravel Pint |
| Optional scaffold | Vite 8 + Tailwind 4 (not required for runtime UI) |
| OS (dev) | Windows PowerShell (included start/stop scripts) |

---

## Requirements

### Runtime

| Requirement | Details |
|-------------|---------|
| **PHP** | `8.3+` (project tested on PHP 8.3) |
| **PHP extensions** | `pdo_mysql`, `mysqli`, `openssl`, `curl`, `zip`, `mbstring`, `fileinfo`, `intl` |
| **Composer** | 2.x |
| **Database** | MySQL 8+ or MariaDB 10.5+ |
| **Web server** | `php artisan serve` for local, or Apache/Nginx pointing at `public/` |

### Optional

| Requirement | When needed |
|-------------|-------------|
| **Node.js + npm** | Only if you run Vite (`npm run build` / `composer setup`) |
| **Razorpay keys** | Live expert payments (`RAZORPAY_KEY` + `RAZORPAY_SECRET`) |

### Not supported on GitHub Pages

GitHub Pages is **static only**. This project needs PHP + MySQL, so the live app must run on your machine or a PHP host (not `*.github.io`).

---

## Quick start (Windows + MySQL)

From the project root:

```powershell
$env:PHPRC = (Get-Location).Path

# 1) Start MySQL/MariaDB (portable)
.\tools\start-mysql.bat

# 2) Create database + tables + demo users
php tools\setup-mysql.php
php artisan migrate:fresh --seed

# 3) Run MAIN portal (customers / tax experts)
php artisan serve --host=127.0.0.1 --port=8000

# 4) Run ADMIN portal (separate window)
php artisan serve --host=127.0.0.1 --port=8001
```

Or use `tools\start-both.bat` (main + admin), or `tools\start-main.bat` / `tools\start-admin.bat`.

| Portal | URL |
|--------|-----|
| Main site | http://127.0.0.1:8000 |
| Admin | http://127.0.0.1:8001/login |

Stop database: `.\tools\stop-mysql.bat`

> Always set `$env:PHPRC` to the project root before `artisan` / `serve` so PHP loads `pdo_mysql` from this project's `php.ini`.

---

## Database (`.env`)

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=itr_tax
DB_USERNAME=root
DB_PASSWORD=
```

Check connection:

```powershell
$env:PHPRC = (Get-Location).Path
php artisan db:show
php artisan migrate:status
```

Optional Razorpay (Admin → Settings or `.env`):

```
RAZORPAY_KEY=
RAZORPAY_SECRET=
```

When both are set, expert checkout uses Razorpay. Empty = simulated demo payment.

Demo coupons: `SAVE10`, `FLAT500`

---

## Demo logins

| Role   | Login ID         | Password   |
|--------|------------------|------------|
| Admin  | admin            | admin@2026 |
| Expert | ca@itr-tax.in    | password   |
| Expert | ca2@itr-tax.in   | password   |
| User   | user@itr-tax.in  | password   |

---

## Features

- **Public site** — eFiling, pricing, tax / HRA calculators, rent receipt, refund lookup, blogs, FAQs, contact
- **Auth** — register/login, forgot/reset password, email verification banner
- **User panel** — Self or expert filing, documents, regime summary, payment, track, ACK + e-verify tips
- **CA panel** — Assigned clients, docs, notes, mark filed, upload receipts
- **Admin panel** — Orders, payments, tax experts, users, settings
- **Separate admin portal** — customers/experts on main; admin on `ADMIN_URL` when `ADMIN_PORTAL_SEPARATE=true`

---

## Limitations

- Self “file” creates an **ITR Tax reference**, not an ITD acknowledgement, unless an expert uploads a real ACK.
- Refund status looks up **this app’s** filings; CPC refund processing is on the Income Tax portal.
- AIS reconcile is a **manual TDS amount compare**, not AIS JSON import.

---

## Tests

```powershell
$env:PHPRC = (Get-Location).Path
php artisan test
```

---

## Project structure

```
app/
  Http/Controllers/   Home, Auth, User, Ca, Admin, PasswordReset, EmailVerification
  Models/             Filings, users, ProcessStep, plans, payments, …
  Support/            TaxCalculator, HraCalculator, PaymentGateway, AisReconcile, Portal
  Helpers/helpers.php
config/itr.php
database/migrations/
resources/views/
public/assets/
tools/                MySQL start/stop + serve helpers
```
