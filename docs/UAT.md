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

## 0. Import loading fields above the repeater (2026-09-24)

- [ ] Bill of Ladings → Import → open a B/L → **Containers** tab. Expected: **Terminal name**, **Date of loading** and **Loading destination** sit above the container list, right after Description of goods / Packages / HS codes — not below it.

## 0. Dashboard Bill of Ladings widget (2026-09-24)

- [ ] Open `/admin` (the dashboard). Expected: the **Filament docs / GitHub widget is gone**; under the welcome card sits a **Bill of Ladings** section with two cards — **Export** and **Import**.
- [ ] Click each card. Expected: it opens the matching list (Bill of Ladings → Export / Import).

## 0. Export sailing dates on the Containers tab (2026-09-24)

- [ ] Bill of Ladings → Export → open a B/L → **Containers** tab. Expected: below the AJU number sit **Departure date** and **Arrival time / ETA**, disabled with "Locked until Step 7: Gate in CY" until that step is reached.
- [ ] **Shipping Details** tab. Expected: no Departure date / ETA / **Actual arrival** there — only the Step 2 booking fields.

## 0. B/L number in Shipment overview (2026-09-24)

- [ ] Open any B/L detail in the portal. Expected: **B/L number** is the first fact in **Shipment overview** (the page title above still shows it too).

## 0. Sailing + Containers card (2026-09-24)

- [ ] Open an Export B/L detail with sailing values. Expected: the sailing strip (route with both ports and their dates) sits at the top of the containers card, separated by a thin divider; no "Sailing information" header.
- [ ] Check **Shipment overview**. Expected: **Vessel name**, **Voyage number** and **Shipping line** appear there once their milestone is reached — before that they are absent.
- [ ] Open an Import B/L detail. Expected: same — one card below Tracking progress holding the sailing strip and the containers table.
- [ ] Check a shipment whose sailing milestone is not reached. Expected: the card shows only the containers table — no empty route strip and no dangling divider line.
- [ ] Look at the route strip. Expected: a small truck icon above a full-width dashed line; the line's ends carry a hollow dot (loading) and a filled dot (discharge); each port's label, name and date sit under its own end (discharge right-aligned on desktop, both stacked on mobile).

## 0. Tracking progress status labels (2026-09-24)

- [ ] Open a B/L detail with future steps → **Tracking progress**. Expected: future milestones are greyed with no "Upcoming" badge and no "Pending" line; the current milestone still shows its "Latest" badge.
- [ ] Expand a container. Expected: the **Cargo tracking** steps show no "Upcoming" badge either; only the current step keeps its "Latest" badge.

## 0. Progress fields without divider (2026-09-24)

- [ ] Open any B/L detail in the portal → **Tracking progress** at desktop width. Expected: the milestone fields on the right show no vertical divider line.
- [ ] Expand a container row → **Cargo tracking**. Expected: the step fields have no vertical divider either — both lists match.
- [ ] Resize to a narrow width. Expected: fields still stack under their milestone with a top divider, nothing overflows.

## 0. Containers table on B/L detail (2026-09-24)

- [ ] Open an Export and an Import B/L detail in the portal. Expected: the containers table starts directly at the top of the card — no "Containers" title, no count badge, no section header.
- [ ] Look at the header row. Expected: it sits flush under the rounded top (no gap or double border) and has no divider line before the blank chevron column — the last header cell reads as padding.
- [ ] Look at a container row with progress. Expected: the status column shows just the event name (e.g. "Pick up empty container at depot") — no "Latest:" prefix — and the subline under the container number shows the size (plus a VGM/Gross weight chip once unlocked) but no "Seal …" chip; the seal number stays in its own **Seal No.** column.
- [ ] Expand a container row. Expected: no "Container summary" heading and no fact tiles; the Track live button (container in progress with a tracking URL) and the photo strip stay above **Cargo tracking**; a container with neither shows only **Cargo tracking**.
- [ ] Resize to a narrow width. Expected: the table still scrolls horizontally and nothing overflows.

