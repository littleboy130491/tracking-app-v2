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

## 1. B/L form layout + customer permissions (2026-09-18)

**Prerequisites**

- Run `php artisan migrate:fresh --seed` first.
- Admin panel: `http://localhost:8000/admin` — log in as `admin@example.com` / `password` (second account: `operator@example.com` / `password`).

**Create page**

- [ ] Bill of ladings → New. Expected: only the **Customer** section is shown — a full-width section with shipment type, Customer, Document received date and Document received by. No milestone stepper, no tabs, no empty box under the fields.

**Edit page layout (as admin)**

- [ ] Open a B/L → Edit. Expected, top to bottom: full-width **Customer** section, then the **milestone stepper**, then the tabs **Shipping Details | Containers | Notes | Activity log**.
- [ ] Open the **Containers** tab. Expected: the container repeater with expandable items (it is no longer nested inside Shipping Details).

**Customer field permissions**

- [ ] As **admin**, open a B/L → Edit. Expected: the **Customer** dropdown is visible and editable; **Customer name** shows the snapshot.
- [ ] As **operator** (`operator@example.com`), open a B/L → Edit. Expected: the **Customer** dropdown is **not** shown; only the read-only **Customer name** is displayed.
- [ ] As operator, Save the form. Expected: no validation error and the customer is unchanged.

**Locked field jumps to milestone**

- [ ] On Edit, find a field showing `Locked until Step N: …` (e.g. **DO number** → Step 2). Expected: the message is a green clickable link with a small step-number dot.
- [ ] Click that message. Expected: the page smooth-scrolls up to the milestone stepper and the matching dot (same number N) pulses green for ~2 seconds.
- [ ] Repeat for a different step (e.g. import B/L **DO released at** → Step 7) and confirm the correct dot pulses.

## 2. Export spec fields + latest event (2026-09-17)

**Prerequisites**

- Run `php artisan migrate:fresh --seed` first.
- Admin panel: `http://localhost:8000/admin` — log in as `admin@example.com` / `password`.

**Document received (Customer section)**

- [ ] Bill of ladings → New. Expected: the **Customer** section shows **Document received date** (defaulted to today) and **Document received by** (default = you); both required.
- [ ] Reopen that shipment → Edit → **Customer** section. Expected: the date is editable; **Document received by** is disabled for operators and editable for admin/super_admin.

**Container fields (Containers tab → expand a container)**

- [ ] At the pickup step each container shows **Driver license number** below the truck plate.
- [ ] At "Container on the way to factory" the container shows **Tracking position** and **Tracking position (url)**.
- [ ] The containers area shows five photo pickers — **Photo — door / floor / seal / EIR** and **Additional photos**. Expected: pick or upload into each slot → Save → reopen: the photos stay in their slots.
- [ ] Remove a photo from a slot and Save. Expected: it detaches from the container (the file stays in the media library).
- [ ] At "Checking PEB & NPE", a container's **Gate in port** starts as the B/L's **Port of loading** (e.g. `Jakarta (IDJKT)` for REF-EXP-0001) and stays editable.

**Latest event**

- [ ] Save any change on a B/L (or move a milestone). Expected: the shipment's `latest_event` updates — visible in the Activity log as the newest row with the matching time.
- [ ] Change a container-level field (e.g. stuffing status) and Save. Expected: both the container row and its B/L show the container event as their latest event.

## 3. Notes on shipments, containers, companies, users (2026-09-17)

**Prerequisites**

- Run `php artisan migrate:fresh --seed` first.
- Admin panel: `http://localhost:8000/admin` — log in as `admin@example.com` / `password` (second account: `operator@example.com` / `password`).

**Adding and reading**

- [ ] Open a B/L → Edit → **Notes** tab. Expected: a textarea + **Add note** button; empty state reads "No notes yet."
- [ ] Add a note. Expected: it appears immediately (no form Save needed) with your name and "just now".
- [ ] Open the same B/L as `operator@example.com`. Expected: you see the admin's note, but it has no Edit/Delete links.
- [ ] As the operator, add your own note. Expected: yours shows Edit and Delete links; the admin's still does not.
- [ ] Edit your note → Save. Expected: the text updates and an "edited" marker appears.
- [ ] Delete your note and confirm. Expected: it disappears.
- [ ] The same **Notes** section appears on: container Edit, company Edit and user Edit pages. Expected: identical add/read/edit-own behaviour everywhere.

**Audit**

- [ ] Back on the B/L → Activity log tab. Expected: `note_created` / `note_updated` / `note_deleted` rows naming you as the actor.
- [ ] Shipments → Activity logs as the operator. Expected: note entries for unassigned companies' shipments are not listed.

## 4. Customer section + Shipping Details Step 2 (2026-09-17)

**Prerequisites**

- Run `php artisan migrate:fresh --seed` first.
- Admin panel: `http://localhost:8000/admin` — log in as `admin@example.com` / `password`.

**Customer section (create)**

