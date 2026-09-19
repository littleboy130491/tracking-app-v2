# SAM Group Logistics Tracking System

<p align="center">
    <img src="assets/logo.png" alt="SAM Group Logistics Tracking System" width="320">
</p>

A shipment tracking system built around **one shipment → many containers**, with an admin dashboard for
staff and a passwordless customer portal. Export and Import are **separate models** with their own tables,
forms and menus.

Shipment tracking is hardcoded per process: an export shipment walks `ExportMilestone` (booking order →
pickup → stuffing → gate in → final checking), an import shipment walks `ImportMilestone` (document
checking → draft PIB → billing/THC/DO → behandle → inspection → gate out → factory → empty return; the
SPJM branch runs only when `billing_response` is SPJM). Each milestone's fields sit in its own section,
locked until that step is reached, with a click-to-jump stepper and advance/regress buttons.

## What it is

- **Admin dashboard** (`/admin`) — menus are **Bill of Ladings** (Export, Import) and **Containers**
  (Export, Import), plus **CRM** (users, companies), **Master data** (HS codes) and **Monitoring**
  (activity log). Containers are edited inline on the shipment form and also standalone; HS codes are shared
  master data the import shipment form multi-selects and can create inline. The activity log is **read-only**
  and records every change, who made it and when.
- **Customer portal** (`/portal`) — passwordless sign-in with an emailed one-time code, greeting, a **Type**
  dropdown (Export / Import) beside the filters (company / number / status / year / month), the shipments of
  the companies the user manages, per-process detail pages with containers opening in a new tab, and
  draft-PIB confirmation for imports — once a draft is confirmed the confirm and revision actions are no
  longer offered.

## Tech stack

| Concern | Choice |
| --- | --- |
| Framework | Laravel 13 (PHP 8.3+) |
| Admin UI | Filament 5 + Filament Shield (`spatie/laravel-permission`) |
| Customer portal | Livewire 4 + Tailwind CSS 4 (Roboto, brand `#499bff`) |
| Passwordless login | `benbjurstrom/otpz` (session-locked, rate-limited OTP) |
| Attachments / media | Filament Curator (`awcodes/filament-curator`) |
| Database | SQLite for development (migrations kept portable) |
| Mail | Mailgun in production, `log` driver in development |

## Requirements

- PHP **8.3+** with the usual Laravel extensions
- Composer 2
- Node.js 20+ and npm

## Installation

```sh
composer install
cp .env.example .env
php artisan key:generate

# SQLite is the default connection; create the file if it is missing.
touch database/database.sqlite

php artisan migrate:fresh --seed
php artisan curator:token   # writes CURATOR_GLIDE_TOKEN for the media picker

npm install
npm run build

php artisan serve
```

The app is then available at <http://localhost:8000> (whatever `APP_URL` is set to).

`composer setup` performs the install, migrate and build steps in one command, but it does not seed — run
`php artisan db:seed` afterwards if you use it. It also installs npm packages without dev dependencies.

After adding a new Filament resource, generate its policy and register the new permissions:

```sh
php artisan shield:generate --all --panel=admin
php artisan db:seed --class=PermissionSeeder
```

## Configuration

Everything lives in `.env`. The settings that matter day to day:

| Variable | Purpose |
| --- | --- |
| `APP_NAME` | Shown in both dashboards and in emails. |
| `APP_URL` | Base URL used by `asset()` and signed links; must match how you serve the app. |
| `DB_CONNECTION` / `DB_DATABASE` | `sqlite` + `database/database.sqlite` by default. |
| `MAIL_MAILER` | `log` in development; set to `mailgun` in production. |
| `MAILGUN_DOMAIN`, `MAILGUN_SECRET`, `MAILGUN_ENDPOINT` | Mailgun credentials; also set `MAIL_FROM_ADDRESS`. |
| `OTPZ_EXPOSE_IN_DEV` | When `true` (and not in production) the one-time code is **shown on the verify screen** as well as emailed. Convenient locally; keep `false` in production. |
| `OTPZ_MAX_ATTEMPTS` | How many incorrect codes are allowed before the code is invalidated, and how many may be guessed per IP before that IP is locked out. Default `8`. |
| `OTPZ_ATTEMPT_DECAY_MINUTES` | How long the per-IP lockout lasts after too many incorrect codes. Default `10`. |
| `OTPZ_EXPIRATION` | How long a one-time code stays valid, in minutes. Default `5`. |
| `CURATOR_DEFAULT_DISK` | Disk Curator stores and serves attachments from. `public` by default; run `php artisan storage:link` once. |
| `CURATOR_GLIDE_TOKEN` | Signs Curator's image URLs. Generate with `php artisan curator:token`. |
| `APP_ENV`, `APP_DEBUG` | Standard Laravel flags. |

Portal mail uses whatever `MAIL_MAILER` you configure. In development the code also lands in
`storage/logs/laravel.log`, so you never need `OTPZ_EXPOSE_IN_DEV` — it is just faster.

## Demo accounts

Created by the seeders. **Change or remove these before going live.**

Staff — sign in at `/admin` with the password `password`:

| Email | Role | Access |
| --- | --- | --- |
| `superadmin@example.com` | super admin | Everything, bypasses every gate, and is the only role that can create or edit admins. |
| `admin@example.com` | admin | Everything except managing admin accounts. |
| `operator@example.com` | operator | Shipments only: no company/user/role management, no deleting (incl. restore/force-delete). |