## 0. B/L detail consistency (2026-09-23)

- [ ] Open Export B/L detail. Expected: Shipment overview and Tracking progress share same card shell + header padding; the sailing strip and the Containers table share one card with no section header.
- [ ] Check Tracking progress vs Cargo tracking. Expected: same vertical line, numbered nodes, current step highlighted, upcoming steps greyed.
- [ ] Check pills. Expected: Completed green, In Progress amber, Cancelled red, same in overview + containers.
- [ ] Open Import B/L. Expected: Draft PIB block uses same card style; Your messages still works.
- [ ] Resize to mobile. Expected: no overflow, grids stack.

## 0. Portal containers, sailing card and summary (2026-09-23)

**Prerequisites**

- `php artisan migrate:fresh --seed` (demo data).
- Portal: log in as `customer@example.com` / `password` (Dewi, assigned to NUS, SIN and BJM).

- [ ] Open Export B/L `BL-EXP-0005`. Expected: the sailing strip at the top of the containers card shows Jakarta (IDJKT) → Singapore (SGSIN) — no arrival line yet — and **Shipment overview** lists vessel MV Ocean Voyager, voyage V-330 and line Maersk; in **Containers**, `MSKU9002004` reads "Container on the way to factory" with the logged time above the mini progress bar, and its seal `SL-0105` sits in the **Seal No.** column.
- [ ] Expand `MSKU9002004` on `BL-EXP-0005`. Expected: done steps are green checks, the current step is a highlighted brand-blue card with a "Current" badge, upcoming steps are greyed with no field values, and the step fields show Driver name, Vehicle / Truck Number and Tracking position.
- [ ] Open Import B/L `BL-IMP-0003` and expand `MSKU7788991`. Expected: the row reads "Empty container returned" with a full green bar and a `Gross weight 14250.000 kg` chip; the sailing card shows Shanghai (CNSHA) → Surabaya (IDSUB) with **Actual arrival** (no ETA line); the "Open tracking link" field opens a new tab.
- [ ] Open Import B/L `BL-IMP-0001`. Expected: the summary shows an **In Progress** status pill, **Document received date** and no "Completed at"; each container row reads "Not started" (the note inside says the journey starts at Container inspection) except `CMAU7654324`, which reads "Cancelled" with the red note; `CMAU7654321` shows its photos while `CMAU7654323`'s internal-only photos stay hidden. Keep a container expanded, send a **Request revision** message and resize to mobile width — the container stays open and nothing overflows.
- [ ] Portal home (`/portal`). Expected: the table has **POD / Vessel arrival** (`BL-IMP-0001` shows Surabaya (IDSUB) with "ETA 28 Sep 2026 10:34") and **Document received date** columns, no "Latest place" column, and `—` for rows with nothing reached.

## 0. Draft PIB revision notes (2026-09-23)

**Prerequisites**

- `php artisan migrate:fresh --seed` (demo data).
- Portal: log in as `customer@example.com` / `password` (Dewi, assigned to SIN).
- Admin: log in as `admin@example.com` / `password`.

- [ ] Portal → open `BL-IMP-0001` → **Draft PIB confirmation** → type a message in "Need a change? Tell us what to revise" → **Request revision**. Expected: "Your revision request has been sent." and the message appears under **Your messages** with a "Sent …" time.
- [ ] Reload the page. Expected: the message is still listed under **Your messages** — it was saved, not just flashed.
- [ ] Admin → Bill of Ladings → Import → open `BL-IMP-0001` → **Notes** tab. Expected: the customer's message is listed with the customer's name as author, on an **amber card** with a **Customer** tag; office notes keep the plain style.
- [ ] Same shipment → **Shipping Details** tab → below **Confirmation checklist**. Expected: an amber strip "The customer sent you a note." with a **View in Notes tab** link; clicking it switches to the Notes tab without a page reload.
- [ ] Tick **Confirmation checklist** on a shipment and Save → below the field shows **"Confirmed by {your admin name}"**. Untick and Save → the line disappears. When the customer confirms from the portal instead, it shows the **customer's** name.
- [ ] Add an office note in that same **Notes** tab, then reload the portal page. Expected: the office note does **not** appear under **Your messages** — only the customer's own notes show.
- [ ] `sari@java-retail.test` opens `BL-IMP-0002` (already confirmed). Expected: the revision box is not offered; if she had sent messages earlier they would still be listed.

