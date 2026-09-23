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

---

# Progress - Portal container accordions

Goal: Show each container's applicable admin fields inline on the bill of lading page.
Started: 2026-09-23 12:20
Status: COMPLETE

## Plan Checklist

- [x] Step 1: Eager-load customer-visible attachments and import HS codes - DONE - 12:20
  - [x] Import query loads visible attachments and HS codes
  - [x] Export query loads visible attachments
- [x] Step 2: Replace container links with expandable accordion sections - DONE - 12:35
  - [x] Convert the shared list to keyboard-accessible native accordions
  - [x] Remove obsolete route props from both shipment page includes
    - [x] Import include
    - [x] Export include
  - [x] Add portal UAT checks for expand/collapse and same-page behavior
  - [x] Add a portal feature test for import/export accordion markup and removed links
- [x] Step 3: Render the matching import/export admin fields - DONE - 12:50
  - [x] Mirror each type's Filament groups and field labels in the expanded panel
  - [x] Show only customer-visible photo attachments in their matching slots
  - [x] Add regression coverage for type-specific fields and hidden internal photos
  - [x] Update the portal UAT checklist for full field parity
- [x] Step 4: Update UAT and verify portal behavior - DONE - 13:03

## Current Focus

Working on: none — plan complete
Next: none
Blocked: None

## Verification

- `php artisan test --filter=PortalTest` - 38 passed (159 assertions).
- PHP syntax checks passed for both shipment detail components.
- After Step 2, `php artisan test --filter=PortalTest` - 39 passed (172 assertions).
- After Step 3, `php artisan test --filter=PortalTest` - 40 passed (234 assertions).
- `php artisan test` - 155 passed (868 assertions).
- `npm run build` passed; Vite noted the optional `fontaine` package is not installed.
- `git diff --check` passed.

## Final Summary (2026-09-23 13:03)

- Replaced standalone container detail links with same-page, keyboard-accessible accordions.
- Expanded sections show Import/Export fields matching their Filament admin forms, with customer-visible photos only.
- Automated tests, production frontend build, and whitespace checks passed; manual UAT remains for the user.

## Notes

- Keep separate Import and Export field sets aligned to their Filament forms.
- Show only customer-visible attachments; keep internal Notes out of the portal.
- Follow the project workflow: complete one approved step, then pause for feedback.

---

# Progress - Portal container summary + progress steps

Goal: Redesign the portal Containers section into a summary card plus a step-by-step container journey per B/L milestone.
Started: 2026-09-23 13:31

## Plan Checklist

- [x] Step 1: Backend — isContainerStep() enums, ContainerProgress DTO, forContainers(), component wiring - DONE - 13:33
- [x] Step 2: Clickable container row (status pill, state text, mini progress bar) - DONE - 13:36
- [x] Step 3: Section 1 container summary (tiles, Track live, photos, last update) - DONE - 13:36
- [x] Step 4: Section 2 container progress stepper (container-steps partial) - DONE - 13:36
- [x] Step 5: PortalTest updates + new tests, UAT rewrite - DONE - 13:45

## Current Focus

All steps complete. Verified: pint clean, PortalTest 46/46, npm build ok, full suite 161/161.
Screenshots saved under /tmp/container-ui/ (export, import, import-done at 1280px + 390px).
Blocked: None

## Final Summary

Container rows are native `<details>` accordions with status pill, "Now/Next/Not started/Journey complete" state text and a mini progress bar. Expanded body = summary tiles + photos + last update, then a done/current/upcoming stepper fed by ShipmentTimeline::forContainers() (steps follow the B/L milestone, fields hidden while pending, http/https-only tracking links). PortalTest covers step states, SPJM branch, completion, not-started, URL safety and cancelled containers; docs/UAT.md section 0 rewritten.

---

# Progress - Remove unused code/data + reference adoption

Goal: delete unused portal container pages/columns/pivots, reseed, and adopt the reference layout (sailing card, latest-step rows, POD column).
Started: 2026-09-23 17:10

## Plan Checklist

