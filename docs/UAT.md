<!--
File: docs/UAT.md
Responsibility: Manual test checklists so the user can verify each feature themselves.
What it does:
- One section per feature; newest on top; checkbox items with expected results.
How to use: Work through a section after its step is marked done; tick what passes,
  report what fails.
How to extend: Agent adds a new section whenever a user-visible feature ships
  (AGENTS.md §12).
-->

# User Acceptance Testing

## 0. Table CSV export & prune old data (2026-09-21)

**Prerequisites**

- Admin panel: log in as `admin@example.com` / `password`.
- Seeded data (`php artisan migrate:fresh --seed`).
- To test the prune action with real data, an admin must first age some rows
  (there is no UI to back-date `created_at`); otherwise the confirm dialog
  will report `0` rows.

**CSV export**

- [ ] Open **Bill of Ladings → Export**. Expected: an **Export** button (download icon) appears in the table header, above the rows.
- [ ] Click **Export** → confirm. Expected: a `.csv` file downloads.
- [ ] Open the file. Expected: it contains **every stored field** (B/L number, DO number, AJU, dates, milestone, status, created/updated/deleted) **plus relationship columns** — company name, document received by, created by, updated by, container numbers, container sizes and HS codes.
- [ ] Repeat on **Bill of Ladings → Import**, **Containers → Export**, **Containers → Import**, **CRM → Companies**, **Users** and **Master data → HS codes**. Expected: each table exports its own full field set; Companies/Users/HS codes include their related users/companies/roles and shipment counts.
- [ ] Open the **Users** export. Expected: **no password** column is present.

**Visibility / permissions**

- [ ] Log in as an operator (seeded operator account). Expected: the **Export** button is **not** shown on any of those tables; the **Prune old data** button is not shown either.
- [ ] As operator, open **CRM → Companies** and **Users** directly by URL. Expected: access is denied (admin-only resources).

**Prune old data**

- [ ] As admin, open any of the seven tables. Expected: a red **Prune old data** button appears next to **Export**.
- [ ] Click it. Expected: a confirmation dialog says how many records would be permanently deleted and states the cutoff (3 years ago), with a **Delete permanently** button.
- [ ] Confirm. Expected: a green notification reports how many records were deleted; the table refreshes.
- [ ] Age a shipment (`created_at` older than 3 years) and prune. Expected: that shipment **and its containers** are removed for good (including soft-deleted rows).
- [ ] Prune again with no old data. Expected: the notification reports `Deleted 0 …` and nothing else changes.

## 0. Empty editable fields highlighted (2026-09-21)

**Prerequisites**

- Admin panel: log in as `admin@example.com` / `password`.
- Seeded data (`php artisan migrate:fresh --seed`).

- [ ] Open `BL-EXP-0001` → Edit (it sits at Step 3). Expected: the still-empty Step 2 inputs — **DO number**, **Closing time at depot**, **Closing time at CY** — show a green **border** (no background fill); filled fields (B/L number, shipping line, vessel, ports, shipment mode, goods) look normal.
- [ ] Focus a green-bordered field. Expected: the normal blue focus ring shows while typing; validation errors still show red.
- [ ] Fields locked by the milestone (disabled, showing `Locked until Step N`) are **never** tinted.
- [ ] Click **Advance** (or jump a step). Expected: newly unlocked fields that are still empty become tinted, and the stepper/other fields behave as before.
- [ ] Fill a tinted field and **Save**. Expected: after saving, that field is no longer tinted.
- [ ] Open `BL-IMP-0001` → Edit → **Containers** tab. Expected: the same highlight on import fields and on container repeater fields.

## 0. Clickable containers on the B/L table (2026-09-21)

**Prerequisites**

- Admin panel: log in as `admin@example.com` / `password`.
- Seeded data (`php artisan migrate:fresh --seed`) or any shipment that has containers.

