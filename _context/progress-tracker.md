# Progress Tracker

Update this file after every meaningful implementation change.

## In Progress

- Portal redesign — Phase 2 build started (2026-10-13)
  - **P2.1 API foundation done**: Sanctum + `/api/v1/auth/login|logout` + `/api/v1/me`, login rate limits,
    per-CPR lockout, Turnstile, honeypot, security log; 13 feature tests passing (see completed-tracker)
  - **P2.2 SPA shell + auth done** (2026-10-13): `frontend/` FSD app, login, shell, theme, 401/419/429 handling,
    23 tests, CI workflow (see completed-tracker)
  - **P2.3 Current course done** (2026-10-13): course API + certificate print view, Home hero / KPIs / tabs
  - **P2.4 Archive done** (2026-10-13): archive list + read-only detail, `/archive/:courseId`
  - **P2.5 Attendance + stats done** (2026-10-13): shared `ClassroomAttendance` service (admin report unchanged,
    verified), attendance + stats endpoints and pages, Home attendance tiles, archive rates
  - **P2.6 Profile done** (2026-10-13)
  - **P2.6b PWA done** (2026-10-13); installability check in Chrome follows P2.7 (needs `/beta` served)
  - **P2.7 Beta launch wiring done** (2026-10-13): `/beta` served by Laravel, `portal.mode` + middleware, SPA
    maintenance/closed screens, old-portal banner + login limit, TrustProxies (P0.1 code part)
  - P2.8 (beta period with students) and P2.9 (promotion) are operational steps, gated by P0.1/P0.2, pen test,
    restore test and the k6 peak test (PLAN.md §11)
  - Next: **P3.1 Teacher API**
- Student portal redesign — Phase 1 (design) delivered, awaiting review (2026-10-12)
  - Plan + mockups in `_brand/new_design/` (`PLAN.md`, `index.html` gallery)
  - HTML + Tailwind 4 + DaisyUI 5 (custom themes `ghadeer` / `ghadeer-night`), RTL, mobile-first
  - Screens: login (bot/lockout states, forgot-password → visit administration), current course,
    archive, attendance log, attendance stats, profile (photo upload), system states
  - Teacher panel design added (2026-10-12): `t-home`, `t-attendance` (5-state roster, save/undo),
    `t-classes`, `t-classroom` (student photo upload/delete), `t-stats`, `t-profile` (teacher archive page removed);
    teacher tab enabled on the shared login; spec + teacher API in PLAN.md §8
  - Decisions applied (2026-10-12): `portal.mode` Backpack setting (beta/live/maintenance/closed) replaces the env flag;
    certificate available when `final_grade` is set; same-day early recording for teachers; no teacher audit log;
    messages kept 90 days; message push titled with the centre name; Cloudflare Free on a Hostinger KVM 2 VPS (lock down Webmin)
  - Quality + infrastructure (2026-10-12): PLAN.md §9 maintainable / scalable / secure criteria (OpenAPI
    contract, e2e on phone viewport, Redis for session/cache/queue, object storage for photos, ASVS L2);
    §10 move the domain to **Cloudflare** (proxy, Full strict, origin lock / Tunnel, WAF, edge rate limits,
    Access on /admin, TrustProxies + CF-Connecting-IP). New milestones P0.1 Cloudflare, P0.2 Laravel/PHP upgrade
  - Messaging + push design added (2026-10-12): student inbox `messages.html` (read-only, unread badge),
    teacher `t-messages.html` (sent, read progress, delete) + `t-compose.html`; profile notification
    settings; lock-screen push mockups in `states.html`. Frontend declared **Arabic only**.
    Pending on implementation: update `architecture.md` invariant 5 (push jobs join the queue)
  - Architecture decisions (2026-10-12): Phase 2 SPA uses **Feature-Sliced Design** (layers app/pages/
    widgets/features/entities/shared, RTK Query endpoints injected per slice, Steiger lint in CI) and ships
    as an installable **PWA** (vite-plugin-pwa; app shell precached, personal API data never SW-cached)
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

- P2.6: `/student/profile` (whitelisted fields, PLAN.md §3.6) + Profile page (keeps theme / legacy link / logout)
- Run the SPA locally: `cd frontend && npm run dev` → http://localhost:5173/beta/ (proxies `/api`, `/sanctum` to Laravel)
- Before go-live (P0.1): `TrustProxies` + `CF-Connecting-IP`, otherwise the per-IP login limits see Cloudflare's IPs

## Open Questions

- Deploy: the web server must send `manifest.webmanifest` as `application/manifest+json`, and `/beta/sw.js`,
  `/beta/index.html`, `/beta/manifest.webmanifest` with no-cache (Cloudflare cache rules, PLAN.md §10.4).
  Build step: `cd frontend && npm ci && npm run build` (writes `public/beta`, gitignored).

- Certificate: served as the admin HTML print view (no mPDF output exists in the project). A real PDF download
  would be its own task. OK for the beta?
- Payment labels (new Arabic copy, please confirm): FULL = رسوم كاملة, PART = دفعة جزئية, OFFER = عرض خاص,
  CASH = نقدًا, BENEFIT_PAY = بنفت باي (`resources/lang/ar/portal.php`)
- **Admin attend list skips a session on the course start date**: `HtmlHelper::getDaysInRange` uses
  `modify('next <day>')`, so a course that starts on a teaching day loses that first session in the report, and
  (to stay identical) in the portal. Fix both together? (One-line change in `getDaysInRange`; pinned by a test.)
- `config/app.php` timezone is UTC; the portal uses `portal.timezone` (Asia/Bahrain). Should the whole app move to
  Asia/Bahrain? (Affects stored timestamps; separate decision.)

- Frontend dependency majors: plan says React 18, kept. Pinned to Vite 7, Vitest 5, TS 5.9, ESLint 9, MSW 2,
  React Router 7 (newer majors exist: React Router 8, TS 7, ESLint 10, MSW 3). Upgrade as a separate task?
- `npm audit` (dev only): `braces` via Steiger, no fix published yet; CI audits production deps only.
- Messages envelope + unread badge in the top bar is deferred to P3.3 (no messages API yet).

- Portal session idle timeout: PLAN.md §6 suggests ~60 min, but `SESSION_LIFETIME` (120) is shared with the
  admin panel. Lower it globally, or add a portal-only idle check?
- `tests/Feature/ExampleTest` (stock Laravel, expects `/` → 200) fails because `/` requires student login.
  Pre-existing; replace or delete it?
- Existing unauthenticated `/api/{course,classroom,student,teacher}` routes expose data publicly
  (code-standards: "Authenticate API requests"). Untouched by P2.1; needs its own fix.
- Composer: the `anasbakro/dompdf` VCS repository in composer.json isn't used by any installed package but
  breaks `composer update` without GitHub credentials. Remove it?

- Portal redesign: all 22 plan questions **answered 2026-10-12**, see `_brand/new_design/PLAN.md` §12 Decisions log.
  Three answers change earlier requirements and should be confirmed with the centre before Phase 3:
  Q4 students cannot upload their own photo; Q13 teachers use the new panel only (Backpack teacher redirect retired);
  Q14 teachers don't see archived courses.

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
