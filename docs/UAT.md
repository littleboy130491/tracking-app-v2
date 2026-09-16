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

## 1. Portal journey timeline like the reference tracker (2026-09-16)

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

## 2. B/L Progress milestones & audit (2026-09-16)

**Prerequisites**

- Run `php artisan migrate:fresh --seed` first (schema was rebuilt).
- Admin panel: `http://localhost:8000/admin` — log in as `admin@example.com` / `password`.

**Progress layout**

- [ ] Bill of ladings → New: the form shows only the **Document** tab; **Progress** and **Activity log** are absent.
- [ ] Create a shipment, then open its Edit → **Progress**. Expected: fields are laid out continuously; no card boxes or collapse controls.
- [ ] The horizontal milestone stepper appears at the top; completed steps are green, the current step is highlighted, later steps are grey.
- [ ] A field belonging to a not-yet-reached step is disabled and shows `Locked until Step X: {step name}` underneath; fields at or before the current step are editable and show no milestone text.
- [ ] Click a future step more than one ahead. Expected: it is not clickable. Click the next step and confirm the Filament modal. Expected: progress advances one step and the newly unlocked fields become editable.
- [ ] Click an earlier step and confirm. Expected: progress moves back and its fields remain editable.

**Containers**

- [ ] At the pickup step (Step 2), add a container. Expected: the item stays open; fields show as a flat list, each disabled field naming the step that unlocks it.
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

## 2. Users & Companies CRUD (2026-09-15)

**Prerequisites**

- Run `php artisan migrate:fresh --seed` first (schema was rebuilt).
- Admin panel: `http://localhost:8000/admin` — log in as `superadmin@example.com` / `password` (or `admin@example.com` / `password`).
- Customer portal: `http://localhost:8000/login` — OTP codes appear directly on the verify screen when `OTPZ_EXPOSE_IN_DEV=true` is set in `.env`.

**Users**

- [ ] CRM → Users → New user: fill name, email, optional phone, password. Expected: user appears in the list with the `customer` role assigned by default.
- [ ] Edit that user: change phone, toggle Active off → Save. Expected: changes persist; phone is visible via the column picker (hidden by default).
- [ ] Create a user with an email that already exists. Expected: validation error, no duplicate created.

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