- [ ] Bill of Ladings → Export. Expected: the **Containers** column shows each container number (e.g. `MSKU1234567`) as a badge, not a count. A shipment with no containers shows **—**.
- [ ] Click a container badge. Expected: you land on that container's **Edit** page (Containers → Export).
- [ ] Bill of Ladings → Import. Expected: the same — numbers visible, click opens Containers → Import edit.
- [ ] CRM → Companies → open a company that has shipments. Expected: the Export/Import shipment tables on that page also list clickable container numbers.

## 0. Container list with no B/L number (2026-09-21)

**Prerequisites**

- Admin panel: log in as `admin@example.com` / `password`.
- A shipment whose **B/L number** is empty (create a new export/import shipment and stop at Customer, or clear B/L on an existing one).

- [ ] Containers → Export. Expected: the list loads (no error). The **Shipment** filter includes the empty-B/L shipment as `No B/L yet (#id)`.
- [ ] Containers → Export → New. Expected: the **Export shipment** dropdown also lists that shipment with the same caption; picking it and saving a container works.
- [ ] Containers → Import (and Import → New). Expected: the same fallback caption, no error.

## 0. Curator media picker styling (2026-09-21)

**Prerequisites**

- Admin panel: log in as `admin@example.com` / `password`.
- A shipment whose photo pickers are unlocked (e.g. `BL-EXP-0001` at Step 3+, or `php artisan migrate:fresh --seed`).

**Media picker modal**

- [ ] Open `BL-EXP-0001` → Edit → Containers tab → expand a container → click any **Photo** picker → **Add media**. Expected: the modal is fully styled — toolbar (search + close) sits in a grey bar, the media grid is a proper card grid, the upload dropzone is a bordered panel, not a plain stacked list.
- [ ] Upload or pick a file in the modal. Expected: selection controls (insert / deselect) render as styled buttons, and the picked image appears in the form field.
- [ ] Picked-photo layout (Photo Door etc.). Expected: each picked photo shows as **one full-width card** in its field (not a narrow 1/3-width tile), with the image filling the card edge to edge.
- [ ] One photo per slot. Expected: once a photo is picked, that field's **Add media** button disappears; removing the photo brings it back. Saving with a second photo in one slot is blocked (validation error).
- [ ] Check the rest of the admin panel still looks unchanged (sidebar, tables, forms). Expected: identical to before — the custom theme only adds Curator styles on top of the default look.

**Container repeater headers (export + import)**

- [ ] Open `BL-EXP-0001` → Edit → **Containers** tab. Expected: each container item (e.g. `MSKU1234567`) has a **light blue header band** with a darker blue, bold container number — clearly visible whether the item is collapsed or expanded.
- [ ] Collapse and expand the item. Expected: the coloured band keeps smooth rounded corners in both states (fully rounded when collapsed, top-rounded when expanded).
- [ ] Open an import shipment (e.g. `BL-IMP-0001`) → Containers tab. Expected: the same coloured headers on its container items.
- [ ] Toggle dark mode (if enabled). Expected: header band shifts to a darker blue tint with a lighter blue label, still readable.

## 1. Split Export/Import models (2026-09-19)

**Prerequisites**

- Run `php artisan migrate:fresh --seed` first.
- Admin panel: `http://localhost:8000/admin` — log in as `admin@example.com` / `password`.
- Portal: `http://localhost:8000/login` — OTP codes appear on the verify screen when `OTPZ_EXPOSE_IN_DEV=true`.

**Menus**

- [ ] Sidebar shows, top to bottom: **Bill of Ladings** (Export, Import), **Containers** (Export, Import), **CRM** (Companies, Users), **Master data** (HS codes), **Monitoring** (Activity logs).
- [ ] Bill of Ladings → Export lists only `BL-EXP-0001/0002/0003`; Bill of Ladings → Import lists only `BL-IMP-0001/0002`. The **Containers** column shows each container number as a clickable badge. Containers → Export / Import list the matching containers.

**Export shipment form**

