# Code Standards

## General

- Keep controllers focused on a single responsibility — admin CRUD, reports,
  and the student portal are three separate controller namespaces
- Fix root causes; do not add workarounds or commented-out code blocks
- Soft-delete entities (Student, Course, ClassRoom, Curriculum, AcademicPath,
  Payment) — never hard-delete unless explicitly required
- All user-visible strings must go through the translation helper `__()`; no
  hardcoded English or Arabic in blade files

## PHP / Laravel

- Laravel 8 conventions: no PHP 8.1+ syntax (match expressions, enums, fibers)
- Use Eloquent relationships instead of raw DB queries wherever possible
- Validate request input before any logic in form requests or controller methods
- Do not use `$request->all()` — use `$request->validated()` or explicit
  `$request->only([...])`
- Avoid `dd()` / `dump()` left in committed code

## Backpack CRUD (Admin)

- All admin entity controllers extend `CrudController` and live in
  `app/Http/Controllers/Admin/`
- Models used in CRUD panels must use the `CrudTrait`
- Custom buttons go in `resources/views/vendor/backpack/crud/buttons/`
- Do not modify files inside `vendor/` — publish and override instead
- Use `setupListOperation()`, `setupCreateOperation()`,
  `setupUpdateOperation()` etc. as separate setup methods

## Models

- Declare `$fillable` on every model — never use `$guarded = []` except Payment
  where it's already established
- Use `$casts` for dates, booleans, JSON columns, and custom cast classes
  (`MarksDetailsCast`, `CurriculumMarksDetails`)
- JSON columns (`teachers`, `attend_table`, `marks`, `marks_labels`) are cast
  to arrays/objects — access via Eloquent accessors, never raw JSON strings
- Computed/display attributes go in `$appends` with corresponding
  `get{Name}Attribute()` methods
- Keep model methods that return HTML (buttons, dropdowns) as model methods
  only when Backpack calls them — do not add HTML to models for other contexts

## Views / Blade

- Student portal views live in `resources/views/student/`
- Report/print templates live in `resources/views/reports/`
- Use `@include` for shared partials; do not duplicate table templates
- Avoid inline `<style>` beyond what already exists in `layouts/app.blade.php`
- Icons: use inline Heroicon SVG in the student portal; Font Awesome classes
  in the admin panel

## Styling

- Student portal: Bootstrap 5 RTL utility classes
- Admin panel: Backstrap/Bootstrap 4 utility classes
- No new hardcoded hex colours — use Bootstrap semantic classes or extend the
  existing `_variables.scss` tokens
- RTL must be preserved: use `ms-auto` / `me-auto` (not `ml-`/`mr-`),
  `text-end` (not `text-right`)

## API Routes

- API controllers live in `app/Http/Controllers/Api/`
- Return JSON responses with consistent shape
- Authenticate API requests — do not expose student/admin data publicly

## Translations

- Add keys to both `ar/` and `en/` language files simultaneously
- Group by domain: `localize.php` for general UI, `reports.php` for report
  labels, `account.php` for payment/accounting, `examtool.php`, `studentmark.php`

## File Organisation

- `app/Models/` — Eloquent models; `Account/` sub-namespace for financial models
- `app/Models/Old/` — legacy migration models (do not use for new features)
- `app/Http/Controllers/Admin/` — Backpack CRUD controllers
- `app/Http/Controllers/Report/` — PDF/print report controllers
- `app/Http/Controllers/StudentPanel/` — Student portal auth + views
- `app/Http/Controllers/Api/` — JSON API endpoints
- `app/Helpers/` — Helper functions (`helpers.php`, `FormBuilderHelper.php`)
- `app/Casts/` — Custom Eloquent cast classes
- `resources/views/reports/` — Print-ready Blade templates
- `resources/views/vendor/backpack/` — Backpack view overrides
- `database/migrations/` — Never edit already-run migrations