## 0. Portal shipment fields in Tracking progress (2026-09-23)

**Prerequisites**

- `php artisan migrate:fresh --seed` (demo data).
- Log in to the customer portal as `agus@borneo.test` / `password` (Agus, assigned to JRD and SNI).

- [ ] Portal home → open Import B/L `BL-IMP-0002` at desktop width → **Tracking progress**. Expected: milestone title and date are on the left, fields are on the right without a vertical divider, and values match the milestone text size while labels stay smaller.
- [ ] Resize the same page to a narrow/mobile width. Expected: fields stack below their milestone without horizontal overflow.
- [ ] Open Export B/L `BL-EXP-0003` at desktop width → **Tracking progress**. Expected: booking, pickup/stuffing, AJU and sailing fields appear to the right of their matching milestones with no vertical divider.
- [ ] Open Import B/L `BL-IMP-0001` → **Tracking progress**. Expected: **Response billing** fields appear to the right; future steps stay greyed without field values.
- [ ] Compare a B/L’s **Tracking progress** with its **Containers** section. Expected: container-specific values stay outside the shipment timeline.

## 0. Combined Bill of Ladings list in customer portal (2026-09-22)

**Prerequisites**

- `php artisan migrate:fresh --seed` (demo data).
- Log in to the customer portal as `customer@example.com` / `password` (Dewi).

- [ ] Open the portal home (`/portal`). Expected: **one** combined list showing both Export and Import B/Ls, newest first; each row has an **Export** or **Import** badge.
- [ ] Check the **Type** filter defaults to **All**. Then switch it to **Export**. Expected: only export rows remain. Switch to **Import**. Expected: only import rows remain.
- [ ] Search box: type a container number from an export shipment (e.g. `MSKU1234567`). Expected: the matching B/L row appears; a result count like "1 shipment found" is shown.
- [ ] Set filters that yield no rows (e.g. search a nonsense string). Expected: "0 shipments found" and the empty-state message.
- [ ] Click **Clear filters** after setting several filters. Expected: all inputs reset to All/blank and the full combined list returns.
- [ ] Click a B/L number on an Import row. Expected: it opens the import detail page. Same for an Export row: opens the export detail page.
- [ ] On a detail page, check **Tracking progress**. Expected: the process steps in order — reached steps with datetimes, the current step marked latest, upcoming steps greyed with no status label.
- [ ] With 16+ visible shipments, use the pagination links. Expected: page 2 shows the next rows; the count reflects the total across both types.

## 0. SPJM milestone cannot skip the branch (2026-09-22)

**Prerequisites**

- Admin panel; log in as `admin@example.com` / `password`.
- `php artisan migrate:fresh --seed` (demo data).

- [ ] Open import B/L `BL-IMP-0012` (response **AP**, sitting on **Response billing**). In **Shipping Details**, set **Billing response** to **SPJM** (do not save), then in the stepper click the next step (**Container shipping schedule**). Expected: the jump is refused — the milestone stays on **Response billing**.
- [ ] Add a step: with response **AP** saved on **Response billing**, click the next step. Expected: it moves to **Container shipping schedule** (a non-SPJM response has no SPJM steps).
- [ ] Open an SPJM shipment (`BL-IMP-0001`) sitting on an SPJM-only step, change the response off SPJM and Save. Expected: the milestone resets to **Response billing**.

## 0. Demo condition seeders (2026-09-22)