- [ ] Bill of Ladings → Export → New. Expected: only the **Customer** section (Customer, Document received date/by); no stepper, no tabs.
- [ ] Open `BL-EXP-0001` → Edit. Expected: Customer section, then the stepper (8 steps), then tabs **Shipping Details | Containers | Status | Notes | Activity log**. The header shows a **New export B/L** action (plus Regress / Advance / Save); clicking it opens the create form.
- [ ] Shipping Details order: **B/L number, DO number, Shipping line, Vessel name, Voyage, Port of loading, Port of discharge, Closing time at depot, Closing time at CY, Shipment mode, Goods description** (no AJU number here — it lives on the Containers tab, unlocked at Step 5). **Departure date, ETA and Actual arrival are gone**; **Status and Completed at now live in the Status tab** (locked until `Step 8: Final checking shipment details`; reaching the last step also completes the shipment automatically). **Pick up depot, Stuffing date (date + time) and Stuffing destination live on the Containers tab, above the repeater.** Package count, Package unit, Terminal name, Loading date and Loading destination are **gone**; **HS codes are import-only**.
- [ ] **Status** tab (between Containers and Notes). Expected: **Status** dropdown (draft / in progress / completed / cancelled) + **Completed at**, both editable once Step 8 is reached; Save persists them and the Activity log records the change.
- [ ] At Step 1 the fields show `Locked until Step 2: Checking booking order`. Click **Advance** → they become editable.
- [ ] Containers tab → expand `MSKU1234567`. Expected: identity + driver + photos unlocked at "Pick up empty container"; **Tracking position** unlocks at "Container on the way to factory"; **Stuffing status at Factory** (On Process / Finished, defaulting to On Process) at "Stuffing at factory / PEB & NPE"; **Port of loading** + **Gate in CY** at "Checking PEB & NPE"; **VGM (kg)** at "Gate in CY"; **Final checked** (toggle) at "Final checking".
- [ ] Containers tab, above the repeater. Expected: **AJU number** appears under the pickup/stuffing fields, locked until `Step 5: Stuffing at factory / PEB & NPE`; advancing to Step 5 makes it editable and the Activity log records changes to it.

**Import shipment form (IMPORT.md spec)**

- [ ] Bill of Ladings → Import → New works the same way (Customer only on create).
- [ ] Open `BL-IMP-0001` → Edit. Expected: the stepper shows the SPJM branch (21 steps) in three rows — row 1: the 10 steps up to **Payment billing**; row 2: **Response billing** followed by the **five SPJM-only steps in red** (Upload all document, Waiting process bahandle, Payment bahandle, Container inspection, Waiting change status SPJM to SPPB); row 3: the remaining 5 steps. The header shows a **New import B/L** action that opens the create form. Shipping Details unlocks in this order: **B/L number** at `Step 2: Checking document`; **Shipping line** at `Step 3: Draft PIB`; **Vessel name** at `Step 4: Checking draft PIB to importir`; **Confirmation checklist** toggle at `Step 5: Waiting confirmation from customer`; **AJU number**, **Voyage** and **Status billing** at `Step 6: Final sending PIB to custom (issuing billing)`; **Port of loading** at `Step 7: Process payment THC`; **Departure date** at `Step 8: Waiting release DO`; **Port of discharge** at `Step 9: DO release`; **Arrival time / ETA** at `Step 10: Payment billing`; **Response billing** at `Step 11: Response billing`; **Goods description** at `Step 12: Upload all document`; **HS codes** at `Step 13: Waiting process bahandle`.
- [ ] SPJM notice: on `BL-IMP-0001` (**Response billing = SPJM**) an info box **Tambahan step SPJM** appears right below the response field, listing the extra steps; on `BL-IMP-0002` (**SPPB**) it is absent. Tambahan step SPJM is **not** a step on the stepper.
- [ ] Set **Response billing** = SPPB on a shipment at Step 11. Expected: the stepper drops the red SPJM block (row 2 becomes Response billing + the normal steps, 16 steps total) and the next step is **Container shipping schedule**.
- [ ] `BL-IMP-0002` (completed, SPPB) shows the same shortened sequence.
- [ ] SPJM demo data: `BL-IMP-0003` (completed SPJM, SIN) shows the whole red block done and the shipment completed; `BL-IMP-0004` (BJM) sits at **Response billing** with the red block upcoming and its containers carrying only the number (no size yet).
- [ ] **Status** tab (after Containers): Status + Completed at, editable once the last step is reached.
- [ ] Containers tab (repeater unlocks at `Step 11: Response billing`): expand `CMAU7654321`. Expected: container number at Step 11; **Size** at `Upload all document`; photos at Step 11; **Gate out CY** + **Driver name** + **No. License** at `Gate out from inbound terminal`; **Tracking position driver** + **Tracking position input (manual)** at `Container on the way factory`; **Gross weight** at `Payment bahandle`; **CBM / measurement** at `Waiting change status SPJM to SPPB`; **Loading in factory** + status at `Container arrived in factory`; **Return depot name** + **Return date** at `Empty container returned`.
- [ ] Removed per IMPORT.md: no DO number, no payment status/timestamps, no draft-PIB status/notes fields, no inspection fields, no container Type/Seal on import.

