# Completed Tracker

Update this file after every meaningful implementation change.

## Completed

### Portal profile — P2.6 (2026-10-13)
- `GET /api/v1/student/profile` (`ProfileResource`, whitelist test: no health/financial/notes/family fields);
  CPR re-padded to 9 digits (integer column drops the leading zero of 2000s CPRs)
- Profile page: identity header (display-only photo + note), personal / contact / academic cards, CPR masked with
  reveal, password notice, appearance, legacy link, logout. Notification card arrives with push (P3.4)

### Portal attendance + stats — P2.5 (2026-10-13)
- `App\Services\Attendance\ClassroomAttendance`: recorded states + the cell rule (slot hour → any record of the
  subject/day → other classroom of the course → any subject of the course that day), moved out of
  `reportStudentAttendList` **and** its Blade view; the admin report now calls the service
- Verified: the `student_attend_list` HTML of 75 real classrooms is byte-identical before/after; a blunt mutation
  changes 6 of them (harness works); moved-student paths are covered by 8 service tests (dev data has none)
- `StudentSessions`: a student's sessions up to now from the timetable + the service (`attend` → present …,
  none → unrecorded; today's unrecorded session listed only once it has started) and §7.4 stats
- API: `GET /api/v1/student/attendance?course_id=&from=&to=`, `GET /api/v1/student/attendance/stats?course_id=`;
  archive cards gain `attendance_rate`; `Student::enrolledClassroomFor()`
- `config('portal.timezone')` = Asia/Bahrain for "today" (the app runs in UTC); a test fails if UTC is used
- Real data: 475/475 → 200 on all three endpoints, 11,997 sessions, stats = sessions for all 387 current courses;
  attendance p50 144 ms / p95 156 ms (Docker on Windows), archive list ≤ 256 ms, ≤ 18 queries
- SPA: `entities/attendance` (status catalog, summarize, chips/pips, SessionRow, queries), `features/filter-attendance`
  (course select, subject + status chip rails, `useSelectedCourse` from `?course=`), widgets `attendance-calendar`
  (month grid; side panel on lg, bottom sheet on phones; `?date=` opens a day), `attendance-log` (by week),
  `attendance-summary` (hero, KPIs, composition bar + table); Attendance + Stats pages; Home attendance tiles;
  archive card rate. Weekend dimming dropped (the centre teaches Fri/Sat). Placeholder ComingSoonPage removed
- Tests: 18 new PHPUnit (service + API), 11 new Vitest

### Portal archive — P2.4 (2026-10-13)
- API: `GET /api/v1/student/courses?status=archived` (`CourseSummaryResource`: identity, dates, result once published)
  and `GET /api/v1/student/courses/{course}` (`CourseDetailResource`, status `active` for the current course, else `archived`)
- `Student::enrolledClassrooms()` (drops soft-deleted courses, which the `classRooms()` join kept) and
  `archivedClassrooms()` (all but the current course, one per course, newest first); the certificate endpoint uses it too
- Verified read-only on dev data: 475/475 lists → 200, 1,056 archive cards, all details → 200 and archived,
  none overlapping the current course, all newest-first
- SPA: `/archive` and `/archive/:courseId`; search + year filter; `widgets/archived-course-card` (disclosure shell);
  detail fetched only when a card opens; tabs marks → timetable → payments; link to the course's attendance log
- `CourseDetailsTabs` is now controlled (hash on Home, local state on archive cards) with `useId` ids
- Shell: exact nav matching only for Home and Attendance, so the archive tab stays active on `/archive/:id`
- Tests: 8 new PHPUnit (`StudentArchiveTest`, fixtures in `Concerns/BuildsEnrollments`), 5 new Vitest;
  Testing Library async timeout 4 s for lazy routes under parallel load

### Portal current course — P2.3 (2026-10-13)
- API: `GET /api/v1/student/courses/current` (`CourseDetailResource`, `data: null` without an active course) and
  `GET /api/v1/student/courses/{course}/certificate` (admin certificate print view; 403 until `final_grade`, 404 if not enrolled)
- `Student::currentClassroom()`: active course, latest `start_date`, then classroom id
- Resource: curricula from `teachers` + `attend_table` JSON (ISO day 1–7 → 0 = Sun … 6 = Sat; missing end = +1 h,
  empty start = null), the student's own payments for the course with Arabic type/source labels
  (`portal.payment_types|payment_sources`), `brief_marks` rows (`n/a` → null), total/grade only once published
- Rate limits: `/api/v1` 600/min per IP + 120/min per user (`throttle:portal`); the old 60/min per IP would have
  throttled a whole classroom on one Wi-Fi
- Verified read-only on dev data: 475/475 students → 200, 387 current courses (= SQL count), 329 real marks records
  mapped with no errors, all totalling 100
- SPA: `entities/course` (query, `nextSession()`, Timetable, CourseMeta), `entities/payment` (PaymentList, ReceiptView),
  `entities/mark` (MarksList); `features/print-receipt`, `features/download-certificate`; widgets `course-hero`,
  `course-details-tabs` (#payments / #marks deep links); Home with loading / no-course / error states, 2 KPI tiles
- Tests: 12 new PHPUnit (course contract, scoping, certificate, rate limits); 13 new Vitest (next session + Home)

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
