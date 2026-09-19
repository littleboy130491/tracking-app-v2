# Design — Split Export & Import into Separate Shipment and Container Models

> **File:** plans/split-export-import.md
> **Responsibility:** The frozen design for giving Export and Import their own shipment and container models, tables, menus and permissions.
> **Status:** EXECUTED — all seven phases done on 2026-09-19; full test suite green (90 tests / 432 assertions)
> **What it does:** pins down names, field ownership, shared links, the portal shape, the fresh-app migration approach and the order of work, so every execution step has one source of truth.
> **How to use:** review §9 "Decisions" (defaults apply unless you say otherwise); §11 lists the execution phases — we do one phase per turn.
> **How to extend:** change this document first, then the code.
>
> **v2 changes (2026-09-19):** containers are split per type as well (`ExportContainer` / `ImportContainer`); the project is treated as a fresh app — migrations and seeders are rewritten, no data migration.

---

## 1. Why we are doing this

- Export and Import get their own model, table, form and menu, so each process can be scoped and permissioned on its own.
- Containers are split too: an export container only shows export fields, an import container only import fields.
- Each table only holds the fields its process uses — no more columns sitting empty for the "wrong" type.
- The five cargo fields you asked to remove (`package_count`, `package_unit`, `terminal_name`, `loading_date`, `loading_destination`) are simply not carried into the new tables and are removed from the docs.
- Fresh app: no data to preserve, so migrations are rewritten in place and `migrate:fresh --seed` is the workflow.

## 2. Where we are now (helicopter view)

- One `bill_of_ladings` table + a `shipment_type` column (`export` / `import`) carries both processes.
- One shared `containers` table mixes export and import container fields and links to the B/L.
- Activity logs, attachments (Curator) and HS codes all link to the single B/L / container.
- The admin has one "Bills of lading" screen and one "Containers" screen; the portal shows both types in one list.
- ~50 files reference the B/L concept; 13 branch on the type.
- This is the largest change the project has had. The app is under construction from Step 2 until Step 6 (no data to preserve).

## 3. Target model

### 3.1 Models and tables

| New model | Table | Admin menu |
| :--- | :--- | :--- |
| `App\Models\ExportShipment` | `export_shipments` | **Export** → Shipments |
| `App\Models\ImportShipment` | `import_shipments` | **Import** → Shipments |
| `App\Models\ExportContainer` | `export_containers` | **Export** → Containers |
| `App\Models\ImportContainer` | `import_containers` | **Import** → Containers |

- No `shipment_type` column: the table itself says which process it is.
- The `ShipmentType` enum stays for labels/badges; each model reports its own type.
- `BillOfLadingStatus` is renamed to `ShipmentStatus` (fresh app — the "B/L" name no longer fits the model layer).

### 3.2 Shipment field ownership (every current `bill_of_ladings` column)

| Column | Goes to | Note |
| :--- | :--- | :--- |
| `reference_number` | Both | unique per table |
| `bl_number` | Both | |
| `shipment_mode` | Both | FCL / LCL / Air |
| `company_id` | Both | FK companies |
| `company_name_snapshot` | Both | |
| `document_received_date` | Both | |
| `document_received_by` | Both | FK users |
| `aju_number` | Both | |
| `do_number` | Both | export: booking order; import: unlocks at DO release |
| `shipping_line` | Both | |
| `vessel_name` | Both | |
| `voyage_number` | Both | |
| `port_of_loading` | Both | |
| `port_of_discharge` | Both | |
| `departure_date` | Both | |
| `eta_at` | Both | |
| `actual_arrival_at` | Both | |
| `goods_description` | Both | |
| `depot_closing_at` | Export | |
| `cy_closing_at` | Export | |
| `pickup_depot_name` | Export | |
| `stuffing_date` | Export | |
| `stuffing_destination` | Export | |
| `draft_pib_confirmation_status` | Import | |
| `draft_pib_confirmed_at` | Import | |
| `draft_pib_confirmation_notes` | Import | |
| `billing_issuance_status` | Import | |
| `billing_issued_at` | Import | |
| `thc_payment_status` | Import | |
| `thc_paid_at` | Import | |
| `do_released_at` | Import | |
| `billing_payment_status` | Import | |
| `billing_paid_at` | Import | |
| `billing_response` | Import | SPPB / AP / SPJK / SPJM |
| `billing_response_at` | Import | |
| `behandle_payment_status` | Import | |
| `behandle_paid_at` | Import | |
| `status` | Both | `ShipmentStatus` enum |
| `current_milestone` | Both | each model uses its own milestone enum (§6) |
| `latest_event` / `latest_event_at` | Both | |
| `completed_at` | Both | |
| `created_by` / `updated_by` | Both | |
| `timestamps` / `deleted_at` | Both | |
| `package_count` | **Drop** | confirmed by product owner |
| `package_unit` | **Drop** | confirmed |
| `terminal_name` | **Drop** | confirmed |
| `loading_date` | **Drop** | confirmed |
| `loading_destination` | **Drop** | confirmed |
| `shipment_type` | **Drop** | the model/table implies it |

