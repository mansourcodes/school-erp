# Completed Tracker

Update this file after every meaningful implementation change.

## Completed

### Portal SPA shell + auth — P2.2 (2026-10-13)
- `frontend/`: Vite 7 + React 18 + TypeScript (strict) + Tailwind 4 + DaisyUI 5 + Redux Toolkit / RTK Query
  + React Router 7 (data router, basename `/beta`) + React Hook Form + `zod/mini`; builds to `public/beta` (gitignored)
- FSD skeleton (PLAN.md §4): `app` (store, providers, routes, styles) · `pages` (login, student/home, student/profile,
  system) · `widgets/app-shell` · `features` (auth/login, auth/logout, theme-switch, password-help) ·
  `entities/viewer` · `shared` (api, config, i18n, lib, ui)
- Boundaries: ESLint `no-restricted-imports` per layer (upward imports fail) + Steiger (slice isolation, public APIs);
  `insignificant-slice` off while slices have one consumer; plan segment names kept in `app`
- `shared/api`: one `baseApi` (cookies, `X-XSRF-TOKEN` from the cookie), session slice; 401/419 after sign-in →
  cache reset + `/login?expired=1`; 429 → `Retry-After` into the error; `toApiError()` normalises the v1 envelope
- Login: CPR digits-only (9), password reveal, Caps Lock hint, honeypot, Turnstile widget (when `VITE_TURNSTILE_SITE_KEY`),
  generic failure + attempts left, lockout countdown, 419 retry, offline message, password-help dialog (page-composed)
- Shell: top bar (beta badge, title from route `handle`), dock on phones, sidebar on `lg`, offline banner,
  link back to the old portal, logout (sidebar + profile placeholder), theme system/light/dark (`gh-theme`, pre-paint `theme-boot.js`)
- Tokens + motifs ported to `app/styles/app.css` (`@plugin "daisyui/theme"`); fonts self-hosted via Fontsource
- Code-split routes; first-load JS 114 KB gz signed in (+23.5 KB login chunk), budget 170 KB
- 23 Vitest tests (MSW integration over the real route tree + unit); CI `.github/workflows/frontend.yml`
- Verified in Chrome at 400px against the Laravel container through the Vite proxy (redirect, failure state)

### Portal API foundation — P2.1 (2026-10-13)
- `laravel/sanctum` 2.15 (SPA cookie mode, `sanctum.guard = ['student']`, token migrations ignored)
- `/api/v1` group (`routes/api.php`): `ForceJsonResponse` + `EnsureFrontendRequestsAreStateful`;
  `POST auth/login` (`throttle:student-login`), `POST auth/logout` + `GET me` (`auth:sanctum`)
- `Api\V1\AuthController` / `MeController` → `LoginRequest` (role `student`, CPR `digits:9`, password ≤ 128)
  → `Services\Portal\StudentLogin` (honeypot → per-CPR lockout → Turnstile → credentials) → `V1\MeResource`
- Rate limits: 5/min per (CPR + IP), 20/min per IP; `LoginLockout`: 10 failures per CPR in 15 min → 15 min lock
- No enumeration: one generic message, dummy-hash check for unknown CPRs; `attempts_left` in the 422 body
- Turnstile verifier (`services.turnstile.*`, disabled when no secret, fails closed); honeypot `website`
- Security log channel `security` (daily, 90 days): failed / locked / honeypot / bot-check, CPR as HMAC
- Arabic-only `resources/lang/ar/portal.php`; `Handler` renders v1 401/403/404/419/429 in Arabic
- 13 PHPUnit feature tests (`tests/Feature/Api/V1/StudentLoginTest.php`); phpunit uses a separate `testing` DB

### Student Portal Redesign — Phase 1 Design (2026-10-12)
- `_brand/new_design/`: PLAN.md + 8 HTML mockups (index, login, home, archive, attendance,
  stats, profile, states) sharing `assets/theme.css` (DaisyUI 5 custom themes) and `assets/app.js`
  (shell, theme toggle, demo data shaped like the planned `/api/v1` responses)
- Cropped brand logos `assets/img/logo-full.svg`, `assets/img/logo-icon.svg`

### Foundation
- Laravel 8 project scaffold with Backpack CRUD 4.1
- RTL admin panel configuration (`html_direction = rtl`)
- Dual auth guard setup: `web` (admin/teacher) + `student` (student portal)
- Spatie Permission integration via Backpack PermissionManager
- Docker + docker-compose deployment setup

### Academic Structure
- AcademicPath CRUD (academic_path_name, academic_path_type)
- CurriculumCategory CRUD
- Curriculum CRUD with marks_labels JSON template and MarksDetailsCast
- Course CRUD (year, hijri year, semester, start/end date, is_active,
  academic_path_id, course_root_id); cascades delete to ClassRooms
- ClassRoom CRUD with JSON teachers + attend_table; clone-with-students helper

### Student Management
- Student CRUD with full profile fields (CPR, DOB, mobile, address, financial
  state, hawza history, health history, financial_support_status)
- Computed attributes: student_id, age (from DOB or CPR), long_name, courses
- Soft delete; Spatie roles on Student model
- Student password field + custom `auth:student` guard login/logout

### Attendance
- Attends model with composite code (`date#time#classroomCode#curriculumId`)
- AttendsCrudController
- ClassRoom.getByDate() resolves sessions for a given day; restricts to
  teacher's own classrooms when Teacher role is active

### Marks
- StudentMarks model with MarksDetailsCast JSON marks column
- Unique constraint: one StudentMarks per (student_id, course_id)
- ClassRoomMarksController for bulk mark entry by class
- StudentMarksCrudController for individual record management
- BriefMarkHelper for composing mark display objects
- brief_marks and curriculum appended attributes

### Payments
- Payment model (course_id, student_id, amount, source, type, meta)
- PaymentCrudController
- Payment print report (mPDF)

### Exam Tools
- ExamTool model (subject, course_id, curriculum_id, file, status,
  zip_file_path, zip_file_size, meta)
- ExamToolCrudController
- File upload to `public` disk; async ZIP packaging via queued Job
- Download link on list view

### Reports
- Student reports: attend list, attend report, attend table, scoring sheet,
  transcript, certificate, edu statement, courses transcript
- Course reports
- Account / payment reports
- Statistic reports
- Exam reports
- Per-entity multilevel dropdown print buttons on list views

### Student Portal
- StudentAuthController (login/logout with `auth:student` guard)
- StudentPanelController dashboard (courses, payments, studentmarks)
- `student/dashboard.blade.php` — collapsible card sections per enrollment:
  - Timetable (student_table_template partial)
  - Payments (payment_print_template partial)
  - Certificate/marks table (certificate_table partial)
- Course list ordered by `courses.start_date` desc (current first, old after)

### API
- ClassRoomsController, CourseController, CurriculumController,
  StudentController endpoints

### Localisation
- Arabic (`ar/`) and English (`en/`) translation files for all domains
- RTL Bootstrap 5 CDN in student portal layout

### Student Iqraa account (2026-10-12)
- `students.iqraa_id_account` column (nullable string) + model fillable + request validation
- Field shown in Student CRUD create/update form (next to email)
- Students names sheet report displays Iqraa account and email per student

### Attend list report with recorded attendance (2026-10-12)
- `reports?view=student_attend_list` fills each student/date cell from the `Attends` records
  (✓ attended, ت late, تع late w/ excuse, ✗ absent, غع absent w/ excuse) with a legend per page

### Attend start_time fix (2026-10-12)
- Attend times saved/compared as `H:i` (was `H:m`, month instead of minutes)
- Data-repair migration restores existing `attends.start_time` from classroom timetables
