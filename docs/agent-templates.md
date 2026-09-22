# Agent Templates

Reference templates for `AGENTS.md`. Open this file only when you need the matching format.

## Plan Proposal Format (AGENTS.md §4)

> **Goal:** [what we want to achieve]
> **Helicopter view:** [where we are now and how this fits in the big picture, 1-2 sentences]
> **Plan:**
>
> 1. [Step 1 - e.g. Create types and interfaces]
> 2. [Step 2 - e.g. Implement service logic]
> 3. [Step 3 - e.g. Add UI and connect]
>    **Next:** I will start with Step 1 only. Is this okay?

## Progress Log Format (AGENTS.md §7B)

```markdown
# Progress - [Feature Name]

Goal: [One sentence goal]
Started: 2026-05-13 14:00

## Plan Checklist

- [x] Step 1: Create types - DONE - 14:05
- [ ] Step 2: Implement service - IN_PROGRESS - Started 14:10
- [ ] Step 3: Add UI - TODO
- [ ] Step 4: Update logs - TODO

## Current Focus

Working on: src/core/auth/auth.service.ts
Next: src/features/auth/LoginForm.tsx
Blocked: None

## Notes

- Decided to use jose instead of jsonwebtoken because...
```

## Package Proposal Format (AGENTS.md §8.3)

NEVER install without asking. Propose like this:

> **Need:** We need to handle date formatting.
> **Search Result:** I found `date-fns` (35k stars, weekly 10M downloads, maintained 2 weeks ago) and `dayjs` (lighter).
> **Comparison:**
>
> - date-fns: Pro: Modular, tree-shakeable, TS native. Con: Slightly larger API surface.
> - dayjs: Pro: Tiny, Moment.js compatible. Con: Plugin system can be confusing.
>   **My Suggestion:** I suggest `date-fns` because we need only 2 functions and it is more robust and easy to remove later. Install size ~20kb.
>   **Can I install it?**

Wait for user yes.

## File Header Format (AGENTS.md §9.1)

```ts
/**
 * File: src/core/auth/auth.service.ts
 * Responsibility: Handles user login, logout, and JWT token verification.
 * What it does:
 * - Provides login() and logout() functions used by the UI.
 * - Verifies tokens via the `jose` library.
 * How to use:
 * - Call `authService.login(email, password)` from LoginForm.
 * How to extend:
 * - To add OAuth, add a new method `loginWithGoogle()` here without changing existing logic.
 */
```

## Example Workflow (AGENTS.md §11)

User: "Add authentication"

Bad Agent: Creates 8 files, installs 3 libraries, writes 500 lines, says "Auth done!"

Good Agent (You):

> **Goal:** Add authentication
> **Helicopter view:** We have a basic app structure, but no user system yet. Auth is foundational, so we will do it first.
> **Plan:**
>
> 1. Define auth types and interfaces
> 2. Implement auth service
> 3. Add login UI
>    **Next:** I will start with Step 1 only. Is this okay?

[User says yes]

[Agent creates `auth.types.ts`, updates log]

> **Done Step 1:** Created auth types.
>
> - Defined User and IAuthService
>   **Log updated.** Ready for Step 2: Implement service logic. Should I proceed? I suggest using JWT because it is simple to start with.

This is how you must work.
