# Progress Tracker

Update this file after every meaningful implementation change.

## In Progress

- Student portal redesign — Phase 1 (design) delivered, awaiting review (2026-10-12)
  - Plan + mockups in `_brand/new_design/` (`PLAN.md`, `index.html` gallery)
  - HTML + Tailwind 4 + DaisyUI 5 (custom themes `ghadeer` / `ghadeer-night`), RTL, mobile-first
  - Screens: login (bot/lockout states, forgot-password → visit administration), current course,
    archive, attendance log, attendance stats, profile (photo upload), system states
  - Rollout decision (2026-10-12): new panel launches as a **beta at `/beta`**; the current Blade
    portal (`/login`, `/dashboard`) stays active and unchanged until promotion (PLAN.md §4, §9)
  - Phase 2 = React + Redux Toolkit + RTK Query SPA over a Sanctum-authenticated `/api/v1`;
    supersedes the Blade/Alpine plan in `_context/feature/01-frontend-student-react.md`
  - Mobile-first pass (≈90% of visits are phones): 360–430px is the primary layout; chip rails,
    bottom sheets, day-chip timetable, list-style marks, camera capture for photo, safe-area insets,
    ≥44px targets; IBM Plex Sans Arabic only; stats reduced to hero + KPIs + status breakdown

## Completed Recently

- `student_attend_list` report shows recorded attendance (2026-10-12)
  - `AcademiaReportsController::reportStudentAttendList` builds `attend_states[curriculum_id][date][hour][student_id]`
    from `Attends` of the classroom (eager-loaded 5 state relations)
  - Matched by hour only (tolerates legacy/unrepaired times), with a curriculum+date fallback
    for timetable slots that have an empty `start_time` (form then records the time it was opened)
  - Student status comes only from the teacher's toggles in `attend_easy_form`; `start_time` just
    identifies the session
  - Classroom membership changes: new student → empty row; removed student → not rendered (old
    pivot rows ignored, no FKs on `class_room_student` / `attends_student_*`); moved student → cells
    filled from records of other classrooms in the same course on that date (same curriculum
    preferred, else any), marked `*`. A record in this classroom always wins (manual correction).
  - Guarded null `attend_table` / `teachers` in `reportStudentAttendList`
  - View renders ✓ / ت / تع / ✗ / غع per cell plus a legend

- Fixed attend `start_time` minutes bug (2026-10-12)
  - `'H:m'` (m = month) → `'H:i'` in attend forms, `Attends::code`, `ClassRoom::getByDate`, report header
  - Migration `2026_10_12_100000_repair_attends_start_time_minutes` restores real times from the
    classroom `attend_table` slot (same curriculum + week day + hour); logs repaired/skipped ids.
    `down()` is a no-op. Run on production with a DB backup first.

- Student `iqraa_id_account` field (2026-10-12)
  - Migration `2026_10_12_000000_add_iqraa_id_account_to_students_table` (nullable string, after `email`)
  - Added to `Student::$fillable`, `StudentRequest` (`nullable|string|max:255`), and Student CRUD create/update form
  - `students_names_sheet` report (`statisticReports?view=studentsNamesSheet`) now shows Iqraa account + email columns

- Feature 02: Teacher role + attendance access (2026-05-20)
  - `ClassRoom.teachers` JSON now includes `user_id` per entry
  - `ClassRoom::getByDate()` filters by JSON `user_id` instead of pivot table
  - `ClassRoomCrudController` teachers repeatable adds `user_id` dropdown (Teacher users only)
  - `RedirectTeacherToAttendance` middleware added to Backpack `middleware_class`
  - Teachers auto-redirected to `/admin/add_attend_by_date` from any other admin page
  - Sidebar dashboard link hidden for Teacher role
  - `api/teacher` endpoint added

## Next Up

- Review redesign mockups; resolve open questions in `_brand/new_design/PLAN.md` §10
- Then Phase 2.1: Sanctum + `/api/v1/auth/*` + login rate limiter/Turnstile/honeypot

## Open Questions

- Student portal redesign: see `_brand/new_design/PLAN.md` §10 (multiple active courses, attendance
  metric policy, marks "published" flag, photo moderation, admin contact details, Turnstile, SPA URL).

- Some `ClassRoom.attend_table` slots have an empty `start_time` (e.g. classroom 897, curriculum 1).
  `Carbon::parse('')` = now, so the attend form/list show and save the current time. Should start_time
  be required in the ClassRoom CRUD timetable?

## Architecture Decisions

- JSON columns for `ClassRoom.teachers` and `ClassRoom.attend_table` chosen
  to avoid a complex pivot-within-pivot schema for curriculum+teacher+timeslot
  assignments. Accepted trade-off: no FK enforcement on curriculum_id inside
  JSON.
- Single `StudentMarks` record per student per course (unique constraint) with
  a rich JSON `marks` column and `MarksDetailsCast`; avoids an EAV table per
  mark type.
- Dual auth guard (`web` for admin/teachers, `student` for student portal) to
  keep the two surfaces completely isolated in session and middleware.
- PDF generation is synchronous in the request (mPDF called directly in Report
  controllers); only exam ZIP packaging is queued.

## Session Notes

- Context files were blank templates; populated from codebase on 2026-05-20.
- Stack: Laravel 8, Backpack CRUD 4.1, Bootstrap 5 RTL (student portal),
  Spatie Permission, mPDF, PHPWord, Docker.
- Primary language: Arabic (RTL). English translations also maintained.
- Recent git work: certificate_table added to student portal, student course
  order fixed, Docker compose fixed, AI context files added.