- [ ] Run `php artisan migrate:fresh --seed`. Expected: seeding finishes and re-running `php artisan db:seed` adds nothing (idempotent).
- [ ] Bill of Ladings → Export. Expected: rows covering draft, early milestones, cancelled and LCL/air modes (e.g. `BL-EXP-DRAFT`, `BL-EXP-0006`); the draft does **not** appear in the customer portal.
- [ ] Bill of Ladings → Import. Expected: rows covering draft, checking document, AP and SPJK responses (e.g. `BL-IMP-0010`, `BL-IMP-0012`, `BL-IMP-0013`).
- [ ] Containers → Export/Import. Expected: containers in **pending**, **in_progress**, **completed** and **cancelled** states.
- [ ] Open `CMAU7654321` (import) or `MSKU1234567` (export). Expected: the photo slots show demo placeholder images; on an import shipment's **Activity log** tab a demo trail is visible, with internal entries hidden in the portal.

## 0. Containers tables — company filter + clickable company (2026-09-22)

**Prerequisites**

- Admin panel; log in as `admin@example.com` / `password`.
- `php artisan migrate:fresh --seed` (demo data).

- [ ] Open **Containers → Export** (and **Containers → Import**). Expected: a **Company** filter appears in the filter dropdown, next to Status.
- [ ] Pick a company and **Apply**. Expected: only that company's containers are listed.
- [ ] In the **Company** column, click a company name. Expected: it opens that company's edit page.

## 0. Dropped Loading-in-factory date & export container type (2026-09-22)

- [ ] Import B/L → Containers tab → expand a container at `Container arrived in factory`. Expected: only **Loading in factory status**; there is **no Loading in factory date/time** field.
- [ ] Export container form (and Bill of Ladings → Export Containers table). Expected: there is **no Container Type** field and **no Type column**.

## 0. Add container to an import B/L (2026-09-22)

**Prerequisites**

- Admin panel; log in as `admin@example.com` / `password`.
- `php artisan migrate:fresh --seed` (demo data).

- [ ] Open an import B/L (e.g. `BL-IMP-0001`) → **Containers** tab. Click **Add to containers**. Expected: a new collapsed container row appears immediately.
- [ ] Expand the new row. Expected: **Description of goods**, **Packages** and **HS codes** are pre-filled from the shipment (overridable).
- [ ] Fill **Container number** and click **Save changes**. Expected: save succeeds and the container is listed with its seeded cargo.

## 0. Admin list date filters — Year / Month / date range (2026-09-22)

**Prerequisites**

- Admin panel; log in as `admin@example.com` / `password`.
- `php artisan migrate:fresh --seed` (demo data).
- All demo records are created "today", so they share one year and month. To see two years, backdate one B/L first:
  `php artisan tinker --execute 'App\Models\ExportShipment::query()->where("bl_number", "BL-EXP-0001")->update(["created_at" => "2023-05-10 08:00:00"]);'`

- [ ] Open **Bill of Ladings → Export** and click the filter icon. Expected: three new filters appear after Status / Company — **Year**, **Month** and **Created between** (From / Until), before Trashed.
- [ ] Pick **Year** = the year of the backdated B/L, then click **Apply**. Expected: only that B/L is listed.
- [ ] Pick **Year** = the current year. Expected: the other B/Ls are listed and the backdated one is gone.
- [ ] With the current year still selected, pick **Month** = the month of the backdated B/L (May). Expected: the list is empty, because the year filter keeps 2026 while the month asks for May.
- [ ] Clear **Month** and set **Created between** From = `2023-05-01`, Until = `2023-05-31`. Expected: only the backdated B/L is listed.
- [ ] Set From = `2023-01-01`, Until = `2023-01-31`. Expected: the list is empty (nothing was created in January 2023).
- [ ] Remove all three filters (x on each badge, or **Reset**). Expected: the full list comes back.
- [ ] Repeat the Year check on **Bill of Ladings → Import**, **Containers → Export** and **Containers → Import**. Expected: the same three filters exist on each list and hide/show rows the same way.
- [ ] Open **CRM → Companies**, pick a company with shipments and open its **Shipments** tab (Export and Import). Expected: the same three filters, scoped to that company's B/Ls.
- [ ] On a container list, combine **Year** with a **Created between** range that covers today. Expected: only containers created in that window stay listed.

