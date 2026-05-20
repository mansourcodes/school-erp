# Architecture Context

## Stack

| Layer       | Technology                                    | Role                                             |
| ----------- | --------------------------------------------- | ------------------------------------------------ |
| Framework   | Laravel 8 + PHP 7.3/8.0                       | Core application framework                       |
| Admin UI    | Backpack CRUD 4.1 + Bootstrap 4 (RTL)         | Admin panel with CRUD scaffolding                |
| Student UI  | Bootstrap 5 RTL + Blade                       | Student-facing portal                            |
| Auth        | Laravel built-in (Users) + custom Student guard | Dual guard: `web` for admin, `student` for portal |
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
  generated ZIP archives
- **Session**: Laravel session for both `web` and `student` guards

## Auth and Access Model

- **Admin users** authenticate via Backpack's login (`/admin/login`); uses the
  `web` guard and `User` model with Spatie roles
- **Students** authenticate via the custom `student` guard at `/login`; uses
  the `Student` model (extends `Authenticatable`); guarded separately — no
  overlap with admin session
- **Teacher role**: Teachers log in as `User` (admin guard); Backpack middleware
  restricts ClassRoom queries to their own assigned classrooms when
  `auth()->user()->hasRole('Teacher')` is true
- **SuperAdmin role**: Full access to all CRUD and reports

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
5. PDF generation runs synchronously in request handlers (no background jobs
   for reports); only exam ZIP packaging is queued
6. Do not store large binary content in the database — files go to `public`
   disk storage