New users created in the panel default to the **customer** role, and only a super admin is offered the admin
and super admin roles — the rule lives in `App\Support\Authorization\AssignableRoles`, and Filament rejects a
submitted role that was not on offer, so it is enforced server-side rather than by hiding an option.

Customer portal — sign in at `/login` with a one-time code (no password):

| Email | Name | Companies they manage |
| --- | --- | --- |
| `customer@example.com` | Dewi Customer | NUS, SIN, BJM |
| `buyer@sinar.test` | Budi Buyer | SIN |
| `rina@nusantara.test` | Rina Hartono | NUS, JRD |
| `agus@borneo.test` | Agus Pratama | BJM, SNI, JRD |
| `sari@java-retail.test` | Sari Wijaya | JRD |

The companies and portal users are deliberately **many-to-many** (spec.md): a user can manage several
companies and a company can be managed by several users. **PT Java Retail Distribution** is handled by three
users (Rina, Agus, Sari), while **Dewi** handles three companies. Whatever their setup, a user only ever
sees the shipments of the companies they manage.

Portal users have no password: enter the email address on `/login`, then use the code from the email. Only
addresses belonging to an active customer account are accepted — there is no self-registration. Codes are
rate limited: `OTPZ_MAX_ATTEMPTS` (default 8) wrong codes invalidate the code and lock that IP out for
`OTPZ_ATTEMPT_DECAY_MINUTES` (default 10); the form explains the wait instead of throwing an error page.

## Demo data

Five companies — **PT Nusantara Ekspor** (`NUS`), **PT Sinar Impor** (`SIN`),
**CV Borneo Jaya Mandiri** (`BJM`), **PT Sulawesi Nickel Industri** (`SNI`) and
**PT Java Retail Distribution** (`JRD`).

| B/L number | Menu | Company | State |
| --- | --- | --- | --- |
| `BL-EXP-0001` | Bill of Ladings → Export | NUS | In progress, 2 containers |
| `BL-EXP-0002` | Bill of Ladings → Export | BJM | In progress, 1 container |
| `BL-EXP-0003` | Bill of Ladings → Export | SNI | **Completed** |
| `BL-IMP-0001` | Bill of Ladings → Import | SIN | In progress. `billing_response = SPJM`, on the behandle branch |
| `BL-IMP-0002` | Bill of Ladings → Import | JRD | **Completed** — it ran the whole SPJM → SPPB path |

The two completed shipments are seeded with their progress fields filled, so there are finished export and
import examples to open in the admin panel and the portal. The seeders
(`DemoExportShipmentSeeder`, `DemoImportShipmentSeeder`) are idempotent: re-running never rewinds a live
shipment.

## Key routes

| Route | Who | What |
| --- | --- | --- |
| `/` | anyone | Redirects to `/portal` for customers, otherwise to `/admin` (guests to `/login`). |
| `/admin` | admin, operator | Filament admin panel. |
| `/login` | guests | Portal sign-in (email step). |
| `/portal` | customers | Shipment dashboard with a Type (Export/Import) dropdown and filters. |
| `/portal/export-shipments/{id}` | customers | Export shipment detail + journey + containers. |
| `/portal/import-shipments/{id}` | customers | Import shipment detail; draft-PIB confirmation (hidden once confirmed). |
| `/portal/export-containers/{id}` | customers | Export container detail (facts + journey). |
| `/portal/import-containers/{id}` | customers | Import container detail (facts + journey). |

## How shipment tracking works

- **Two models**: `ExportShipment` and `ImportShipment` (tables `export_shipments` / `import_shipments`),
  each with its own form, table, menu, policy and milestone enum. Containers are split the same way
  (`ExportContainer` / `ImportContainer`).
- Each shipment form has a **Customer** section above the tabs, then **Shipping Details** (booking/document
  fields, each locked until its milestone), **Containers** (a repeater — one collapsible item per container),
  **Notes** and **Activity log**.
- Shared machinery uses explicit links: activity logs and Curator media carry
  `export_/import_shipment_id` + `export_/import_container_id`; HS codes link through two pivots; notes are
  polymorphic. `App\Services\ActivityLogger` and `App\Services\ShipmentTimeline` serve both processes.
- `status` / `completed_at` are managed manually for now; reaching the last milestone completes the shipment
  automatically.

## Testing and code style

```sh
php artisan test     # feature tests covering the panel, the seeders and the portal
./vendor/bin/pint    # Laravel code style
```

## Notes and gotchas

- **UUID generation:** `Str::orderedUuid()` crashes the PHP process (SIGILL) on this environment — ramsey's
  `CombGenerator`. Filament calls it for every notification id, so the admin panel could not save anything.
  `AppServiceProvider` installs a v7-shaped generator built from `random_bytes` instead.
- **npm dev dependencies:** if your shell exports `NODE_ENV=production`, `npm install` skips dev
  dependencies and `npm run build` fails with `vite: not found`. Use `npm install --include=dev`.
- **Curator token:** the media picker needs `CURATOR_GLIDE_TOKEN`; run `php artisan curator:token` once.
- **Branding:** `style.md` holds the tone (`#499bff`) and font (Roboto) — defined for the portal in
  `resources/css/app.css` and for the admin panel in `AdminPanelProvider`. The mark lives at
  `assets/logo.png` and is published to `public/images/logo.png` for web serving.
- **Activity logs are append-only:** the admin resource deliberately offers no create, edit or delete action.
