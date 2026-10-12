# Completed Tracker

Update this file after every meaningful implementation change.

## Completed

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
