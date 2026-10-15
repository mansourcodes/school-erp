# Architecture Context

## Stack

| Layer       | Technology                                    | Role                                             |
| ----------- | --------------------------------------------- | ------------------------------------------------ |
| Framework   | Laravel 8 + PHP 7.3/8.0                       | Core application framework                       |
| Admin UI    | Backpack CRUD 4.1 + Bootstrap 4 (RTL)         | Admin panel with CRUD scaffolding                |
| Student UI  | Bootstrap 5 RTL + Blade                       | Student-facing portal (current, stays during beta) |
| Portal SPA  | React 18 + RTK Query + Tailwind 4 / DaisyUI 5 (Vite) | New student/teacher portal at `/beta`, Feature-Sliced Design |
| Auth        | Laravel built-in (Users) + custom Student guard | Dual guard: `web` for admin, `student` for portal |
| Portal API auth | Laravel Sanctum 2.x (SPA cookie mode)     | `/api/v1` session auth for the new `/beta` portal; no tokens |
| Database    | MySQL via Eloquent + migrations               | All relational data                              |
| PDF         | carlos-meneses/laravel-mpdf                   | Report and certificate generation                |
| Roles/Perms | Spatie Permission (via Backpack PermMgr)      | Role-based access control                        |
| File storage| Laravel `public` disk                         | Exam tool uploads and ZIP archives               |
| Queue       | Laravel Jobs (database driver)                | Async ZIP packaging for exam tools               |
| Deployment  | Docker + docker-compose                       | Container-based hosting                          |
| Fonts       | Nunito (web font), Arabic system fonts        | Typography                                       |

## System Boundaries

- `app/Http/Controllers/Admin/` — Backpack CRUD controllers for all admin entities
- `app/Http/Controllers/Report/` — Report rendering controllers (PDF/print views)
- `app/Http/Controllers/StudentPanel/` — Student portal auth + dashboard
- `app/Http/Controllers/Api/` — API endpoints (classrooms, courses, curriculum, students)
- `app/Http/Controllers/Api/V1/` — new portal JSON API (`/api/v1`, PLAN.md §7): thin controllers →
  Form Requests (`app/Http/Requests/Api/V1/`) → services (`app/Services/Portal/`) → API Resources
  (`app/Http/Resources/V1/`, whitelisted fields only)
- `frontend/` — portal SPA source (FSD layers app → pages → widgets → features → entities → shared,
  imports flow downward only; enforced by ESLint + Steiger). Talks to Laravel only via `/api/v1` + `/sanctum/csrf-cookie`.
  Build output `public/beta/` is a gitignored artifact
- `app/Models/` — Eloquent models; `Account/` sub-namespace for Payment
- `resources/views/` — All Blade templates; `vendor/backpack/` for customised Backpack views
- `resources/views/reports/` — Print/PDF templates (student, course, exam, payment, statistic, account)
- `resources/lang/` — Translations: `ar/` (primary) and `en/`
- `database/migrations/` — Schema history; do not edit old migrations

## Storage Model

- **MySQL**: All entity data — students, courses, classrooms, marks, payments,
  attendance, curricula, users, roles/permissions
- **JSON columns in MySQL**: `ClassRoom.teachers` (array of teacher+curriculum
  assignments), `ClassRoom.attend_table` (array of day/time/curriculum
  schedule slots), `Curriculum.marks_labels` (mark template definitions),
  `StudentMarks.marks` (student mark entries — custom cast `MarksDetailsCast`)
- **Local disk (`public`)**: Uploaded exam files (`examtools/exams/`) and
  generated ZIP archives; portal photos `students/photos/` (`students.photo_path`) and `teachers/photos/`
  (`users.photo_path`), always re-encoded by `App\Services\Portal\ProfilePhotos`
- **Session**: Laravel session for both `web` and `student` guards; the `/api/v1` portal API uses the same
  session cookie through Sanctum's stateful middleware (old and new portal share the login)
- **Time**: the app runs in UTC (`config/app.php`); the portal's "today" uses `config('portal.timezone')`
  (Asia/Bahrain)