- [ ] Bill of ladings → New. Expected: the **Customer** section shows **Shipment type**, **Customer**, **Document received date** and **Document received by**; no AJU, B/L, or mode fields. Saving with just the required fields succeeds.
- [ ] Reopen that shipment → Edit → **Customer** section. Expected: **Shipment type** is disabled (locked) and **Customer name** is shown read-only.

**Shipping Details + stepper**

- [ ] Same shipment → Edit. Expected: the milestone stepper sits **above the tabs** (not inside Shipping Details); on New it is absent.
- [ ] **Shipping Details** tab. Expected: the first fields are the Step 2 group, in this order — **AJU number**, **DO number**, **Shipping line**, **Vessel name**, **Voyage**, **Port of loading**, **Port of discharge**, **Closing time at depot**, **Closing time at CY**, **Shipment mode**, **B/L number**.
- [ ] On a shipment still at Step 1, those fields show `Locked until Step 2: Checking booking order`. Move it to Step 2 → all become editable at once.

## 5. Admins see all shipments in the portal (2026-09-17)

**Prerequisites**

- Run `php artisan migrate:fresh --seed` first.
- Portal login: `http://localhost:8000/login` — OTP codes appear on the verify screen when `OTPZ_EXPOSE_IN_DEV=true`. Use `admin@example.com` (sees everything) vs a customer email (scoped).

**Admin portal access**

- [ ] At `/login`, request a code for `admin@example.com`. Expected: code is issued (admins may use the portal login); for `operator@example.com` the same request shows "No active customer account matches that email address."
- [ ] Log in as `admin@example.com` → portal home. Expected: all five demo shipments are listed (`REF-EXP-0001/0002/0003`, `REF-IMP-0001/0002`); the company filter offers every company.
- [ ] Open a shipment the admin has no link to (e.g. `REF-IMP-0002` / JRD) and one of its containers. Expected: both pages open normally.
- [ ] Log in as a customer (e.g. `rina@nusantara.test`). Expected: only their companies' shipments appear; guessing another company's shipment URL still 404s.

## 6. Operator row-level scope (2026-09-17)

**Prerequisites**

- Run `php artisan migrate:fresh --seed` first — the seeder assigns `operator@example.com` to NUS and SNI.
- Admin panel: `http://localhost:8000/admin` — log in as `operator@example.com` / `password` (then compare with `admin@example.com` / `password`).

**Shipments**

- [ ] As operator, open **Shipments → Bills of lading**. Expected: only **REF-EXP-0001** (NUS) and **REF-EXP-0003** (SNI) are listed — not the SIN/BJM/JRD shipments.
- [ ] Open one of the hidden shipments by pasting its edit URL. Expected: **404** page.
- [ ] Open REF-EXP-0001 → Edit → Shipping Details. Expected: the page works normally (containers, attachments, activity log tab all scoped to this shipment).
- [ ] Create a new B/L → **Customer** dropdown. Expected: only **PT Nusantara Ekspor** and **PT Sulawesi Nickel Industri** are offered.
- [ ] **Containers** list → B/L filter dropdown. Expected: only the operator's two B/Ls appear; container rows match them.
- [ ] **Activity logs** list. Expected: only entries for the operator's shipments/containers; no rows for other companies.
- [ ] Log out, log in as `admin@example.com` → same lists. Expected: every shipment, container and log is visible.

**Portal (customers)**

- [ ] Customer portal → log in as `customer@example.com`. Expected: only NUS/SIN/BJM shipments appear (unchanged behaviour; portal scoping matches the admin rule).
- [ ] Paste another company's shipment URL. Expected: **404**.

## 7. Staff impersonation (2026-09-17)

**Prerequisites**

- Run `php artisan migrate:fresh --seed` first.
- Admin panel: `http://localhost:8000/admin` — log in as `admin@example.com` / `password` (or `superadmin@example.com` / `password`).

**Impersonate from the users list**

- [ ] CRM → Users. Expected: each row shows an impersonate icon; your own row does not.
- [ ] Click the impersonate icon on a customer row. Expected: you land on `/` as that user (portal for customers) and a dark banner reads "Impersonating {name}" with a leave link.
- [ ] Click the leave link. Expected: you return to the admin users list as yourself.
- [ ] Log in as `operator@example.com` / `password` → CRM → Users. Expected: 403 (operators never see the list, so no impersonate icon).
- [ ] As `admin@example.com`, open the edit page of `superadmin@example.com`. Expected: no impersonate button (admins cannot impersonate super admins).
- [ ] As `superadmin@example.com`, open any user edit page. Expected: impersonate button present in the header.

## 8. Portal journey timeline like the reference tracker (2026-09-16)

**Prerequisites**

- Run `php artisan migrate:fresh --seed` first.
- Customer portal: `http://localhost:8000/login` — use a seeded customer email (e.g. `customer@example.com`); OTP codes appear on the verify screen when `OTPZ_EXPOSE_IN_DEV=true`.

**Container page**