## 0. Import cargo fields, tracking URL and container defaults (2026-09-21)

**Prerequisites**

- Admin panel; log in as `admin@example.com` / `password`.
- `php artisan migrate:fresh --seed`.
- Open `BL-IMP-0001` (SPJM, sits at **Waiting process bahandle**, so Step 11 is already reached).

- [ ] Edit `BL-IMP-0001` → **Shipping Details**. Expected: only **Response billing** remains of the Step 11 group — **Description of goods**, **Packages** and **HS codes** have moved to the Containers tab.
- [ ] **Containers** tab. Expected: **Description of goods**, **Packages** and **HS codes** above the containers repeater (unlocked at `Step 11: Response billing`); **Terminal name**, **Date of loading** and **Loading destination** sit **above** the repeater too, under the cargo fields (locked until `Step 17: Container shipping schedule`).
- [ ] Expand container `CMAU7654321`. Expected: **Container Size**, **Description of goods**, **Packages** and **HS codes** are shown and unlocked (Step 11), next to the container number.
- [ ] Check the cargo fields on the container. Expected: they start from the shipment values (`Electronic components` / `85 cartons` / `8542.31`) and can be changed per container without affecting the shipment.
- [ ] Change a container's **Description of goods** and Save. Expected: the container keeps the overridden value; the shipment's own Description of goods is unchanged.
- [ ] Change a container's **HS codes** selection and Save; reopen the container. Expected: the selected codes persist for that container only.
- [ ] Set **Tracking position (url)** on a container to `not a url` and Save. Expected: a validation error; a full `https://…` URL saves and persists.
- [ ] Open **Containers → Import → New**, pick `BL-IMP-0001`, fill Container Number, leave the cargo fields empty and Save. Expected: the new container inherits the shipment's Description of goods, Packages and HS codes.
- [ ] Reopen that new container, change one cargo value and Save. Expected: the change sticks; the shipment is untouched.
- [ ] Export the **Containers → Import** table to CSV. Expected: the export includes **Tracking position (url)**, **Description of goods** and **Packages**; the old manual tracking field is gone.

## 0. Loading / Discharge location columns (2026-09-21)

**Prerequisites**

- Admin panel; log in as `admin@example.com` / `password`.
- `php artisan migrate:fresh --seed`.

- [ ] Open **Bill of Ladings → Export**. Expected: a **Loading** and a **Discharge** column appear (may need the column-toggle menu if hidden); empty values show `—`.
- [ ] Type a port name into the table search box. Expected: rows filter by loading/discharge location.
- [ ] Repeat on **Bill of Ladings → Import**. Expected: the same **Loading** / **Discharge** columns.
- [ ] Open **CRM → Companies → (edit a company)**, look at the **Export B/Ls** and **Import B/Ls** panels. Expected: both show **Loading** and **Discharge**.
- [ ] Open a shipment's **edit** form and set **Port of loading** / **Port of discharge**, save, and return to the list. Expected: the table reflects the saved values.
- [ ] Hide the two columns via the column-toggle menu and reload. Expected: the choice sticks (they are toggleable).

## 0. Shipment tables show Created / Updated instead of ETA (2026-09-21)

**Prerequisites**

- Admin panel; log in as `admin@example.com` / `password`.
- `php artisan migrate:fresh --seed`.

- [ ] Open **Bill of Ladings → Export**. Expected: the table no longer has an **ETA** column; it shows **Created** and **Updated** (date + time), both sortable.
- [ ] Repeat on **Bill of Ladings → Import**. Expected: same **Created** / **Updated** columns, no ETA.
- [ ] Open **CRM → Companies → (edit a company)** and look at the **Export B/Ls** panel. Expected: **Created** and **Updated** columns, no ETA.
- [ ] In the same company page, check the **Import B/Ls** panel. Expected: **Created** and **Updated** columns, no ETA.
- [ ] Confirm ETA is not lost elsewhere: open a shipment's **edit** form. Expected: the **ETA** field is still there and editable.
- [ ] Customer portal: open a shipment. Expected: the ETA estimate line is still shown to customers.

