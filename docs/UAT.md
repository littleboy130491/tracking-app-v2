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

## 1. Users & Companies CRUD (2026-09-15)

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
