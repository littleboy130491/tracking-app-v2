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

---

# Progress - Portal parity with reference cargo tracker

Goal: Customer portal answers "where is my box and what's next" like the reference, without cloning its UI.
Started: 2026-09-16 15:21

## Plan Checklist

- [x] Step 1: Timeline entry contract - DONE - 15:21
- [x] Step 2: Timeline builder service - DONE - 15:36
- [x] Step 3: Sailing + progress header on B/L and container pages - DONE - 15:42
- [x] Step 4: Dashboard latest columns + container search - DONE - 15:50
- [x] Step 5: UAT + activity log - DONE - 15:50

## Current Focus

Working on: none — plan complete
Next: none
Blocked: None

## Notes

- Chose activity_logs reuse (Option A) over a new events table: no schema change, auditable.
- ETA renders as the only estimate entry; everything else is actual.
- Dashboard latest columns are shipment-level (voyage + B/L-wide logs), not per-container max.

---

# Progress - Split Export/Import into separate shipment & container models

Goal: Give Export and Import their own shipment AND container models, tables, menus and permissions; drop five B/L fields.
Started: 2026-09-19 08:06
Design: plans/split-export-import.md (v2)

## Plan Checklist

- [x] Step 1: Design freeze - DONE - 08:06 - plans/split-export-import.md v2 (approved)
- [x] Step 2: Schema & models - DONE - 08:24 - 4 fresh tables, 4 models, 2 traits, 3 enums; migrate:fresh clean; temp test passed (14 assertions)
- [x] Step 3: Shared services - DONE - 08:30 - ActivityLogger + ShipmentTimeline reworked; ActivityLog/Attachment/HsCode/Company updated; milestone changes audited; temp test passed (6 tests / 29 assertions)
- [x] Step 4: Admin - DONE - 08:40 - 4 resources (forms/tables/pages/policies), shared builders, menus Export/Import/Master data, company RMs, audit viewer, NotesPanel; old resources deleted; temp test passed (5 tests / 34 assertions)
- [x] Step 5: Customer portal - DONE - 08:50 - tabs + 4 detail pages + routes; temp test passed (5 tests / 25 assertions)
- [x] Step 6: Switch-over - DONE - 08:57 - old models/enum/seeder deleted; seeders + tests rewritten; migrate:fresh --seed + full suite green (90 tests / 432 assertions)
- [x] Step 7: Docs & UAT - DONE - 09:05 - README, ERD, UAT, migration_plan banner, IMPORT.md five fields removed

## Current Focus

Working on: none — plan complete
Next: none
Blocked: None

## Final Summary (2026-09-19 09:05)