- [x] Step 1: Remove unused code — container pages/routes, dead timeline methods, ShipmentMilestone/ShipmentType enums, welcome view, PortalTest trims - DONE - 17:10
- [x] Step 2: Drop created_by/updated_by/latest_event/is_customer_visible/export HS pivot/export container status+completed; seeder gating cleanup; migrate:fresh --seed OK; ERD/README updated - DONE - 17:45
- [x] Step 3: latestReached()/sailingInformation() helpers, sailing card, Latest-step row text + gated chips, summary Status/Completed tiles, dashboard POD column - DONE - 17:40
- [x] Step 4: UAT rewrite, activity log, pint clean, narrow set 136/136 (512M — see note), tests updated - DONE - 17:49

## Current Focus

Working on: none — plan complete
Next: None
Blocked: None
Note: the prescribed narrow-set order OOMs at the default 128M CLI limit
(AdminPanelSmokeTest runs last and memory accumulates); same set is green via
`php -d memory_limit=512M vendor/bin/phpunit`.

## Final Summary (2026-09-23 17:56)

- Step 1 deleted the standalone container pages/routes, dead ShipmentTimeline
  methods, ShipmentMilestone/ShipmentType enums and welcome.blade.php.
- Step 2 dropped created_by/updated_by, latest_event/latest_event_at,
  export_shipment_hs_code, export_containers status+completed_at and
  activity_logs.is_customer_visible (attachments keep their own flag); seeders
  now respect admin milestone gating; `migrate:fresh --seed` succeeded.
- Step 3 added `latestReached()`/`sailingInformation()` on ShipmentTimeline,
  a gated sailing card, "Latest: {title}" rows with seal/weight chips, summary
  Status/Completed tiles and a dashboard "POD / Vessel arrival" column.
- Step 4: UAT rewritten, Pint clean, narrow set 136 tests/762 assertions green
  (512M phpunit), full suite 167 tests/966 assertions green, `npm run build`
  OK, stale-reference grep clean except attachment is_customer_visible.
- Screenshots saved under /tmp/cleanup-ui/ (export-0005, import-0003,
  dashboard at 1280px and 390px).

### Follow-up (2026-09-23 18:18)

- `DemoActivityLogSeeder` now logs only transitions (steps 2..n; marker on
  the first written log, step-1 shipments skipped) and aligns seeded dates
  to the trail: document_received_date = first step's date, a completed
  shipment's completed_at = last step's time, and filled container
  timestamps (gate out/in CY, empty returned, final checked, container
  completed_at) move to their step's time — never invented. Writes use
  forceFill + saveQuietly.
- `ShipmentTimeline::containerSummaryFields()` gates every tile on the B/L
  milestone that unlocks it (export size/seal at Pickup empty container;
  import cargo tiles at Response billing, Completed at at Empty returned).
- New PortalTest case covers locked vs unlocked summary tiles; narrow set
  55/355 and full suite 168/978 green. Fix along the way: write
  document_received_date as a date string — a datetime in the `date` column
  made the field look dirty on the next admin save.
- UAT top section re-checked: still holds (no date-dependent wording broke;
  ETA expectations come from untouched sailing fields).

---

# Progress - Import tracking URL and field placement

Goal: Rename the import manual tracking input to a validated URL and move cargo fields to the Response billing step.
Started: 2026-09-21 17:59
Status: COMPLETE

## Plan Checklist

- [x] Step 1: Finish required Laravel Boost setup - DONE
- [x] Step 2: Rename tracking_position_input to tracking_position_url - DONE
- [x] Step 3: Move shipment Description of goods, Packages, HS codes to Response billing (Step 11) - DONE
- [x] Step 4: Move container Size to Response billing; add container Description of goods, Packages and HS codes with shipment defaults - DONE
- [x] Step 5: Add regression tests for URL validation and field placement - DONE
- [x] Step 6: Update IMPORT.md, ERD, UAT, recommendation and logs - DONE
- [x] Step 7: Run Pint and targeted tests; review the final diff - DONE

## Current Focus

Working on: Final verification
Next: None
Blocked: None

