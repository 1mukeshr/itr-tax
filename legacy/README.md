# ITR Tax — ClearTax-style ITR Filing Portal (PHP)

Complete assisted Income Tax e-filing portal inspired by **ClearTax**, with User, CA and Admin panels.

## Quick start

```bash
php -c php.ini database/install.php
php -c php.ini -S 0.0.0.0:8000 -t public public/router.php
```

Open http://localhost:8000

### Demo logins (password: `password`)

| Role  | Email             |
|-------|-------------------|
| Admin | admin@itr-tax.in  |
| CA    | ca@itr-tax.in     |
| User  | user@itr-tax.in   |

Coupon: `SAVE10` · `FLAT500`

## Public pages (ClearTax-style)

| Page | URL |
|------|-----|
| Home | `/` |
| eFiling | `/efiling` |
| How it works | `/how-it-works` |
| Pricing | `/pricing` |
| Tax Calculator | `/tax-calculator` |
| Tools | `/tools` |
| Refund status | `/refund-status` |
| Guides | `/blogs` |
| FAQs | `/faqs` |
| Support | `/contact` |
| About | `/about` |
| Privacy / Terms | `/privacy` · `/terms` |

## Filing flow

**Self:** Start → Upload Form 16 → Tax summary → Review & File → ACK  

**Expert:** Start + plan → Docs → Summary → Pay → CA assign → Review/File → ACK  

## Stack

Custom PHP MVC · SQLite · Sessions · CSRF · Role middleware · `itr-*` design system · SVG icons