**Standalone containers**

- [ ] Containers → Export → New: pick an **Export shipment**, fill Container Number → Save. Expected: saved and listed; edit page shows the export fields only.
- [ ] Containers → Import → New: same with an **Import shipment**; edit page shows the import fields only.

**Portal**

- [ ] Log in as `customer@example.com` → portal home has a **Type** dropdown (Export / Import) next to the company/status filters; Export lists NUS/BJM exports; selecting Import lists the SIN import.
- [ ] Open `BL-EXP-0001` → Export shipment page: facts, journey, containers table (container links open in a new tab).
- [ ] Open `BL-IMP-0001` → Import shipment page: facts, **Draft PIB confirmation** with Confirm / Request revision, journey, containers.
- [ ] Open an export container page (`MSKU1234567`) and an import container page (`CMAU7654321`). Expected: per-type facts (export: tracking/VGM/final check; import: gate-out/weights/return) + Sailing information + Journey.
- [ ] `sari@java-retail.test` opens `BL-IMP-0002` (already confirmed): the Confirm and Request revision actions are **not** offered.
- [ ] A **draft** shipment (Status = Draft) is **not** listed in the portal, and opening its shipment or container URL returns **404**. Advance it past **Document received** → it becomes In progress and appears (with its container pages).

**Audit**

- [ ] Monitoring → Activity logs. Expected: the **Shipment** and **Container** columns fill from the new split links (e.g. `BL-EXP-0001`, `MSKU1234567`); operator scope still hides other companies' rows.
- [ ] On any shipment edit page → **Activity log** tab. Expected: milestone moves and saved field edits are listed with when/event/actor/summary.

## 2. Shipment form layout + customer permissions (2026-09-18, updated)

**Prerequisites**

- Run `php artisan migrate:fresh --seed` first.
- Admin panel: log in as `admin@example.com` / `password` (second account: `operator@example.com` / `password`).

**Create page**

- [ ] Bill of Ladings → Export → New (and Bill of Ladings → Import → New). Expected: only the **Customer** section is shown — Customer, Document received date, Document received by. No milestone stepper, no tabs.

**Edit page layout (as admin)**

- [ ] Open a shipment → Edit. Expected, top to bottom: full-width **Customer** section, then the **milestone stepper**, then the tabs **Shipping Details | Containers | Notes | Activity log**.
- [ ] Open the **Containers** tab. Expected: the container repeater with expandable items.

**Pickup / stuffing fields live on the export shipment**

- [ ] Open `BL-EXP-0001` → Edit → **Containers** tab. Expected: **Pick up depot**, **Stuffing date**, **Stuffing destination** sit **above the container repeater** as shipment-level fields, locked until `Step 3: Pick up empty container at depot`.
- [ ] The **Shipping Details** tab does **not** show those three fields, and the container items don't contain them either.