## Notes

- Import keeps `tracking_position`; `tracking_position_input` becomes `tracking_position_url` with Export-equivalent URL validation.
- User decision: Description of goods, Packages and HS codes unlock at Response billing (Step 11) on the shipment form.
- User decision: Container Size unlocks at Response billing; each container gets its own Description of goods, Packages and multiple HS codes that default from the shipment and can be overridden.
- Step 1 used composer require + `php artisan boost:install --no-interaction`; Boost added guidelines/skills/MCP config.
- Implementation complete: 30 AdminPanelSmokeTest tests pass (165 assertions); the add-item seeding hook lives in ShipmentFields::containersTab().

---

# Progress - Admin list date filters (Year / Month / date range)

Goal: Add Year, Month and From/Until date-range filters on `created_at` to the four admin lists plus the two company shipment relation managers.
Started: 2026-09-22 10:55
Status: COMPLETE

## Plan Checklist

- [x] Step 1: Create the DateFilters interface skeleton - DONE - 10:58
- [x] Step 2: Implement the three filters in DateFilters - DONE - 11:01
- [x] Step 3: Wire into the two B/L tables + UAT - DONE - 11:01
- [x] Step 4: Wire into the two container tables + UAT - DONE - 11:01
- [x] Step 5: Wire into the two company relation managers + UAT - DONE - 11:01
- [x] Step 6: Feature test TableDateFiltersTest + Pint - DONE - 11:10
- [x] Step 7: Wrap up logs and verify - DONE - 11:10

## Current Focus

Working on: none — plan complete
Next: none
Blocked: None

## Final Summary (2026-09-22 11:10)

- `DateFilters::make($modelClass)` returns the Year, Month and From/Until filters; wired into the 4 admin lists + 2 company relation managers (6 call sites).
- `tests/Feature/Admin/TableDateFiltersTest.php`: 6 tests / 32 assertions, all passing; Pint clean.
- Full suite: 144 passed (727 assertions). The first full run failed only because a concurrent session was mid-edit on the export `goods_description` removal; re-run after it settled was green.
- UAT checklist added as section 0 in docs/UAT.md (10 items, incl. a tinker one-liner to backdate a B/L for two-year testing).

## Notes

- User decisions: admin panel only (the portal already has Year/Month) and filter on `created_at`.
- User addition after plan approval: the Company -> Shipments relation managers get the same filters too.
- Six call sites: Import/Export shipment tables, Import/Export container tables, Import/Export company relation managers.
- Filter keys `created_year`, `created_month`, `created_between`; the range filter uses `whereDate` on both ends.
- Filters combine with AND; Year options come from the model's own table (not company-scoped).

---

# Progress - Import cargo fields onto the Containers tab

Goal: Move the import shipment's Description of goods, Packages and HS codes from Shipping Details to the Containers tab, above the repeater.
Started: 2026-09-22 11:18
Status: COMPLETE

## Plan Checklist

- [x] Step 1: Move the trio in ImportShipmentForm to the Containers tab header - DONE - 11:22
- [x] Step 2: Verify AdminPanelSmokeTest - DONE - 11:22 (30 pass)
- [x] Step 3: Update docs/UAT.md + check IMPORT.md - DONE - 11:23
- [x] Step 4: Pint + full suite + logs - DONE - 11:24

## Notes

- Data stays on the shipment (no schema change); only the form placement moved, still gated at `Step 11: Response billing`.
- Order on the Containers tab header: cargo trio first (Step 11), then the loading fields (Step 17).
- Container seeding (`seedContainerCargo` + `ImportContainer::booted`) untouched and still covered by the smoke tests.
- IMPORT.md names no tabs, so it needed no change.

---

# Progress - Portal tracking progress step fields

Goal: Show populated shipment-level Filament fields under their matching steps in portal tracking progress.
Started: 2026-09-23 11:59
Status: COMPLETE

## Plan Checklist

- [x] Step 1: Add typed field values to shipment timeline entries - DONE - 11:59
- [x] Step 2: Render step fields in the shared portal timeline - DONE - 12:01
- [x] Step 3: Add portal UAT checklist and finish activity log - DONE - 12:03