- **Cache**: login throttles and the per-CPR lockout counters (`portal-login:*` keys)
- **Logs**: `storage/logs/security-*.log` — failed/locked portal logins (CPR as HMAC, IP, UA)

## Auth and Access Model

- **Admin users** authenticate via Backpack's login (`/admin/login`) with their **username**
  (`backpack.base.authentication_column`); uses the `web` guard and `User` model with Spatie roles.
  `users.username` is unique, stored lowercase, `a-z 0-9 . _ -`, 3–50 (`User::usernameRules()` is the one rule set
  for the admin CRUD and the teacher portal); `users.email` is optional contact information (nullable, unique
  when set; Backpack's password reset only works for users with one)
- **Students** authenticate via the custom `student` guard at `/login`; uses
  the `Student` model (extends `Authenticatable`); guarded separately — no
  overlap with admin session
- **Teacher role**: Teachers log in as `User` (admin guard); Backpack middleware
  restricts ClassRoom queries to their own assigned classrooms when
  `auth()->user()->hasRole('Teacher')` is true
- **SuperAdmin role**: Full access to all CRUD and reports
- **Open portal as teacher** (`App\Services\Portal\TeacherImpersonation`): a Superadmin clicks "Open portal" on a
  Teacher in Backpack → Users (`POST /admin/user/{id}/portal`). The portal session is the teacher's own (`web`
  guard, the teacher's user id, **no remember cookie**), so `TeacherScope` applies unchanged; the session stores
  `portal_impersonator_id` / `portal_impersonated_at`. `portal.impersonation` (on the `/api/v1` auth group) ends it
  with a 401 after `portal.impersonation.ttl_minutes` (60) or when the admin loses Superadmin, and logs every
  write to the security log; `portal.impersonation:deny` blocks the teacher's password / username / own photo.
  Portal logout during an impersonation signs out the `web` guard only (no `session()->invalidate()`), so the
  admin stays in Backpack. `/me` returns `impersonated_by: {name}` and the SPA shows a banner with "End"
- **Portal API (`/api/v1`)**: `auth:sanctum` over the `student` guard (teachers join with `web` in Phase 3).
  Students sign in with CPR, teachers with their username (same as the admin panel).
  Login protection: per-(CPR+IP) and per-IP rate limits, per-CPR lockout, Turnstile, honeypot, generic errors.
  Other v1 routes: 600/min per IP (shared classroom Wi-Fi) + 120/min per signed-in user

## Key Relationships

```
AcademicPath ──< Course ──< ClassRoom >──< Student
                                │
CurriculumCategory ──< Curriculum (stored in ClassRoom.teachers JSON
                                    and ClassRoom.attend_table JSON)
                                │
                         StudentMarks (per student per course)
                                │
                         Payment (per student per course)
                                │
                         Attends (per session date/time/classroom/curriculum)
ExamTool >── Course, Curriculum
```

## Invariants

1. `ClassRoom.teachers` and `ClassRoom.attend_table` are JSON columns — never
   store curriculum assignments or timetable as separate relational tables
2. `StudentMarks` has a unique constraint on `(student_id, course_id)` — one
   marks record per student per course
3. `Attends` code is composite: `date#time#classroomCode#curriculumId` —
   uniqueness is enforced via this code, not a simple integer PK
4. The `student` auth guard is completely separate from the `web` (admin)
   guard — middleware must specify the correct guard explicitly
5a. Attendance states are resolved only by `App\Services\Attendance\ClassroomAttendance` (admin
   `student_attend_list` report and portal API alike); do not re-implement the cell rule elsewhere
5. PDF generation runs synchronously in request handlers (no background jobs
   for reports); exam ZIP packaging and the portal's Web Push notifications are queued
   (push listeners never fail the request that triggered them). Production runs
   `QUEUE_CONNECTION=sync` with no worker, so teacher saves and messages only record a push
   outbox (`push_deliveries`) that the teacher's app sends one request at a time
   (`App\Services\Portal\PushOutbox`); admin saves send inside the request
6. Do not store large binary content in the database — files go to `public`
   disk storage