**Customer field permissions**

- [ ] As **admin**, open a shipment → Edit. Expected: the **Customer** dropdown is visible and editable; **Customer name** shows the snapshot.
- [ ] As **operator** (`operator@example.com`), open an assigned shipment → Edit. Expected: the **Customer** dropdown is **not** shown; only the read-only **Customer name** is displayed.
- [ ] As operator, Save the form. Expected: no validation error and the customer is unchanged.

**Locked field jumps to milestone**

- [ ] On Edit, find a field showing `Locked until Step N: …` (e.g. **DO number** → Step 2). Expected: the message is a green clickable link with a small step-number dot.
- [ ] Click that message. Expected: the page smooth-scrolls up to the milestone stepper and the matching dot (same number N) pulses green for ~2 seconds.

## 3. Export spec fields + latest event (2026-09-17, updated)

**Prerequisites**

- Run `php artisan migrate:fresh --seed` first.
- Admin panel: log in as `admin@example.com` / `password`.

**Document received (Customer section)**

- [ ] New shipment. Expected: the **Customer** section shows **Document received date** (defaulted to today) and **Document received by** (default = you); both required.
- [ ] Reopen that shipment → Edit → **Customer** section. Expected: the date is editable; **Document received by** is disabled for operators and editable for admin/super_admin.

**Container fields (Containers tab → expand a container)**

- [ ] At the pickup step an export container's fields appear in this order: **Container Number**, **Container Size**, **Container Type**, **Seal Number**, **Driver name**, **Vehicle / Truck Number**, **Driver License Number**, then the five photos.
- [ ] At "Container on the way to factory" the container shows **Tracking position** and **Tracking position (url)** side by side on one row (50/50).
- [ ] The containers area shows five photo pickers — **Photo Door / Photo Floor / Photo Seal / Photo EIR** and **Additional Photos**. Expected: pick or upload into each slot → Save → reopen: the photos stay in their slots.
- [ ] Remove a photo from a slot and Save. Expected: it detaches from the container (the file stays in the media library).
- [ ] At "Checking PEB & NPE", a container's **Port of loading** is prefilled from the shipment's **Port of loading** (e.g. `Jakarta (IDJKT)` for BL-EXP-0001) and can be overridden.

**Latest event**

- [ ] Save any change on a shipment (or move a milestone). Expected: its `latest_event` updates — visible in the Activity log as the newest row with the matching time.
- [ ] Change a container-level field (e.g. stuffing status) and Save. Expected: both the container row and its shipment show the container event as their latest event.

## 4. Notes on shipments, containers, companies, users (2026-09-17)

**Prerequisites**

- Run `php artisan migrate:fresh --seed` first.
- Admin panel: log in as `admin@example.com` / `password` (second account: `operator@example.com` / `password`).

**Adding and reading**

- [ ] Open a shipment → Edit → **Notes** tab. Expected: a tall textarea with padding, then **Add note** below it (not overlapping the field); empty state reads "No notes yet."
- [ ] Add a note. Expected: it appears immediately (no form Save needed) with your name and "just now".
- [ ] Open the same shipment as `operator@example.com`. Expected: you see the admin's note, but it has no Edit/Delete links.
- [ ] As the operator, add your own note. Expected: yours shows Edit and Delete links; the admin's still does not.
- [ ] Edit your note → Save. Expected: the text updates and an "edited" marker appears.
- [ ] Delete your note and confirm. Expected: it disappears.
- [ ] The same **Notes** section appears on: container Edit, company Edit and user Edit pages. Expected: identical add/read/edit-own behaviour everywhere.

**Audit**

- [ ] Back on the shipment → Activity log tab. Expected: `note_created` / `note_updated` / `note_deleted` rows naming you as the actor.
- [ ] Monitoring → Activity logs as the operator. Expected: note entries for unassigned companies' shipments are not listed.