## 0. Delete rights by role (2026-09-21)

**Prerequisites**

- Admin panel; log in as `superadmin@example.com`, `admin@example.com` or `operator@example.com` (all `password`).
- `php artisan migrate:fresh --seed`.

**Super admin — permanent delete**

- [ ] Log in as `superadmin@example.com` → **Master data → HS codes**. Expected: a **Force delete** bulk action appears (select rows → bulk menu).
- [ ] Select a row → **Delete**. Expected: the row leaves the list; turn on the **Trashed** filter to see it again.
- [ ] With the row trashed, choose **Force delete**. Expected: the row is permanently removed; it no longer appears even under the **Trashed** filter.

**Admin — soft delete and recover only**

- [ ] Log in as `admin@example.com` → **CRM → Companies**. Expected: a **Delete** bulk action and a **Restore** action appear, but **no Force delete** action.
- [ ] Select a company → **Delete**. Expected: the company disappears from the default list.
- [ ] Set the **Trashed** filter (top-right of the table). Expected: the deleted company is listed.
- [ ] Select it → **Restore**. Expected: the company returns to the default list.
- [ ] Repeat for **Users** and **Master data → HS codes**.
- [ ] On **Bill of Ladings → Export/Import** and **Containers → Export/Import**, log in as admin. Expected: **Delete** and **Restore** appear, **Force delete** does not.
- [ ] As admin, open **Users → (edit a user)** and delete that user. Expected: the user is soft-deleted; logging in as that account is refused, and the admin panel is unreachable for it until restored.

**Operator — no delete at all**

- [ ] Log in as `operator@example.com` → **Bill of Ladings → Export/Import**. Expected: **no** Delete, Restore or Force delete actions appear (only view/edit).
- [ ] Open **CRM → Companies**, **Users** directly by URL. Expected: access is denied (admin-only resources).
- [ ] Confirm the **Prune old data** button is absent everywhere for the operator.

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

- [ ] As **`superadmin@example.com`**, open any of the seven tables. Expected: a red **Prune old data** button appears next to **Export**.
- [ ] As **`admin@example.com`**, open the same tables. Expected: the **Export** button appears, but the **Prune old data** button is **hidden** (permanent deletion is super-admin only).
- [ ] Click **Prune old data**. Expected: a confirmation dialog says how many records would be permanently deleted and states the cutoff (3 years ago), with a **Delete permanently** button.
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
- [ ] **Document received by** (same on the import form): open the dropdown. Expected: only **staff accounts** are offered (admin / super admin / operator) — customer accounts never appear.
- [ ] Shipping Details order: **B/L number, DO number, Shipping line, Vessel name, Voyage, Port of loading, Port of discharge, Closing time at depot, Closing time at CY, Shipment mode** (no AJU number here — it lives on the Containers tab, unlocked at Step 5; **Goods description is gone from the export form**). **Departure date and Arrival time / ETA live on the Containers tab, after AJU number (locked until `Step 7: Gate in CY`); Actual arrival is removed from the export form**; **Status and Completed at now live in the Status tab** (locked until `Step 8: Final checking shipment details`; reaching the last step also completes the shipment automatically). **Pick up depot, Stuffing date (date + time) and Stuffing destination live on the Containers tab, above the repeater.** Package count, Package unit, Terminal name, Loading date and Loading destination are **gone**; **HS codes are import-only**.
- [ ] **Status** tab (between Containers and Notes). Expected: **Status** dropdown (draft / in progress / completed / cancelled) + **Completed at**, both editable once Step 8 is reached; Save persists them and the Activity log records the change.
- [ ] At Step 1 the fields show `Locked until Step 2: Checking booking order`. Click **Advance** → they become editable.
- [ ] Containers tab → expand `MSKU1234567`. Expected: identity + driver + photos unlocked at "Pick up empty container"; **Tracking position** unlocks at "Container on the way to factory"; **Stuffing status at Factory** (On Process / Finished, defaulting to On Process) at "Stuffing at factory / PEB & NPE"; **Port of loading** + **Gate in CY** at "Checking PEB & NPE"; **VGM (kg)** at "Gate in CY"; **Final checked** (toggle) at "Final checking".
- [ ] Containers tab, above the repeater. Expected: **AJU number** appears under the pickup/stuffing fields, locked until `Step 5: Stuffing at factory / PEB & NPE`; advancing to Step 5 makes it editable and the Activity log records changes to it.

