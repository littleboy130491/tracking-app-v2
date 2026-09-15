# Progress - Tracking App v2

Goal: Full shipment-tracking system (Filament admin + workflow engine + Livewire OTP customer portal) per spec.md / migration_plan.md / stack.md.
Started: 2026-09-15
Status: COMPLETE

## Plan Checklist

- [x] Phase 0: Install & configure packages - DONE
- [x] Phase 1: Migrations - DONE
- [x] Phase 2: Models & enums - DONE
- [x] Phase 3: Seeders - DONE
- [x] Phase 4: Filament admin CRUD - DONE
- [x] Phase 5: Workflow definition CRUD - DONE
- [x] Phase 6: Workflow execution engine - DONE
- [x] Phase 7: Customer portal - DONE
- [x] Phase 8: Mailgun, docs, tests - DONE

## Verification

- `php artisan test` -> 24 passed (70 assertions)
- `php artisan migrate:fresh --seed` -> all tables + demo data
- Live portal run: email -> signed verify (dev code shown) -> /portal -> "Hello, Dewi Customer" with both shipments
- Portal detail pages: B/L 200 (import page shows Draft PIB confirm/revision), container 200
- `npm run build` -> assets built; `./vendor/bin/pint` -> clean

## Notes / decisions

- Package substitution: spatie/laravel-one-time-passwords stable caps at Laravel 12, so
  benbjurstrom/otpz v0.7.0 (Laravel 13) is used for the passwordless portal login.
- otpz verification requires a *signed* request, so login/verify is a controller + Blade
  form on a signed URL; the portal dashboard itself is Livewire.
- Dev OTP display: App\Mail\DevOtpMail stashes the plaintext code in the session when
  OTPZ_EXPOSE_IN_DEV=true and the app is not in production.
- Roles: spatie/laravel-permission is the single source of truth; `roles.is_internal`
  added. admin = Shield super admin via Gate::before; operator seeded with all
  permissions except User/Role; customer has no panel access.
- `customer_user` M:N pivot replaces migration_plan's users.customer_id (spec.md needs M:N).
- Custom `activity_logs` model + ActivityLogger instead of spatie/laravel-activitylog
  (schema is fixed by migration_plan §10).
- Field validation semantics: `required_condition` = the field becomes required when the
  condition matches the context; `validation_rules` (e.g. {"in":["SPPB"]}) constrains the
  value. The SPJM -> SPPB gate uses `in`.
- Engine rules live in app/Services/Workflow; the admin stage screen is
  App\Filament\Pages\ShipmentWorkflow (reached from the B/L table "Workflow" action).

## Environment quirks encountered (sandbox only, not project bugs)

- The shell exports many .env values (APP_ENV, SESSION_DRIVER, DB_CONNECTION, NODE_ENV...),
  so phpunit.xml could not override them. Fixed by forcing the test environment in
  tests/TestCase::createApplication().
- NODE_ENV=production made `npm install` skip devDependencies; used `--include=dev`.

## Open items for the product owner

- Meaning of `No. License` and whether closing time / depot / location must be uniform
  across containers: stored as plain fields, business rules intentionally not assumed.
- AP and SPJK responses show "flow not configured" and do not auto-progress, per §12.
- Laravel Boost was NOT installed (it can rewrite AGENTS.md).