## 5. Customer section + Shipping Details Step 2 (2026-09-17, updated)

**Prerequisites**

- Run `php artisan migrate:fresh --seed` first.
- Admin panel: log in as `admin@example.com` / `password`.

**Customer section (create)**

- [ ] Bill of Ladings → Export → New. Expected: the **Customer** section shows **Customer**, **Document received date** and **Document received by**; no AJU, B/L, or mode fields. Saving with just the required fields succeeds.
- [ ] Reopen that shipment → Edit → **Customer** section. Expected: **Customer name** is shown read-only (editable by admin/super-admin).

**Shipping Details + stepper**

- [ ] Same shipment → Edit. Expected: the milestone stepper sits **above the tabs** (not inside Shipping Details); on New it is absent.
- [ ] **Shipping Details** tab (export). Expected: the first fields are the Step 2 group, in this order — **B/L number**, **DO number**, **Shipping line**, **Vessel name**, **Voyage**, **Port of loading**, **Port of discharge**, **Closing time at depot**, **Closing time at CY**, **Shipment mode**, then **Goods description** (no AJU number here — it lives on the Containers tab at Step 5; no HS codes field on export).
- [ ] On a shipment still at Step 1, those fields show `Locked until Step 2: Checking booking order`. Advance to Step 2 → all become editable at once.
- [ ] Import shipment Step 2 instead groups: **AJU number**, **B/L number**, **Shipping line**, **Vessel name**, **Voyage**, **Port of loading**, **Port of discharge**, **Shipment mode**, **Goods description**, **HS codes** (DO number lives with DO release).

## 6. Admins see all shipments in the portal (2026-09-17, updated)

**Prerequisites**

- Run `php artisan migrate:fresh --seed` first.
- Portal login: `http://localhost:8000/login` — OTP codes appear on the verify screen when `OTPZ_EXPOSE_IN_DEV=true`. Use `admin@example.com` (sees everything) vs a customer email (scoped).

**Admin portal access**

- [ ] At `/login`, request a code for `admin@example.com`. Expected: code is issued (admins may use the portal login); for `operator@example.com` the same request shows "No active customer account matches that email address."
- [ ] Log in as `admin@example.com` → portal home. Expected: with **Type = Export** the list shows `BL-EXP-0001/0002/0003`; switching **Type = Import** shows `BL-IMP-0001/0002`; the company filter offers every company.
- [ ] Open a shipment the admin has no link to (e.g. `BL-IMP-0002` / JRD) and one of its containers. Expected: both pages open normally.
- [ ] Log in as a customer (e.g. `rina@nusantara.test`). Expected: only their companies' shipments appear; guessing another company's shipment URL still 404s.

## 7. Operator row-level scope (2026-09-17, updated)

**Prerequisites**

- Run `php artisan migrate:fresh --seed` first — the seeder assigns `operator@example.com` to NUS and SNI.
- Admin panel: log in as `operator@example.com` / `password` (then compare with `admin@example.com` / `password`).

**Shipments**

- [ ] As operator, open **Bill of Ladings → Export**. Expected: only **BL-EXP-0001** (NUS) and **BL-EXP-0003** (SNI) are listed — not the BJM export.
- [ ] Open **Bill of Ladings → Import**. Expected: empty — the seeded imports belong to SIN and JRD.
- [ ] Open one of the hidden shipments by pasting its edit URL. Expected: **404** page.
- [ ] Open BL-EXP-0001 → Edit → Shipping Details. Expected: the page works normally (containers, attachments, activity log tab all scoped to this shipment).
- [ ] Create a new shipment → **Customer** dropdown. Expected: only **PT Nusantara Ekspor** and **PT Sulawesi Nickel Industri** are offered.
- [ ] **Containers → Export** → Shipment filter. Expected: only the operator's two shipments appear; container rows match them.
- [ ] **Monitoring → Activity logs**. Expected: only entries for the operator's shipments/containers; no rows for other companies.
- [ ] Log out, log in as `admin@example.com` → same lists. Expected: every shipment, container and log is visible.

