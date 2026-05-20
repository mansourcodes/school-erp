# AI Workflow Rules

## Approach

Build this project incrementally using a spec-driven workflow. Context files
define what to build, how to build it, and the current state of progress.
Always implement against these specs — do not infer or invent behaviour from
scratch. Read all context files at the start of every session before writing
any code.

## Scoping Rules

- Work on one feature unit at a time
- Prefer small, verifiable increments over large speculative changes
- Do not combine unrelated system boundaries in a single implementation step
- Admin panel work and student portal work are separate units — do not mix them

## When to Split Work

Split an implementation step if it combines:

- Admin CRUD changes and student portal changes
- Report view changes and model/migration changes
- Multiple unrelated API routes
- Behaviour not clearly defined in the context files

If a change cannot be verified end to end quickly, the scope is too broad —
split it.

## Handling Missing Requirements

- Do not invent product behaviour not defined in the context files
- If a requirement is ambiguous, resolve it in the relevant context file before
  implementing
- If a requirement is missing, add it as an open question in
  `progress-tracker.md` before continuing

## Protected Files

Do not modify the following unless explicitly instructed:

- `vendor/` — Composer-managed packages
- `resources/views/vendor/backpack/` views that are Backpack defaults (only
  edit files that have been intentionally published/customised)
- `app/Models/Old/` — legacy models used only for data migration scripts

## Keeping Docs in Sync

Update the relevant context file whenever implementation changes:

- System architecture or boundaries → `architecture.md`
- Storage model decisions → `architecture.md`
- Code conventions or standards → `code-standards.md`
- Feature scope → `project-overview.md`
- UI patterns → `ui-context.md`

Always update `progress-tracker.md` and `completed-tracker.md` after each
meaningful implementation change.

## Before Moving to the Next Unit

1. The current unit works end to end within its defined scope
2. No invariant defined in `architecture.md` was violated
3. `completed-tracker.md` reflects the completed work
4. Translations added to both `ar/` and `en/` language files
5. `php artisan serve` (or Docker) starts without errors

## Testing

- The project has PHPUnit configured (`phpunit.xml`)
- Run `php artisan test` to verify no regressions
- For visual/UI changes, manually verify in browser (student portal at `/`,
  admin panel at `/admin`)