## Current Focus

Working on: none — plan complete
Next: None
Blocked: None

## Final Summary (2026-09-23 12:03)

- Added typed shipment-level field values to reached Import and Export timeline milestones.
- Rendered values below their steps; omitted blank values and kept container fields in the Containers section.
- Added portal UAT coverage; PHP syntax checks and `git diff --check` passed.

## Notes

- Scope approved: shipment-level fields only; container-specific values remain in the existing Containers section.
- The shared timeline serves both Import and Export shipment detail pages.
- Added the typed timeline field-value payload to `ShipmentTimelineEntry`.
- Mapped populated shipment-level fields to matching Import and Export milestones.
- Rendered the values as escaped, responsive label/value pairs under each reached step.

---

# Progress - Right-side portal tracking fields

Goal: Align milestone details on the left and related fields on the right in the portal timeline.
Started: 2026-09-23 12:07
Status: COMPLETE

## Plan Checklist

- [x] Step 1: Place fields in a responsive right-hand column - DONE - 12:07
- [x] Step 2: Update portal UAT for desktop and mobile layout - DONE - 12:07
- [x] Step 3: Finish activity log and review - DONE - 12:09

## Current Focus

Working on: none — plan complete
Next: None
Blocked: None

## Final Summary (2026-09-23 12:09)

- Moved populated milestone fields to a right-hand column on medium and wider layouts.
- Kept fields below milestone details on mobile; updated the UAT checklist for both layouts.
- `git diff --check` passed; no automated tests were run.

## Notes

- Approved layout: milestone title and date stay left; populated fields sit right on wider screens and stack on mobile.
- The timeline now uses a responsive two-column row when milestone fields exist.
- Updated the manual checklist for desktop right alignment and mobile stacking.

---

# Progress - Tracking progress border and font refinement

Goal: Remove the vertical divider and align field-value text size with milestone text.
Started: 2026-09-23 12:11
Status: COMPLETE

## Plan Checklist

- [x] Step 1: Remove the divider and normalize value text size - DONE - 12:11
- [x] Step 2: Update the desktop UAT expectation - DONE - 12:11
- [x] Step 3: Finish activity log and review - DONE - 12:12

## Current Focus

Working on: none — plan complete
Next: None
Blocked: None

## Final Summary (2026-09-23 12:12)

- Removed the vertical separator and set field values to the milestone `text-sm` size.
- Updated the portal UAT checklist; `git diff --check` passed.

## Notes

- Keep field labels smaller than their values; values use the milestone `text-sm` scale.
- Removed only the desktop vertical border; retained mobile's horizontal separator.
- Updated the desktop manual check for the missing divider and text-size proportion.

---

# Progress - B/L detail consistency (Option B)

Goal: Unify B/L detail top-bottom around container line progress style
Started: 2026-09-23 19:00

## Plan Checklist

- [x] Step 1: Unify card shell + headers + spacing - DONE - 19:05
- [ ] Step 2: Restyle shipment timeline to container vertical line - TODO
- [ ] Step 3: Normalize pills, colors, typography - TODO
- [ ] Step 4: Fold PIB block + verify + UAT - TODO

## Current Focus

Working on: Step 1 done, awaiting approval for Step 2
Next: resources/views/components/shipment-timeline.blade.php
Blocked: None

## Notes

- PortalTest 53 passed after Step 1. Shell is now mt-6 overflow-hidden rounded-xl bg-white shadow-sm + border-b header px-4 py-3 sm:px-6.

- [x] Step 2: Restyle shipment timeline to container vertical line - DONE - 19:15
- [x] Step 3: Normalize pills, colors, typography - DONE - 19:15
- [x] Step 4: Fold PIB block + verify + UAT - DONE - 19:15

## Verification

- `php artisan test --filter=PortalTest` - 53 passed (352 assertions).

- [x] Timeline fields right-side - DONE - 19:30

- [x] Container fields right-side - DONE - 19:35