**Portal (customers)**

- [ ] Customer portal → log in as `customer@example.com`. Expected: only NUS/SIN/BJM shipments appear (portal scoping matches the admin rule).
- [ ] Paste another company's shipment URL. Expected: **404**.

## 8. Staff impersonation (2026-09-17)

**Prerequisites**

- Run `php artisan migrate:fresh --seed` first.
- Admin panel: log in as `admin@example.com` / `password` (or `superadmin@example.com` / `password`).

**Impersonate from the users list**

- [ ] CRM → Users. Expected: each row shows an impersonate icon; your own row does not.
- [ ] Click the impersonate icon on a customer row. Expected: you land on `/` as that user (portal for customers) and a dark banner reads "Impersonating {name}" with a leave link.
- [ ] Click the leave link. Expected: you return to the admin users list as yourself.
- [ ] Log in as `operator@example.com` / `password` → CRM → Users. Expected: 403 (operators never see the list, so no impersonate icon).
- [ ] As `admin@example.com`, open the edit page of `superadmin@example.com`. Expected: no impersonate button (admins cannot impersonate super admins).
- [ ] As `superadmin@example.com`, open any user edit page. Expected: impersonate button present in the header.

## 9. Portal journey timeline like the reference tracker (2026-09-16, updated)

**Prerequisites**

- Run `php artisan migrate:fresh --seed` first.
- Customer portal: `http://localhost:8000/login` — use a seeded customer email (e.g. `customer@example.com`); OTP codes appear on the verify screen when `OTPZ_EXPOSE_IN_DEV=true`.

**Container page**

- [ ] Portal → open a shipment → open a container in a new tab. Expected: a **Sailing information** block shows vessel, line, POL/departure and POD/arrival (actual) or ETA (estimate).
- [ ] Same page → **Journey** section. Expected: oldest-first rows with time + place; the last row carries a `latest` badge; an ETA-only arrival carries an `estimate` badge.
- [ ] Open the completed export container `EGHU6677881`. Expected: journey reads gate-in → final check → departure → arrival.
- [ ] Open a container with no dates yet. Expected: journey shows "No journey events have been recorded for this shipment yet."

**Shipment page**

- [ ] Portal → open a shipment. Expected: a **Journey** section sits above the containers table with the shipment-level voyage dates plus visible log entries (e.g. milestone moves, PIB confirmation).

**Dashboard list**

- [ ] Portal home → shipment table. Expected: **Latest place** and **Latest event** (+ time) columns appear per row; rows with no journey yet show `—`.
- [ ] With **Type = Export**, search `EGHU6677881` (as `agus@borneo.test`, who manages SNI). Expected: only `BL-EXP-0003` matches; its latest event reads "Vessel arrival at port of discharge".

## 10. Shipment Progress milestones & audit (2026-09-16, updated)

**Prerequisites**

- Run `php artisan migrate:fresh --seed` first (schema was rebuilt).
- Admin panel: log in as `admin@example.com` / `password`.

**Shipping Details layout**

- [ ] Bill of Ladings → Export → New: the form shows only the **Customer** section; **Shipping Details** and **Activity log** are absent.
- [ ] Create a shipment, then open its Edit → **Shipping Details**. Expected: fields are laid out continuously; no card boxes or collapse controls.
- [ ] The horizontal milestone stepper appears at the top with comfortable vertical padding; completed steps are green, the current step is highlighted, later steps are grey.
- [ ] A field belonging to a not-yet-reached step is disabled and shows `Locked until Step X: {step name}` underneath; fields at or before the current step are editable and show no milestone text.
- [ ] Click a future step more than one ahead. Expected: it is not clickable. Click the next step and confirm the Filament modal. Expected: progress advances one step and the newly unlocked fields become editable.
- [ ] Click an earlier step and confirm. Expected: progress moves back and its fields remain editable.