**Import shipment form (IMPORT.md spec)**

- [ ] Bill of Ladings → Import → New works the same way (Customer only on create).
- [ ] Open `BL-IMP-0001` → Edit. Expected: the stepper shows the SPJM branch (21 steps) in three rows — row 1: the 10 steps up to **Payment billing**; row 2: **Response billing** followed by the **five SPJM-only steps in red** (Upload all document, Waiting process bahandle, Payment bahandle, Container inspection, Waiting change status SPJM to SPPB); row 3: the remaining 5 steps. The header shows a **New import B/L** action that opens the create form. Shipping Details unlocks in this order: **B/L number** at `Step 2: Checking document`; **Shipping line** at `Step 3: Draft PIB`; **Vessel name** at `Step 4: Checking draft PIB to importir`; **Confirmation checklist** toggle at `Step 5: Waiting confirmation from customer`; **AJU number**, **Voyage** and **Status billing** at `Step 6: Final sending PIB to custom (issuing billing)`; **Port of loading** at `Step 7: Process payment THC`; **Departure date** at `Step 8: Waiting release DO`; **Port of discharge** at `Step 9: DO release`; **Arrival time / ETA** at `Step 10: Payment billing`; **Response billing** at `Step 11: Response billing` (Description of goods, Packages and HS codes moved to the Containers tab).
- [ ] Containers tab. Expected: **Description of goods**, **Packages** and **HS codes** above the containers repeater (unlocked at `Step 11: Response billing`); **Terminal name**, **Date of loading** and **Loading destination** sit **above** the repeater too, under the cargo fields — locked until `Step 17: Container shipping schedule` on the SPJM shipment (Step 12 on a non-SPJM shipment).
- [ ] Set **Response billing** = SPPB on a shipment at Step 11. Expected: the stepper drops the red SPJM block (row 2 becomes Response billing + the normal steps, 16 steps total) and the next step is **Container shipping schedule**.
- [ ] `BL-IMP-0002` (completed, SPPB) shows the same shortened sequence.
- [ ] SPJM demo data: `BL-IMP-0003` (completed SPJM, SIN) shows the whole red block done and the shipment completed; `BL-IMP-0004` (BJM) sits at **Response billing** with the red block upcoming and its containers carrying only the number (no size yet).
- [ ] **Status** tab (after Containers): Status + Completed at, editable once the last step is reached.
- [ ] Containers tab (repeater unlocks at `Step 11: Response billing`): expand `CMAU7654321`. Expected: container number, **Size**, **Gross weight (kg)**, **CBM / measurement**, **Description of goods**, **Packages** and **HS codes** (in that order, all at Step 11) — the cargo fields start from the shipment values and can be overridden per container; photos at Step 11; **Gate out CY** + **Driver name** + **No. License** at `Gate out from inbound terminal`; **Tracking position driver** + **Tracking position (url)** at `Container on the way factory`; **Loading in factory** + status at `Container arrived in factory`; **Return depot name** + **Return date** at `Empty container returned`. There is **no separate gross weight unit** field.
- [ ] Set a container's **Tracking position (url)** to a plain text value (e.g. `not a url`) and Save. Expected: a validation error appears; entering a full URL (`https://…`) saves.
- [ ] Removed per IMPORT.md: no DO number, no payment status/timestamps, no draft-PIB status/notes fields, no inspection fields, no container Type/Seal on import.