### 3.3 Container field ownership (the shared `containers` table is split)

**`export_containers`** — belongs to `export_shipments` (`export_shipment_id`, unique with `container_number`):

`container_number`, `size`, `type`, `seal_number`, `driver_name`, `license_number`, `driver_license_number`, `tracking_position`, `tracking_position_url`, `stuffing_status`, `stuffing_started_at`, `stuffing_finished_at`, `gate_in_port_name`, `gate_in_cy_at`, `vgm_value`, `final_checked`, `final_checked_at`, `status`, `latest_event`, `latest_event_at`, `completed_at`, `created_by`, `updated_by`, timestamps, `deleted_at`.

**`import_containers`** — belongs to `import_shipments` (`import_shipment_id`, unique with `container_number`):

`container_number`, `size`, `type`, `seal_number`, `driver_name`, `license_number`, `driver_license_number`, `gate_out_cy_at`, `gross_weight`, `gross_weight_unit`, `cbm`, `inspection_status`, `inspected_at`, `inspection_notes`, `factory_arrived_at`, `factory_loading_status`, `factory_loading_started_at`, `factory_loading_finished_at`, `return_depot_name`, `empty_returned_at`, `status`, `latest_event`, `latest_event_at`, `completed_at`, `created_by`, `updated_by`, timestamps, `deleted_at`.

- The five named photo slots (door / floor / seal / EIR / additional) are shared by both container models through Curator attachments.

## 4. Shared machinery — how everything links to the new models

### 4.1 Link rule: explicit foreign keys

Any column that points at a shipment is named `export_shipment_id` or `import_shipment_id`; any column that points at a container is named `export_container_id` or `import_container_id`. Nullable, cascade delete; exactly the matching one is set.

- Explicit: you can see in the database which process a row belongs to.
- Real foreign keys and per-parent unique indexes are possible.
- Filament's relationship repeaters keep working with normal `hasMany` relations.
- Alternative considered: polymorphic `type` + `id` pairs (fewer rules, but no FK constraints and more Laravel "magic"). See Decision D3.

### 4.2 Table-by-table changes

| Table | Change |
| :--- | :--- |
| `bill_of_ladings` | replaced by `export_shipments` + `import_shipments` |
| `containers` | replaced by `export_containers` + `import_containers` |
| `activity_logs` | drop `bill_of_lading_id`; add `export_shipment_id`, `import_shipment_id`, `export_container_id`, `import_container_id` (nullable FKs) |
| `curator` (attachments) | drop `bill_of_lading_id`; add the same four nullable FKs; keep `category`, `is_customer_visible`, `uploaded_by` |
| `bill_of_lading_hs_code` | replaced by `export_shipment_hs_code` + `import_shipment_hs_code` |
| `notes` | unchanged — already polymorphic (`noteable_type` / `noteable_id`), works with all new models |
| `companies` | add `exportShipments()` + `importShipments()` relations |
| `users` | row-scope helpers stay (`companyIds()`, `scopeToAssignedCompanies()`) |

### 4.3 Small helper methods

