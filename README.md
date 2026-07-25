# ITR Tax — Laravel ITR Filing Portal

Income tax e-filing **workspace** built with Laravel. Guides Self or Expert-assisted preparation, documents, regime comparison, payments, tracking, and acknowledgements. Official upload / e-verify / CPC refund remain on the Income Tax Department portal (this app does not replace ERI/ITD APIs without credentials).

Legacy PHP MVC reference code lives in `legacy/`.

## Requirements

- PHP 8.3+ with `pdo_mysql`, `openssl`, `curl`, `zip`
- Composer
- **MySQL / MariaDB** (portable MariaDB included under `tools/mariadb`)

## Quick start (Windows + MySQL)

```powershell
cd e:\mukesh-rawat\itr-filing
$env:PHPRC = "e:\mukesh-rawat\itr-filing"

# 1) Start MySQL/MariaDB (portable)
.\tools\start-mysql.bat

# 2) Create database + tables + demo users
php tools\setup-mysql.php
php artisan migrate:fresh --seed

# 3) Run MAIN portal (customers / tax experts)
php artisan serve --host=127.0.0.1 --port=8000

# 4) Run ADMIN portal (separate window) — admin ID / password only
php artisan serve --host=127.0.0.1 --port=8001
```

Or double-click `tools\start-both.bat` (starts main + admin), or `tools\start-main.bat` / `tools\start-admin.bat` separately.

- Main site: [http://127.0.0.1:8000](http://127.0.0.1:8000) — customer/expert login form only  
- Admin portal: [http://127.0.0.1:8001/login](http://127.0.0.1:8001/login) — admin ID `admin` / `admin@2026`

`.env` uses:

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=itr_tax
DB_USERNAME=root
DB_PASSWORD=
```

Optional Razorpay (Admin → Settings or `.env`):

```
RAZORPAY_KEY=
RAZORPAY_SECRET=
```

When both key and secret are set, expert checkout uses Razorpay. Empty = simulated demo payment.

Stop DB: `.\tools\stop-mysql.bat`

Coupons for demo payment: `SAVE10`, `FLAT500`

## Demo logins

| Role   | Login ID         | Password   |
|--------|------------------|------------|
| Admin  | admin            | admin@2026 |
| Expert | ca@itr-tax.in    | password   |
| Expert | ca2@itr-tax.in   | password   |
| User   | user@itr-tax.in  | password   |

## Features

- **Public site**: eFiling, pricing, tax / HRA calculators, rent receipt, refund/status lookup, blogs, FAQs, contact → support tickets
- **Auth**: register/login, forgot/reset password, soft email verification banner
- **User panel**: Self or expert filing, AIS vs Form 16 TDS check, cancel filing, Self→Expert upgrade, payment (demo or Razorpay), track, ACK + local e-verify mark
- **CA panel**: Assigned clients, docs, notes, mark filed, upload receipts
- **Admin panel**: Orders, payments, tax experts, users, settings (incl. Razorpay)
- **Separate admin portal** (default on): main site = customers/experts; admin only on `ADMIN_URL` (`ADMIN_PORTAL_SEPARATE=true`)

## Limitations (honest)

- Self “file” creates an **ITR Tax reference**, not an ITD acknowledgement, unless an expert uploads a real ACK.
- Refund status looks up **this app’s** filings; CPC refund processing is on the Income Tax portal.
- AIS reconcile is a **manual TDS amount compare**, not AIS JSON import.

## Tests

```powershell
$env:PHPRC = "e:\mukesh-rawat\itr-filing"
php artisan test
```

## Artisan commands

```powershell
$env:PHPRC = "e:\mukesh-rawat\itr-filing"
.\tools\start-mysql.bat
php tools\setup-mysql.php
php artisan migrate:fresh --seed
php artisan serve --host=0.0.0.0 --port=8000
php artisan route:list
```

> Always set `$env:PHPRC` before `artisan serve` so PHP workers load `pdo_mysql`.

## Project structure

```
app/
  Http/Controllers/   Home, Auth, User, Ca, Admin, PasswordReset, EmailVerification
  Support/            TaxCalculator, HraCalculator, PaymentGateway, AisReconcile, Portal
  Helpers/helpers.php
config/itr.php
database/migrations/
resources/views/
public/assets/
legacy/
```