- Export and Import are separate shipment + container models, tables, forms, menus, policies and portal pages.
- Five fields removed everywhere (package_count, package_unit, terminal_name, loading_date, loading_destination).
- `migrate:fresh --seed` rebuilds the demo data (3 export + 2 import shipments); full test suite green.
- Manual verification: docs/UAT.md section 1.
- Post-plan tweak (09:08): Filament navigation regrouped to **Bill of Ladings** (Export, Import) and **Containers** (Export, Import); regression test added.
- Post-plan tweak (09:11): group order fixed in AdminPanelProvider — Bill of Ladings first, then Containers, CRM, Master data, Monitoring; rendered-sidebar order test added.
- Post-plan tweak (09:14): portal Export/Import tabs replaced by a **Type** dropdown in the filter row (like Company/Status).
- Post-plan tweak (09:17): removed the "Here are the shipments…" subtitle from the portal dashboard.
- Post-plan tweak (09:24): export Step 2 starts with B/L number → DO → AJU; the reference number was removed completely (schema, models, seeders, admin, portal, tests, docs) — the B/L number is now the shipment identifier.
- Post-plan tweak (09:26): HS codes field hidden on the export shipment form (import-only); export pivot kept for future use.
- Post-plan tweak (09:29): Pick up depot / Stuffing date / Stuffing destination moved from Shipping Details to the Containers tab, above the repeater (still shipment-level, still gated at Step 3).
- Post-plan tweak (09:32): Tracking position and Tracking position (url) laid out side by side (50/50) in the repeater and the standalone export container form.
- Post-plan tweak (09:36): removed Stuffing started at / Stuffing finished at from export (columns dropped, model/form/timeline/seeder/docs updated); stuffing_status stays.
- Post-plan tweak (09:39): Checking PEB & NPE field renamed Gate in port → **Port of loading** (column `gate_in_port_name` → `port_of_loading`); still defaults from the B/L and stays overridable.
- Post-plan tweak (09:42): export container **Status** + **Completed at** fields removed (repeater + standalone form); columns kept for the list/portal, import container forms keep the fields.
- Post-plan tweak (09:45): export shipment form drops **Departure date / ETA / Actual arrival / Status / Completed at** (columns kept — the engine still completes shipments and the portal still reads the sailing dates).
- Post-plan tweak (09:47): new **Status** tab on the export shipment form (after Containers) holding Status + Completed at; shared `ShipmentFields::statusTab()` builder so the import form can adopt it later.
- Post-plan tweak (09:51): export container stuffing field labelled **Stuffing status at Factory**.
- Post-plan tweak (09:54): stuffing status reduced to **On Process / Finished** (default On Process) — "Not Started" dropped from the enum, form and DB default.
- Post-plan tweak (09:57): **Stuffing date** is now date + time (column changed to timestamp, picker switched to DateTimePicker, seeder updated).
- Post-plan tweak (10:03): portal hides **draft** shipments (list + detail + container URLs 404, admins included); the milestone engine flips draft → in progress as soon as a shipment advances past Document received.
- Post-plan tweak (10:06): export container **Final checked** is a toggle instead of a checkbox.
- Post-plan tweak (2026-09-21 16:20): **AJU number** removed from the export shipment form only (import keeps it); `export_shipments.aju_number` column and model fillable kept for existing data. Portal never displayed AJU, so no portal change. Audit test updated to fill/assert `bl_number` only.
- Post-plan tweak (2026-09-21 13:57): Notes composer uses a real textarea with padding and a clear gap above **Add note** (the button no longer overlaps the field).
- Post-plan tweak (2026-09-21 14:05): Milestone stepper vertical padding increased (4/6px → 16/20px).
- Post-plan tweak (2026-09-21 14:26): Container list/form shipment pickers no longer 500 when a shipment has a null B/L number (Filament Select forbids a null option label).
- Post-plan tweak (2026-09-21 15:14): B/L tables list each container number as a clickable badge to the container edit page (export + import + company relation managers).

## Notes

- v2: containers split too (ExportContainer/ImportContainer); fresh-app approach — migrations/seeders rewritten, no data migration, `migrate:fresh --seed` is the workflow.
- Design defaults (unless vetoed): Export/Import menus with Shipments + Containers; explicit FK columns (export_/import_shipment_id, export_/import_container_id) on logs + curator; two milestone enums; portal tabs; HS codes move to a Master data group.
- Five fields (package_count, package_unit, terminal_name, loading_date, loading_destination) are not carried into the new tables; IMPORT.md gets updated in Step 7.
- BillOfLadingStatus renamed to ShipmentStatus; audit events become shipment_updated / container_updated.
- Test suite is red from Step 2 until Step 6 rewrites legacy tests; each phase has targeted checks.
- Step 2 done: dev DB rebuilt with `php artisan migrate:fresh` (empty, new schema).
- Step 3 done: ActivityLogger/ShipmentTimeline + ActivityLog/Attachment/HsCode/Company support all four models; moveToMilestone audits.
- Step 4 done: old admin resources/policies deleted early (kept the panel clean); menus are Export / Import / Master data / CRM / Monitoring.
- Remaining legacy: old portal Livewire components + views + routes (Step 5), old BillOfLading/Container models, BillOfLadingStatus enum, DemoShipmentSeeder, legacy tests (Step 6).
- Permissions: PermissionSeeder auto-discovers the four new resources; the dev DB is empty so `migrate:fresh --seed` in Step 6 regenerates everything.
- Post-plan tweak (2026-09-21 15:10): **Import process rebuilt to IMPORT.md** — milestone enum expanded to 22 steps (added Waiting confirmation from customer, Waiting release DO, Tambahan step SPJM, Waiting process bahandle, Waiting change status SPJM to SPPB, Container shipping schedule); fields re-gated per step (containers repeater now unlocks at Response billing, HS codes at Waiting process bahandle); non-spec import columns dropped (shipment_mode, do_number, payment statuses/timestamps, draft-PIB status/notes, actual_arrival_at, container type/seal/driver_license/inspection/factory_arrived_at); added `confirmation_checklist` (boolean) and container `tracking_position`/`tracking_position_input`; portal draft-PIB confirm now sets the checklist; seeder, tests and UAT updated; `migrate:fresh --seed` + full suite green (95 tests).