- `ExportContainer::shipment()` / `ImportContainer::shipment()` → the parent shipment.
- `ActivityLog::shipment()` / `ActivityLog::container()` and `Attachment::shipment()` / `Attachment::container()` → whichever of the four FKs is set.
- Container `syncAttachments()` writes its own container and shipment columns.

## 5. Models & shared behaviour

- Shared shipment behaviour lives in one trait: `app/Models/Concerns/ActsAsShipment.php`
  - create defaults (company name snapshot, document received date/by),
  - milestone helpers: `milestoneSequence()`, `milestonePosition()`, `nextMilestone()`, `previousMilestone()`, `advanceMilestone()`, `regressMilestone()`, `moveToMilestone()`.
- Shared container behaviour lives in `app/Models/Concerns/ActsAsContainer.php`
  - `photoPickers()`, `syncAttachments()`, `shipment()`.
- Each model defines its own relations explicitly: `company()`, `containers()`, `activityLogs()`, `attachments()`, `hsCodes()`, `creator()`, `updater()`, `documentReceivedBy()` (shipments); `shipment()`, `activityLogs()`, `attachments()` (containers).
- Services type-hint unions: `ExportShipment|ImportShipment` and `ExportContainer|ImportContainer` (simple and explicit for two types).
- Audit event names become process-neutral: `shipment_updated`, `container_updated`, `shipment_created`, etc.

## 6. Milestones

- `ShipmentMilestone` (one enum with both sequences) is replaced by two enums:
  - `ExportMilestone` — 8 steps: document received → checking booking order → pick up empty container → on the way to factory → stuffing / PEB & NPE → checking PEB & NPE → gate in CY → final checking.
  - `ImportMilestone` — 17 steps including the SPJM branch (documents uploaded, behandle payment, inspection, SPPB) that only appears when `billing_response = SPJM`.
- The milestone stepper Blade component stays as-is (it already takes a sequence + current step).

## 7. Admin panel

- Four resources, each with its own form, table, pages, policy and permissions:
  - `app/Filament/Resources/ExportShipments/**` — `ExportShipmentResource`, `Schemas/ExportShipmentForm.php`, `Tables/ExportShipmentsTable.php`, `Pages/List|Create|Edit`.
  - `app/Filament/Resources/ImportShipments/**` — same shape.
  - `app/Filament/Resources/ExportContainers/**` — `ExportContainerResource`, form, table, pages.
  - `app/Filament/Resources/ImportContainers/**` — same shape.
- Menus: **Export** (Shipments, Containers), **Import** (Shipments, Containers); HS codes move to a **Master data** group; CRM and Monitoring unchanged (Decision D2).
- Shared form field builders in one place (Decision D6): shipment customer/booking/cargo blocks, milestone gating, containers repeater, notes + activity-log tabs; container identity/photo blocks.
- Milestone gating (`gate()`, `locked()`, locked-message links) moves into the shared builders; the stepper + advance/regress header actions are replicated per shipment edit page.
- Company edit page: one B/L relation manager becomes two — Export Shipments and Import Shipments.
- Activity logs viewer: row scope + "Shipment" / "Container" columns adapted to the four new models.
- Policies/permissions: Shield-generated `ExportShipmentPolicy`, `ImportShipmentPolicy`, `ExportContainerPolicy`, `ImportContainerPolicy`; `BillOfLadingPolicy` + `ContainerPolicy` retired. `PermissionSeeder` updated; `RoleSeeder` re-run (operators automatically receive the four new resource permission sets under the existing rule).

## 8. Customer portal

- Dashboard: **two tabs, Export and Import** (recommended), each the existing paginated list with company / number / status / year / month filters and latest-journey columns (Decision D4).
- Shipment detail: two Livewire components + views:
  - `ExportShipmentDetail` → `/portal/export-shipments/{exportShipment}`
  - `ImportShipmentDetail` → `/portal/import-shipments/{importShipment}` (keeps the Draft PIB confirm / request-revision actions)
- Container detail: two thin components + views sharing partials:
  - `ExportContainerDetail` → `/portal/export-containers/{exportContainer}`
  - `ImportContainerDetail` → `/portal/import-containers/{importContainer}`