**Standalone containers**

- [ ] Containers → Export → New: pick an **Export shipment**, fill Container Number → Save. Expected: saved and listed; edit page shows the export fields only.
- [ ] Containers → Import → New: same with an **Import shipment**; edit page shows the import fields only.

**Portal**

- [ ] Log in as `customer@example.com` → portal home has a **Type** dropdown (Export / Import) next to the company/status filters; Export lists NUS/BJM exports; selecting Import lists the SIN import.
- [ ] Open `BL-EXP-0001` → Export shipment page: summary, tracking progress, and the sailing + containers card whose rows expand in place.
- [ ] Open `BL-IMP-0001` → Import shipment page: facts, **Draft PIB confirmation** with Confirm / Request revision, journey, containers.
- [ ] On `BL-EXP-0001` and `BL-IMP-0001`, expand a container row (`MSKU1234567`, `CMAU7654321`). Expected: customer-visible photos and the per-step journey with the admin field labels (export: driver/tracking/stuffing/VGM/final check; import: gate-out/driver/return).
- [ ] `sari@java-retail.test` opens `BL-IMP-0002` (already confirmed): the Confirm and Request revision actions are **not** offered.
- [ ] A **draft** shipment (Status = Draft) is **not** listed in the portal, and opening its shipment URL returns **404**. Advance it past **Document received** → it becomes In progress and appears with its containers.

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

- [ ] At the pickup step an export container's fields appear in this order: **Container Number**, **Container Size**, **Seal Number**, **Driver name**, **Vehicle / Truck Number**, **Driver License Number**, then the five photos.
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
- [ ] **Shipping Details** tab (export). Expected: the first fields are the Step 2 group, in this order — **B/L number**, **DO number**, **Shipping line**, **Vessel name**, **Voyage**, **Port of loading**, **Port of discharge**, **Closing time at depot**, **Closing time at CY**, **Shipment mode** (no AJU number here — it lives on the Containers tab at Step 5; no **Goods description** or HS codes field on export).
- [ ] Export the **Bill of Ladings → Export** table to CSV. Expected: the file has **no Goods description column** (it was removed); the other shipment fields and relations are still there.
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

**Sailing information**

- [ ] Portal → open `BL-IMP-0003`. Expected: the sailing strip (no header) shows Shanghai (CNSHA) → Surabaya (IDSUB) with the departure date and **Actual arrival**; no ETA line once the actual arrival exists. Vessel, voyage and shipping line sit in **Shipment overview**.
- [ ] Portal → open `BL-IMP-0001` (still sailing). Expected: the card shows Shanghai (CNSHA) → Surabaya (IDSUB) with **Arrival time / ETA** instead of an actual arrival; on `BL-EXP-0005` (earlier milestone) the strip shows the route but no arrival line — values whose milestone is not reached never appear.
- [ ] Expand `MSKU7788991` on `BL-IMP-0003`. Expected: the container's step-by-step journey reads inspection → gate out → factory → empty return with its logged times.
- [ ] Open `BL-IMP-0001` (containers not started). Expected: each container row reads "Not started" and the journey inside begins with "Container inspection" marked upcoming.

**Shipment page**

- [ ] Portal → open a shipment. Expected: a **Tracking progress** section sits below the sailing card with the shipment-level milestone fields beside their steps; upcoming steps stay pending with no values.

**Dashboard list**

- [ ] Portal home → shipment table. Expected: **POD / Vessel arrival** and **Latest event** (+ time) columns appear per row; rows with no journey yet show `—`.
- [ ] With **Type = Export**, search `EGHU6677881` (as `agus@borneo.test`, who manages SNI). Expected: only `BL-EXP-0003` matches; its latest event reads "Final checking shipment details".

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