- [ ] Portal → open a shipment → open a container in a new tab. Expected: a **Sailing information** block shows vessel, line, POL/departure and POD/arrival (actual) or ETA (estimate).
- [ ] Same page → **Journey** section. Expected: oldest-first rows with time + place; the last row carries a `latest` badge; an ETA-only arrival carries an `estimate` badge.
- [ ] Open the completed export container `EGHU6677881`. Expected: journey reads pickup → stuffing → gate-in → final check → departure → arrival.
- [ ] Open a container with no dates yet. Expected: journey shows "No journey events have been recorded for this shipment yet."

**Shipment page**

- [ ] Portal → open a shipment. Expected: a **Journey** section sits above the containers table with the shipment-level voyage dates plus visible log entries (e.g. milestone moves, PIB confirmation).

**Dashboard list**

- [ ] Portal home → shipment table. Expected: **Latest place** and **Latest event** (+ time) columns appear per row; rows with no journey yet show `—`.
- [ ] Search `EGHU6677881` (as `agus@borneo.test`, who manages SNI). Expected: only `REF-EXP-0003` matches; its latest event reads "Vessel arrival at port of discharge".

## 9. B/L Progress milestones & audit (2026-09-16)

**Prerequisites**

- Run `php artisan migrate:fresh --seed` first (schema was rebuilt).
- Admin panel: `http://localhost:8000/admin` — log in as `admin@example.com` / `password`.

**Shipping Details layout**

- [ ] Bill of ladings → New: the form shows only the **Customer** tab; **Shipping Details** and **Activity log** are absent.
- [ ] Create a shipment, then open its Edit → **Shipping Details**. Expected: fields are laid out continuously; no card boxes or collapse controls.
- [ ] The horizontal milestone stepper appears at the top; completed steps are green, the current step is highlighted, later steps are grey.
- [ ] A field belonging to a not-yet-reached step is disabled and shows `Locked until Step X: {step name}` underneath; fields at or before the current step are editable and show no milestone text.
- [ ] Click a future step more than one ahead. Expected: it is not clickable. Click the next step and confirm the Filament modal. Expected: progress advances one step and the newly unlocked fields become editable.
- [ ] Click an earlier step and confirm. Expected: progress moves back and its fields remain editable.

**Containers**

- [ ] The containers area sits in a collapsible **Containers** section; each container item is collapsed by default showing only its container number. Expected: expanding the section and clicking a container reveals its flat milestone field list.
- [ ] At the pickup step (Step 2), add a container. Expected: a collapsed item appears; expand it — fields show as a flat list, each disabled field naming the step that unlocks it.
- [ ] Each container item shows an **Attachments** picker at the pickup step. Expected: click it → the Curator media library opens; pick existing files or upload new ones → Save → files stay attached to that container.
- [ ] Reopen the B/L after saving. Expected: the picked attachments are still shown on the container; remove one and save → it detaches (the file itself remains in the media library).
- [ ] **Tracking position** is a single text field per container, unlocked at "Container on the way to factory" (Step 3) — not a repeatable list.
- [ ] At "Checking PEB & NPE" the container unlocks **Gate in port** + **Gate in CY** date; at "Gate in CY" it unlocks **VGM (kg)** (weight only, no unit selector); at "Final checking" it unlocks **Final checked** + **Final checked at**.
- [ ] CRM → Containers → edit a container directly: the same fields appear, including the Attachments section.

**Activity log**

- [ ] Edit → Activity log. Expected: milestone changes and any saved field edits appear as rows (when, event, actor, summary); click an entry to inspect old/new values.
- [ ] Attach or remove a container attachment, then Save. Expected: an activity row records the attachment change (old vs new media ids).
- [ ] Press Save without changing anything. Expected: no new activity row appears.

**Customer portal**

- [ ] Log in to the portal as a customer user and open a tracked container. Expected: the details grid shows the **Tracking position** text when it is filled in; there is no location-history table.

## 10. Users & Companies CRUD (2026-09-15)

**Prerequisites**

- Run `php artisan migrate:fresh --seed` first (schema was rebuilt).
- Admin panel: `http://localhost:8000/admin` — log in as `superadmin@example.com` / `password` (or `admin@example.com` / `password`).
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

**Manage portal users inside a company**

- [ ] Companies → New company → in the "Portal users" section, type a name that doesn't exist → click "Create …" in the dropdown → fill name + email (+ optional phone) → Create, then save the company. Expected: company created; the new user is attached AND listed in CRM → Users carrying the `customer` role badge.
- [ ] Same select on Edit: search and pick an existing user → Save. Expected: user is linked (CRM → Users shows them under "Companies" count, or reopen the company edit form).
- [ ] Portal check: at `/login`, request a code for the newly created user's email. Expected: code is issued (visible on screen in dev); after entering it the user lands on `/portal`.
- [ ] Edit the company → click the × on that user in the select → Save. Expected: link removed; the user still exists under CRM → Users.
- [ ] Set the user Active = off, then request an OTP again. Expected: "No active customer account matches that email address."
