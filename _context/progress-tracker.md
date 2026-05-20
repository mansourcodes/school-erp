# Progress Tracker

Update this file after every meaningful implementation change.

## In Progress

- None.

## Completed Recently

- Feature 02: Teacher role + attendance access (2026-05-20)
  - `ClassRoom.teachers` JSON now includes `user_id` per entry
  - `ClassRoom::getByDate()` filters by JSON `user_id` instead of pivot table
  - `ClassRoomCrudController` teachers repeatable adds `user_id` dropdown (Teacher users only)
  - `RedirectTeacherToAttendance` middleware added to Backpack `middleware_class`
  - Teachers auto-redirected to `/admin/add_attend_by_date` from any other admin page
  - Sidebar dashboard link hidden for Teacher role
  - `api/teacher` endpoint added

## Next Up

- TBD — awaiting direction from user

## Open Questions

- None currently logged.

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
