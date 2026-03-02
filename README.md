# LGU Queue System – Municipality of Manolo Fortich

A QR code–based queueing system for the offices of the Municipality of Manolo Fortich, built with **Laravel** and **Livewire**.

## Features

- **QR code per office** – Each office has a unique QR code. Clients scan to get a queue number for that office only.
- **Queue Master dashboard** – Manage all offices, generate/print QR codes, monitor active queues, reset numbering per office.
- **Office Admin (per office)** – Dedicated dashboard per office (e.g. MISO, LDRRMO, HRMO). Call next number, mark completed, view waiting list.
- **Role-based access** – Super Admin, Queue Master, Office Admin with clear permissions.
- **Independent queues** – Queue numbers and flow are separate per office.

## Requirements

- PHP 8.2+
- Composer
- Node.js & npm (for Vite)
- SQLite (default) or MySQL/PostgreSQL

## Installation

```bash
cd manolo-fortich-queue
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed
npm install && npm run build
```

For local development with hot reload:

```bash
npm run dev
# In another terminal:
php artisan serve
```

Set `APP_URL` in `.env` to your base URL (e.g. `http://localhost:8000`) so QR codes and links work correctly.

## Default accounts (password: `password`)

| Role         | Email                          | Access                          |
|-------------|----------------------------------|----------------------------------|
| Super Admin | admin@manolofortich.gov.ph      | Full access, Queue Master area  |
| Queue Master| queuemaster@manolofortich.gov.ph| Queue Master dashboard, all offices |
| Office Admin (MISO) | miso@manolofortich.gov.ph  | MISO office queue only          |
| Office Admin (HRMO) | hrmo@manolofortich.gov.ph   | HRMO office queue only          |

**Change these passwords in production.**

## Routes

- `/` – Welcome (or redirect to dashboard if logged in)
- `/login` – Staff login
- `/dashboard` – Redirects by role (Queue Master or Office dashboard)
- `/queue-master` – Queue Master dashboard (Super Admin / Queue Master)
- `/queue-master/office/{slug}` – Manage office, view/print QR code
- `/office/{slug}` – Office Admin queue dashboard (for that office only)
- `/queue/join/{office}` – Public: get queue number (used when client scans QR or opens link)

## Seeded offices

MISO, LDRRMO, HRMO, Mayor's Office, Treasury, Accounting, Budget, Civil Registry, Business Permits, Engineering.

You can add more offices via the database or a future admin UI.

## License

MIT.
