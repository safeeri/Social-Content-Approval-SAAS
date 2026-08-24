# Socvial — Social Content Approval SaaS

A multi-tenant Laravel platform where agencies draft social media posts, route them through
internal QA, and let clients approve or reject them on a shared content calendar.

**Stack:** Laravel 12 · MySQL · Pure Blade + Bootstrap 5 + vanilla JS (Select2, Cleave.js, FullCalendar)

---

## Features

- **Multi-tenancy** — every record is scoped through `companies` (agency tenants). Clients are
  external customers attached to a tenant; all queries are strictly role-scoped.
- **RBAC** — `saas_admin`, `company_admin`, `company_manager`, `company_approver`, `client`
  enforced by the `role:` middleware (`app/Http/Middleware/EnsureRole.php`).
- **Approval workflow**

  ```
  draft ──submit──▶ internal_review ──sign off──▶ pending_approval ──client approves──▶ approved
     ▲                                        │                    │
     └────────── back to draft ◀── internal reject                 ▼ client rejects (comment required)
                                                               rejected → returns to agency queue
  ```

- **Email notifications** — client notified on `pending_approval`; managers notified on
  `approved` / `rejected` (`app/Mail/PostStatusChanged.php`, log driver by default).
- **Calendar UI** — FullCalendar with a status filter bar (Draft / Internal Review /
  Pending Approval / Approved / Rejected), month grid on desktop and agenda list on mobile.
- **Right-side drawer** — click any event to preview media, caption and take action;
  "Expand" opens a full-screen modal for hi-res video playback and long captions.
- **Media** — direct file uploads or Google Drive links. The storage disk used for each file
  is recorded in the `media.disk` column (`local`, `s3`, or `r2`).
- **Timezones** — everything is stored in UTC; dates render in each user's timezone in Blade
  views and FullCalendar events (`app/Support/TimeZone.php`).
- **Soft deletes** on `companies`, `clients`, `users`, `posts`.
- **Form Requests** for every submission; controllers never validate inline.

## Requirements

- PHP ≥ 8.2, Composer
- MySQL
- XAMPP works fine out of the box

## Setup

```bash
composer install
cp .env.example .env            # then set DB_DATABASE etc.
php artisan key:generate

# create the database
mysql -u root -e "CREATE DATABASE IF NOT EXISTS socvial CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"

php artisan migrate --seed      # demo data included
php artisan serve               # http://127.0.0.1:8000
```

For cloud storage set `FILESYSTEM_DISK=s3` or `FILESYSTEM_DISK=r2` plus the matching
credentials in `.env` (`R2_*` keys for Cloudflare R2) and run:

```bash
composer require league/flysystem-aws-s3-v3
```

## Demo logins (password: `password`)

| Role             | Email                  | Sees                          |
|------------------|------------------------|-------------------------------|
| SaaS Admin       | `admin@socvial.com`    | Companies & platforms admin   |
| Company Admin    | `admin@acme.com`       | Clients & team management     |
| Company Manager  | `manager@acme.com`     | Calendar, drafts, submissions |
| Company Approver | `approver@acme.com`    | Internal review queue         |
| Client           | `client@bellavista.com`| Own calendar only, approve/reject |

## Project layout

```
app/
├── Http/
│   ├── Controllers/{Saas,Company}/   # per-role controllers
│   ├── Middleware/EnsureRole.php     # RBAC + tenant guard
│   └── Requests/                     # strict validation layer
├── Mail/PostStatusChanged.php        # approval-loop emails
├── Models/                           # Company, Client, User, Post, Media, ...
└── Support/TimeZone.php              # UTC ⇄ user-timezone helpers
resources/views/
├── calendar/index.blade.php          # FullCalendar + filter pills
├── posts/partials/drawer-body.blade.php  # drawer preview + actions
└── layouts/app.blade.php             # black sidebar / yellow accents shell
```