- `ShipmentTimeline` accepts either shipment or container model; the dated rows are per type (export container dates vs import container dates); customer-visible rules unchanged.

## 9. Decisions (defaults apply unless you veto)

| # | Decision | Default | Alternative |
| :--- | :--- | :--- | :--- |
| D1 | Names | `ExportShipment` / `ImportShipment`, `ExportContainer` / `ImportContainer` | keep "B/L" wording |
| D2 | Menu structure | groups **Bill of Ladings** (Export, Import) and **Containers** (Export, Import); HS codes → **Master data** (product-owner change after Step 4) | one shared "Shipments" group |
| D3 | Link style | explicit FK columns (`export_/import_shipment_id`, `export_/import_container_id`) | polymorphic type + id pairs |
| D4 | Portal dashboard | two tabs Export / Import | one merged list (union query) |
| D5 | Containers | split per type (your call) — two models, two tables, two menus | one shared container table |
| D6 | Shared form code | new folder `app/Filament/Concerns/` for shared field builders | duplicate fields in each form |
| D7 | Milestones | two enums (`ExportMilestone`, `ImportMilestone`) | one enum with type switches |
| D8 | Fresh app | rewrite migrations + seeders in place; no data migration; `migrate:fresh --seed` | keep old table and copy data |

## 10. Fresh-app approach

- Migrations are rewritten in place instead of layering new ones:
  - replace `create_bill_of_ladings_table` with `create_export_shipments_table` + `create_import_shipments_table`;
  - replace `create_containers_table` with `create_export_containers_table` + `create_import_containers_table`;
  - edit `create_activity_logs_table`, the curator shipment-columns migration and `create_hs_codes_table` for the new links;
  - fold and delete the follow-up migrations (`add_shipment_mode…`, `add_export_spec_columns`, `move_pickup_stuffing…`, `drop_empty_picked_up_at…`, `make_bill_of_lading_id_nullable…`).
- `DemoShipmentSeeder` is rewritten to seed both shipment and container models (split into one seeder per process, run by `DatabaseSeeder`).
- The old `BillOfLading` / `Container` models, resources, policies and tests are deleted in Step 6; nothing legacy remains.
- Verification: `php artisan migrate:fresh --seed` + full `php artisan test` at Step 6.

## 11. Execution phases

| Phase | Deliverable |
| :--- | :--- |
| 1. Design freeze | this document, approved |
| 2. Schema & models | fresh migrations (4 tables + logs/curator/pivots), `ExportShipment` / `ImportShipment` / `ExportContainer` / `ImportContainer`, `ActsAsShipment` / `ActsAsContainer`, `ExportMilestone` / `ImportMilestone`, `ShipmentStatus` rename; `migrate:fresh` runs clean |
| 3. Shared services | `ActivityLogger`, `ShipmentTimeline`, `ActivityLog`, `Attachment`, `HsCode`, `Company` and user scoping work with all four models + targeted checks |
| 4. Admin | four resources (forms/tables/pages/policies/permissions), menus, company relation managers, activity-log viewer + targeted checks |
| 5. Customer portal | dashboard tabs, four detail pages, routes + targeted checks |
| 6. Switch-over | delete old model/resources/policies, rewrite seeders + all affected tests, `migrate:fresh --seed`, full `php artisan test` |
| 7. Docs & UAT | ERD, migration_plan, README, IMPORT.md (five fields removed), UAT checklist, activity log |

## 12. Risks & safety

- The test suite is red from Step 2 until Step 6 rewrites the legacy tests; every phase still has its own targeted verification (migrations, tinker checks, new tests where they can already run).
- No data is preserved (fresh-app decision); the database is rebuilt with `migrate:fresh --seed`.
- Permissions: new Shield permissions must be generated and the role seeder re-run; without it, operators lose shipment access.
- Invariant "exactly one parent FK is set": enforced in model validation (and forms); no DB check constraint to keep SQLite/MySQL portable.
- If a phase goes wrong, `git` history plus `migrate:fresh --seed` is the recovery path.