**Containers**

- [ ] The containers area sits in the **Containers** tab; each container item is collapsed by default showing only its container number. Expected: expanding an item reveals its flat milestone field list.
- [ ] At the pickup step, add a container. Expected: a collapsed item appears; expand it — fields show as a flat list, each disabled field naming the step that unlocks it.
- [ ] Each export container item shows five photo pickers at the pickup step. Expected: pick existing files or upload new ones → Save → files stay attached to that container.
- [ ] Reopen the shipment after saving. Expected: the picked attachments are still shown on the container; remove one and save → it detaches (the file itself remains in the media library).
- [ ] **Tracking position** is a single text field per export container, unlocked at "Container on the way to factory" (Step 4) — not a repeatable list.
- [ ] At "Checking PEB & NPE" the container unlocks **Port of loading** + **Gate in CY** date; at "Gate in CY" it unlocks **VGM (kg)** (weight only, no unit selector); at "Final checking" it unlocks **Final checked** (toggle) + **Final checked at**.
- [ ] Containers → Export → edit a container directly: the same fields appear, including the photo pickers.

**Activity log**

- [ ] Edit → Activity log. Expected: milestone changes and any saved field edits appear as rows (when, event, actor, summary); click an entry to inspect old/new values.
- [ ] Attach or remove a container attachment, then Save. Expected: an activity row records the attachment change (old vs new media ids).
- [ ] Press Save without changing anything. Expected: no new activity row appears.

**Customer portal**

- [ ] Log in to the portal as a customer user and open a tracked container. Expected: the details grid shows the **Tracking position** text when it is filled in; there is no location-history table.

## 11. Users & Companies CRUD (2026-09-15)

**Prerequisites**

- Run `php artisan migrate:fresh --seed` first (schema was rebuilt).
- Admin panel: log in as `superadmin@example.com` / `password` (or `admin@example.com` / `password`).
- Customer portal: `http://localhost:8000/login` — OTP codes appear directly on the verify screen when `OTPZ_EXPOSE_IN_DEV=true` is set in `.env`.

**Users**

- [ ] CRM → Users → New user: fill name, email, optional phone, password. Expected: user appears in the list with the `customer` role assigned by default.
- [ ] Edit that user: change phone, toggle Active off → Save. Expected: changes persist; phone is visible via the column picker (hidden by default).
- [ ] Create a user with an email that already exists. Expected: validation error, no duplicate created.
- [ ] CRM → Users: the **Companies** column lists each linked company by name. Expected: clicking a name opens that company's edit page; users with no company show `—`.
- [ ] Same list → open the filter panel → filter by a company. Expected: only users linked to that company are shown.

**Companies**

- [ ] CRM → Companies → New company: fill name, code, email, phone, address → Save. Expected: company listed; "Users" count badge = 0.
- [ ] Create another company with the same `code`. Expected: validation error (code must be unique).
- [ ] Toggle Active off on a company and filter by Active = No. Expected: company is found.
- [ ] Open a company → Edit. Expected: tabs **Export Shipments** and **Import Shipments** list only that company's shipments, each row with an **Open** action to its edit page.

**Manage portal users inside a company**

- [ ] Companies → New company → in the "Portal users" section, type a name that doesn't exist → click "Create …" in the dropdown → fill name + email (+ optional phone) → Create, then save the company. Expected: company created; the new user is attached AND listed in CRM → Users carrying the `customer` role badge.
- [ ] Same select on Edit: search and pick an existing user → Save. Expected: user is linked (CRM → Users shows them under "Companies" count, or reopen the company edit form).
- [ ] Portal check: at `/login`, request a code for the newly created user's email. Expected: code is issued (visible on screen in dev); after entering it the user lands on `/portal`.
- [ ] Edit the company → click the × on that user in the select → Save. Expected: link removed; the user still exists under CRM → Users.
- [ ] Set the user Active = off, then request an OTP again. Expected: "No active customer account matches that email address."