---

# Progress - CSV export + prune old data

Goal: CSV export (all fields incl. relationships) and a 3-year prune action on the seven main tables, admin/super_admin only.
Started: 2026-09-21 19:10
Status: COMPLETE

## Plan Checklist

- [x] Step 1: Install pxlrbt/filament-excel - DONE
- [x] Step 2: Shared full-field export columns (TableExportColumns) - DONE
- [x] Step 3: Prune service (OldDataPruner) + header action builder - DONE
- [x] Step 4: Wire both actions into all seven tables - DONE
- [x] Step 5: Permissions + tests + docs - DONE

## Current Focus

Working on: none — plan complete
Next: none
Blocked: None

## Notes

- Package: pxlrbt/filament-excel ^4.1 (MIT, maintained, wraps maatwebsite/excel); Filament v5 does not ship CSV export natively.
- Export uses explicit columns, not fromTable(), so every stored field and relation is included. Relation columns use getStateUsing() because data_get() cannot resolve HasMany (Arr::wrap(null) === []).
- Prune is a confirmation-gated header action: DangerAction semantics, count + cutoff in the dialog, forceDelete() so soft-deleted rows go too; children cascade.
- Visibility: User::canExportTables() / User::canPruneOldData() both return hasAnyRole(Role::PRIVILEGED) (admin + super_admin). The action re-checks in action() so a crafted request cannot bypass it.
- PermissionSeeder adds a baseline `Prune:LegacyData` permission (admins get it via RoleSeeder's all-permissions sync; operators are excluded).
- Tests: tests/Feature/Admin/TableExportAndPruneTest.php — action visibility per role on all 7 tables, prune cutoff/cascade, full column sets, real map() output. Full suite 128 passed.

---

# Progress - Shipment tables: Created/Updated instead of ETA

Goal: Show created_at + updated_at (not ETA) in the four admin shipment tables.
Started: 2026-09-21 20:05
Status: COMPLETE

- [x] B/L export + import tables: ETA -> Created + Updated (both visible) - DONE
- [x] Companies export/import relation managers: ETA -> Created + Updated - DONE
- [x] Tests: assert columns present/absent - DONE (29 pass)
- [x] Docs + activity log - DONE

Notes:
- Display-only change; eta_at stays in the DB, edit form, portal estimate line and CSV export.
- Relation managers are standalone Livewire components; test with ownerRecord + pageClass.

---

# Progress - Loading/Discharge columns on shipment tables

Goal: Show port_of_loading + port_of_discharge on the B/L tables and company relation managers.
Started: 2026-09-21 20:25
Status: COMPLETE

- [x] Add Loading/Discharge columns to export + import B/L tables - DONE
- [x] Add to companies export/import relation managers - DONE
- [x] Tests + docs - DONE (29 pass)

Notes:
- Display/search only; no schema change (columns already existed and were on the forms).
- Both columns searchable, placeholder '—', toggleable.
