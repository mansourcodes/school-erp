# Completed Tracker

Update this file after every meaningful implementation change.

## Completed

### Admin dashboard redesign (2026-10-17, branch `feature/admin-light-sidebar`)
- `base/dashboard.blade.php` is plain Blade now (no widget arrays): greeting hero with the date, Quran verse card,
  Superadmin figures (the fake "11.456" progress widget is gone) and shortcut tiles
- Quran card: `App\Support\QuranQuotes::ALL` (32 short verses), one at random per visit (`AdminController`), and an
  "آية أخرى" button that swaps the verse in the page. Font: Amiri Quran (Google Fonts, dashboard only)
- Figures: `App\Support\AdminDashboard::stats()`: active courses, their classrooms, students enrolled in them
  (read from `class_room_student`), all students
- Styles: `.dash*` block at the end of `public/css/admin-layout.css`; accent from `--at-sidebar-active` /
  `--at-primary`, so every theme recolours it. Keys under `base.dashboard.*` (ar only, as the other shell keys)
- Two donut cards under the figures, one shared partial `inc/dashboard_donut` (inline SVG, no chart library; key
  in one row under the ring, counts on hover; colours = dataviz categorical slots 1–5 `--dash-slot-*`, validated):
  - Attendance (`AdminDashboard::attendance()`): the five status pivot tables for sessions in active-course
    classrooms; centre = attendance rate (present + late + late with excuse). Colours = the portal status palette
    (present #0ca30c, late #fab219, absent #d03b3b; excused = same hue with a white 45° hatch, SVG pattern),
    key chips carry the portal codes ✓ ت تع غع ✗, order as the portal (`states.ts`)
  - Classrooms (`AdminDashboard::classrooms()`): active classrooms with at least one attendance session vs none;
    centre = "recorded / total"; "not recorded" is neutral grey (`--dash-slot-muted`)
- Order: hero + verse, shortcuts, figures, the two donuts, teacher rankings
- Teacher rankings (`inc/dashboard_teachers`, `AdminDashboard::teachers()`): top 3 and bottom 3 Teacher-role users
  by attendance sessions in the active classrooms they teach (`class_room_teacher`). `attends` does not store who
  recorded a session, so a shared classroom counts for each of its teachers. "Least" skips teachers already in
  "most" (no repeats when there are ≤ 6). One bar scale for both cards (top teacher = full width)
- Cache: `AdminDashboard::cached()` keeps every figure (plain arrays, no models) under `admin-dashboard:figures`
  for `CACHE_DAYS` = 4 days with `computed_at`; the bottom of the page (under the teacher lists) shows "آخر تحديث …" and a
  "تحديث الإحصائيات" button (`POST admin/dashboard/refresh`, `admin.dashboard.refresh`, Superadmin only) that
  forgets the key and redirects to the dashboard with a flash message. The verse is not cached
- Teachers still never see it (`RedirectTeacherToAttendance`). `AdminDashboardTest` (8)

### Launch review fixes (2026-10-16) — `_context/feature/04-launch-review-fixes.md`
- Login throttle: the per-account+IP key uses the teacher `username` (lower-cased, trimmed), not `email`.
  `TeacherLoginThrottleTest` (3)
- `portal.session` (`PortalAuthenticateSession`) on the `/api/v1` auth group and the legacy `auth:student` group:
  stores `portal_password_hash_{guard}` in the session (also at API login); a changed password ends that session
  with 401 (redirect to `/login` for Blade). Impersonation: ends it, the Backpack session stays.
  Laravel 8's `AuthenticateSession` only checks the default guard, so it stays off. `PortalSessionTest` (7)
- API login signs the other portal guard out (`logoutCurrentDevice`, other devices keep their cookie)
- Admin select2 lookups moved from public `/api/{student,course,classroom,teacher}` to `/admin/api/*` (Backpack
  login + admin middleware; teachers are redirected); 21 `data_source` URLs → `backpack_url()`. The student lookup
  returns `id` + `long_name` only. `AdminLookupTest` (10)
- `AttendanceRecorder::save` locks the classroom row (`lockForUpdate`) before find-or-create
- Service worker: 419'd roster saves stay queued until the page confirms (`ROSTER_REPLAY_DONE`), offered to every
  window; the teacher shell asks for waiting saves on open (`ROSTER_FLUSH`); the page clears its badge only on a
  final answer. `use-roster-handoff.test.tsx` (4)
- `PushOutbox::pendingFor` is read-only; stuck `SENDING` rows are claimable after 5 min; bookkeeping moved to
  `PushOutbox::tidy()`: run by the first admin dashboard visit of the day (production has no cron, like message
  pruning; `AdminController::onceToday`), also `push:outbox-tidy` (scheduled hourly for when a cron exists). PushTest +3
- Verified false positives, regression tests only: upload fields keep their file on an untouched save (Laravel's
  `filterFiles()` drops empty uploads; SettingCrudTest +1, UserUsernameTest +2); `currentClassroom()` already orders
  by start_date then id (StudentCurrentCourseTest +1)

### Admin shell redesign: light navbar + sidebar in the core CSS (2026-10-17)
- Design reference: `_brand/new_admin_design/header-sidebar-samples.html`, sample ٢ (الرقّ الفاتح); only the
  sidebar, navbar and page background were taken from it
- New `public/css/admin-layout.css`, loaded by both layouts (`top_left`, `plain`) before `admin-themes.css`:
  page background, navbar (blends into the page, 1px line), sidebar (pill links, lined `nav-title` sections).
  Default colours on `:root` are the portal palette: parchment `#f5f3ec`, white sidebar, active pill in the default primary blue (`var(--primary)`, #3490dc, same as the buttons)
- `admin-themes.css` now holds only `--at-*` colour blocks plus the link / primary-control rules; the shell rules
  moved to the core file. New variables: `--at-sidebar-hover-text`, `--at-sidebar-active-text`,
  `--at-sidebar-border`, `--at-sidebar-line`, `--at-sidebar-title` (all 7 themes have dark sidebars → white text,
  light lines); `--at-sidebar-sub` removed. Header line is 1px for every theme (was 3px)
- `sidebar_content.blade.php`: `nav-dropdown` groups replaced by `nav-title` sections (same items, same role
  checks); hidden News block removed; stray `\"` fixed in the file-manager link
- Sidebar 275px (Backstrap 200px; `--at-sidebar-width`), sticky under the header on desktop and scrolls on its own
  (`.app-body` uses `overflow-x: clip` so sticky works; the vendor `overflow-hidden` on `.sidebar-nav` is overridden)
- Navbar 64px (`--at-header-height`), sticky: brand block over the sidebar column (logo from setting `logo`, else
  `public/beta/logo-icon.svg`; name from `portal.centre`, subtitle `base.admin_panel`), page breadcrumbs (moved from
  above the page into the navbar, `inc/breadcrumbs.blade.php` override), user name + role (`base.roles.*`) + avatar
  dropdown. No search, no notifications bell
- Active-term chips in the navbar: `App\Support\ActiveCourses::terms()` groups `courses.is_active` by semester +
  hijri year ("الفصل الأول 1447هـ", `base.active_term`), count when a term has several courses
  (`base.courses_count`, Arabic plural ranges), course names in the tooltip, links to `/admin/course`. Gold pill
  (`--at-term-bg` / `--at-term-text`; dark-red / dark-blue use white). `tests/Feature/Admin/AdminHeaderTest.php` (3 tests)
- Titles font IBM Plex Sans Arabic (`--at-title-font`): h1–h6, card headers, modal titles, brand, sidebar
  section titles; loaded from Google Fonts in `config/backpack/base.php` `styles`. Body text keeps Nunito
- Desktop: the brand block collapses and reopens with the sidebar toggle (CSS on `body:not(.sidebar-lg-show)`)
- Sidebar menu is 80% of the sidebar height (scrolls); under it a card with the source link (`handcrafted_by` +
  `developer_link`), moved from the page footer — `inc/sidebar.blade.php` override (vendor scripts kept), no
  `<footer>` in `top_left` any more. Card colours `--at-sidebar-card` / `--at-sidebar-card-link`
- Sidebar menu fills the free height with flex (`flex: 1 1 auto`, was 80%), scrollbar hidden (still scrolls);
  sidebar has no background or border in `default` / `ghadeer` (`--at-sidebar-bg: transparent`; phones use
  `--at-sidebar-overlay` because the sidebar slides over the page); dark themes keep their sidebar colour
- `nav-title` opens / closes the links under it (script in `inc/sidebar.blade.php`: `role=button`, keyboard,
  `aria-expanded`, chevron); closed sections remembered in `localStorage` (`sidebar-closed-sections`), the
  section of the current page always opens
- Theme link colour (`--at-link`) now applies to page content only (`.main` / `.modal` links, not `.btn` /
  `.dropdown-item`): before, it overrode the navbar and sidebar link colours of every non-default theme
- New theme `ghadeer` (الغدير): sample ٢ colours (parchment `#f5f3ec`, white sidebar, teal `#127a70` for the active
  pill, buttons, links, pagination). Migration `2026_10_17_000000_add_ghadeer_admin_theme_to_settings` adds the
  option and makes it the active `admin.theme`; `down()` goes back to `default`
- `AdminThemeTest` also checks `admin-layout.css` is loaded and that the migration activates `ghadeer`

### Admin panel themes from Backpack Settings (2026-10-15)
- Migration `2026_10_15_400000_add_admin_theme_to_settings_table`: setting `admin.theme` (مظهر لوحة الإدارة),
  `select_from_array` with `default`, `islamic-girly`, `brown`, `wood`, `gold`, `silver`, `dark-red`, `dark-blue`
- `App\Support\AdminTheme::current()` (unknown / missing → `default`); both Backpack layouts (`top_left`, `plain`)
  set `<html data-admin-theme="...">` and load `public/css/admin-themes.css` (cache-busted by `filemtime`)
- `admin-themes.css`: each theme sets `--at-*` variables only; one shared rule block recolours the header,
  sidebar (incl. global.scss's light sub-menu), body/footer, primary buttons, badges, pagination, focus rings and
  checks. `default` has no rules (plain Backstrap). Islamic theme has an SVG geometric sidebar pattern, wood a grain
  gradient, gold / silver metallic gradients; dark red / dark blue use dark header + sidebar (content stays light)
- `tests/Feature/Admin/AdminThemeTest.php` (3 tests)

### Admin logo from Backpack Settings (2026-10-15)
- Migration `2026_10_15_300000_add_logo_to_settings_table`: setting `logo` (الشعار), field `upload` on the public disk
- `SettingCrudController::update()`: for `upload` settings, validates (image, jpeg/png/webp, ≤ 2 MB), stores
  `settings/<key>-<uuid>.<ext>`, deletes the previous file, empty value on clear; the request is rebuilt with
  `Request::createFrom()` because Laravel caches converted uploads
- `App\Support\AdminBranding::logoUrl()`; logo above the login heading and as the navbar brand (overridden
  `resources/views/vendor/backpack/base/inc/main_header.blade.php`), project name text when unset
- Tests: SettingCrudTest +4 (upload replaces previous, clear deletes, SVG rejected, login + navbar show the logo)

### Live portal at the root, without /beta (2026-10-15)
- `PortalMode::appPath($path)`: `/beta$path` in beta / maintenance / closed, `$path` when live. Used by the
  manifest (`start_url`, `scope`, shortcuts), push `data.url` (`PortalPush`), the teacher redirect, the impersonation
  redirects and the legacy-route middleware
- `PortalAppController`: `beta()` (live → redirect to the same path + query at the root), `fallback()` (`Route::fallback`:
  shell for unmatched GET paths when live, except `SERVER_PREFIXES` admin/api/sanctum/storage/beta/pull/logout),
  `serviceWorker()` (`GET /sw.js`, live only, serves `public/beta/sw.js`), `shell()`. `portal.mode:legacy` when live:
  `GET /` and `/login` get the shell, the rest redirects to `appPath()`
- `GET /api/v1/theme` adds `base` ('/beta/' or '/')
- SPA: `APP_BASE` / `IS_BETA` / `SW_SCOPE` / `SW_URL` in `shared/config` (from `location.pathname`); router basename =
  `APP_BASE`; `BASE_PATH` stays the asset base. `UpdatePrompt` registers the worker with `workbox-window` (the
  `virtual:pwa-register` URL is fixed at build time) only after `useAppBase()` is `ok`; on a base mismatch it unregisters
  this base's worker and `location.replace`s to the same page at the server's base. "تجريبي" badge and "back to the old
  portal" links only while `IS_BETA`
- Worker: precache URLs absolute (`modifyURLPrefix: { '': '/beta/' }`); scope-aware navigation route (root scope skips
  server paths), notification click default/focus follow the scope
- Installed PWAs from the beta keep their `/beta/` start URL; it redirects to `/` (reinstall for the root scope)
- Tests: PortalModeTest (+5, live test rewritten), PushTest (live URL), PortalImpersonationTest (+1); PHP 190 passed
  (ExampleTest fails before too); Vitest 85 passed, typecheck / ESLint / Steiger / build clean

### Superadmin opens the portal as a student (2026-10-15)
- Backpack → Students: "فتح البوابة" line button (`btn btn-sm btn-primary`, Superadmin only) →
  `POST /admin/student/{id}/portal` → signs the `student` guard in as that student (no remember cookie) → `/beta/`
- Generalised: `TeacherImpersonation` → `PortalImpersonator` (student + teacher, log field `acting_as`),
  `ImpersonateTeacherController` → `PortalImpersonationController@student|teacher`, button view
  `portal_as_teacher` → `open_portal` (shared by Users and Students), lang keys `open_portal` / `open_portal_hint`
- Blocked while impersonating (403): `PUT student/password`, `PATCH student/notification-settings`,
  `POST|DELETE push/subscriptions`; `POST student/messages/{id}/read` answers without marking read
- `MeResource.impersonated_by`; SPA banner for students too (`impersonation.banner` takes `{name}`), profile hides
  the password form and notifications card, no push soft prompt
- Tests: `PortalImpersonationTest` (15) + Vitest student banner; PHP 185 passed (ExampleTest fails before too),
  Vitest 85 passed

### Admin settings: key column and category filter (2026-10-15)
- `routes/backpack/settings.php` overrides the package route → `Admin\SettingCrudController` (extends the
  backpack/settings controller)
- List: `key` column (searchable, default order), `category` column, `category` dropdown filter; edit form shows
  `key` disabled above the name
- Category comes from the key (no schema change): `portal.*` → portal, `payment_types|payment_sources` →
  payments, `*.pre|*.pro|*.title` → reports, the rest → general. `categoryOf()` (PHP) and `scopeCategory()` (SQL)
  must stay in sync; a new setting key outside these patterns lands in "general"
- Labels in `resources/lang/ar/setting.php`
- Tests: `Admin/SettingCrudTest` (4)

### Students change their password on the portal (2026-10-15)
- `PUT /api/v1/student/password` (`throttle:student-password`, 10/hour per student): current password required,
  new one 8–128, confirmed, different; other devices sign out (remember token rotates), this device stays signed in
- `TeacherPasswordController` → `PortalPasswordController`, shared by both roles (guard from `PortalLogin::GUARDS`);
  the security log adds `role`
- SPA: `ChangePasswordForm` takes `role` (`useChangePasswordMutation`); `/profile` shows it instead of the
  “visit the administration” note, plus a forgotten-password note (`profile.forgotPasswordNote`; `profile.password` /
  `passwordNote` removed)
- Tests: `StudentPasswordTest` (6), 1 Vitest; PHP 175 passed (ExampleTest fails before too), Vitest 84 passed

### Portal texts from Backpack Settings (2026-10-15)
- Migration `2026_10_15_200000_add_portal_branding_to_settings_table`: `portal.name` (بوابة الغدير),
  `portal.short_name` (الغدير), `portal.centre` (جمعية الغدير لعلوم القرآن), `portal.description`; text fields
- `PortalBranding` (empty values fall back to the name / centre); `GET /api/v1/theme` adds the four texts
- `PortalAppController`: `/beta/manifest.webmanifest` built from the settings (icons unchanged); the app shell
  route fills `<title>` and `apple-mobile-web-app-title` (escaped)
- SPA: `entities/branding` (`useBranding`), used by login (title, alt, ©), shell sidebar, maintenance screen,
  install banner (`pwa.installTitle` takes `{name}`), receipt (`centre` prop); `app.name` / `app.centre` removed
  from ar.json; `portal.push.centre` removed (message push title = `portal.centre`); SW fallback title empty
- Images not touched (logo / icons stay files)
- Tests: PortalThemeTest (+3), PortalModeTest shell test, PushTest title, Vitest branding case; PHP 175 passed
  (ExampleTest fails before too), Vitest 83 passed

### Superadmin opens the portal as a teacher (2026-10-15)
- Backpack → Users: "Open portal" line button (Superadmin only, rows with the Teacher role, not self) posts to
  `POST /admin/user/{id}/portal` (`ImpersonateTeacherController`) → signs the `web` guard in as the teacher
  without a remember cookie (any portal user on the device signs out first, current device only) → `/beta/teacher`
- `TeacherImpersonation` service (session keys, TTL + Superadmin check, security log `portal.impersonate.start|end|action`)
- `PortalImpersonation` middleware: expiry → 401; writes logged; `:deny` on `PUT teacher/password|username`,
  `POST|DELETE teacher/photo`
- Logout ends only the impersonation (Backpack session kept); a real login clears the impersonation keys
- `TeacherMeResource.impersonated_by`; SPA: red banner with "إنهاء" in the shell, profile hides photo / username /
  password forms; `LogoutButton` takes a `label`
- Config `portal.impersonation.ttl_minutes` (`PORTAL_IMPERSONATION_TTL_MINUTES`, default 60)
- Tests: `TeacherImpersonationTest` (9) + Vitest banner case; PHP suite 166 passed (ExampleTest `/` 302 fails before
  this change too), Vitest 82 passed

### Users sign in with a username (2026-10-15)
- Migration `2026_10_15_000000_add_username_to_users_table`: `users.username` varchar(50), unique, NOT NULL (MySQL);
  existing users backfilled from the part of their email before "@" (lowercased, `a-z 0-9 . _ -` kept, a number
  appended on a clash, `user{id}` when shorter than 3). Verified on the dev DB (6 users) incl. rollback
- `User::USERNAME_PATTERN` + `User::usernameRules($ignoreId)` (required, 3–50, pattern, unique); mutator lowercases
  and trims. Email kept as contact information
- Admin: Backpack `authentication_column` → `username` (login page and "My account"); users CRUD has a username
  column + field (`App\Http\Requests\Admin\UserStoreCrudRequest` / `UserUpdateCrudRequest` extend Permission
  Manager's); `localize.username(_hint)` + `validation.custom.username.regex` in ar/en
- Portal: `LoginRequest` / `PortalLogin` / `LoginFailedException` use `username` (length-only check at login, so a
  malformed name fails like a wrong one); `TeacherMeResource` adds `username`
- `PUT /api/v1/teacher/username` (`TeacherUsernameController`, `throttle:teacher-username` 10/hour): same rules,
  `current_password` required and checked (wrong ones count toward the limit), `portal.username.*` messages,
  `portal.username.changed` / `wrong_current` security log; returns the viewer
- Email optional (decision 2026-10-15): migration `2026_10_15_100000_make_users_email_nullable` (MySQL `MODIFY`;
  `down()` fills missing emails with `{username}@no-email.invalid`), `User::emailRules()` (nullable, email, unique)
  in the admin requests; `TeacherViewer.email` is `string | null`; the password log uses `user_id`
- SPA: login teacher tab asks for the username; `features/change-username` (`ChangeUsernameForm`, writes the answer
  into the `me` cache) on `/teacher/profile`, which also shows username and email
- Tests: `TeacherUsernameTest` (7), `Admin\UserUsernameTest` (7, incl. several users without an email on create and update), `TeacherApiTest` login tests updated, 1 Vitest;
  `TeacherPasswordTest` fixed (needed the SPA `Referer` for a session) and now passing. Backend 156/157 (only the
  scaffold `ExampleTest` fails: `/` redirects), frontend 81/81, typecheck / ESLint / Steiger clean

### Teachers edit their messages (2026-10-15)
- `PATCH /api/v1/teacher/messages/{id}` `{body}` (`MessageController::update`): own messages only (404 otherwise,
  deleted included), body trimmed, 1–500; recipients and read state stay; no new push
- `edited_at` on sent and inbox items (`updated_at` when later than `created_at`, so admin edits count too)
- SPA: `features/edit-message` (`EditMessageButton`: sheet with textarea + counter, save disabled while unchanged)
  next to Delete on each sent card; "· معدّلة" on the sent card and the student's inbox card
- Tests: 5 PHPUnit in `MessagesTest`, 1 Vitest in `messages.test.tsx`

### Teacher password change (2026-10-15)
- `PUT /api/v1/teacher/password` (`TeacherPasswordController`, now `PortalPasswordController`, `throttle:teacher-password`, 10/hour per teacher):
  `current_password` checked with `Hash::check`; new password 8–128, `confirmed`, `different:current_password`;
  Arabic messages in `portal.password.*`; success / wrong-current events go to the `security` log channel
- Saving rotates the remember token (`SignsOutDevicesOnPasswordChange`), so other devices sign out; the current
  device is logged in again with a fresh remember cookie
- SPA `features/change-password` (`ChangePasswordForm`, zod schema mirrors the server) on `/teacher/profile`,
  replacing the "contact administration" note; `TeacherPasswordTest` (5 tests) + a profile test in `teacher-panel.test.tsx`

### Receipt printing fix (2026-10-14)
- "طباعة" on the payment receipt printed blank pages: `.print-area { position: fixed }` sat inside daisyUI's
  transformed, `overflow-hidden` `.modal-box`, which collapsed to 0 px and clipped it; the hidden page behind still
  added pages
- `app.css` print rules now `display: none` everything off the path to `.print-area`, reset the modal box's
  translate / scale / clip, clear daisyUI's open-modal `:root` darkening, and print the header band in colour
  (`print-color-adjust: exact`); verified with a headless Chromium PDF (one page, receipt only)

### Teacher photos (2026-10-14)
- `users.photo_path` (migration `2026_10_14_100000_add_photo_path_to_users_table`); files on the public disk under
  `teachers/photos/{user_id}/`
- `App\Services\Portal\ProfilePhotos` (GD re-encode, EXIF strip, 512 px) is now the shared base of `StudentPhotos`
  (`students/photos`) and the new `TeacherPhotos` (`teachers/photos`)
- Admin: `UserCrudController` has a `photo_path` upload field (create / edit, clear button removes it) and a list
  column; the upload is validated (jpeg/png/webp, ≥ 300×300, ≤ 2 MB) and stored through `TeacherPhotos`
- Teacher portal: `POST|DELETE /api/v1/teacher/photo` (`throttle:teacher-photo`, 5/hour); `TeacherMeResource.photo_url`;
  SPA `features/manage-teacher-photo` on `/teacher/profile` (add / change / delete with an in-page confirm sheet)
- Student portal: `teacher_photo_url` on each `CourseDetail.curricula[]` (from the `user_id` of the `teachers` JSON row;
  null when the row has no linked user) and on inbox messages (the sender); shown in the timetable, the hero's next
  session and the message card
- Course info section (`CourseMeta`) leads with a "المدرّسون" block: one card per teacher name, 64×80 arch photo
  (initial when none), name and their subjects
- `resizeImage` moved from `features/manage-student-photo` to `shared/lib`
- Tests: 8 PHPUnit (`TeacherPhotoTest`), 3 Vitest (teacher profile upload, inbox photo, course info teachers)

### Admin messages CRUD (2026-10-14)
- `/admin/message` (`MessageCrudController`, Superadmin only; sidebar under academic registration): list with
  sender, classroom, text, read progress (`read / recipients`); show with each recipient's read state; edit the text
  only (recipients and read state stay); delete is the model's soft delete (leaves every inbox at once)
- Create: classroom + chosen students (or "all students of the classroom"), validated by `MessageRequest` (students
  must be enrolled); the admin is the sender; `MessageSent($message, sendNow: true)` makes `SendMessagePush` send the
  pushes at once (no teacher app to drain the outbox), like an admin attendance save
- `api/student` narrows to one classroom when a Backpack `include_all_form_fields` form sends `class_room_id`
- `Message` uses `CrudTrait`; translations in `lang/ar/message.php`
- Tests: 7 PHPUnit (`tests/Feature/Admin/MessageCrudTest`)

### Portal theme setting, dark mode removed (2026-10-14)
- Backpack setting `portal.theme` (migration `2026_10_14_000000_add_portal_theme_to_settings_table`, select):
  `ghadeer` (default) / `girls` (Islamic theme for girls) / `cupcake` / `autumn` / `wireframe`; `App\Support\PortalTheme`
- Public `GET /api/v1/theme` (`PortalThemeController`), outside `portal.mode` so maintenance / closed screens are
  themed; throttled 60/min per IP
- SPA: `features/theme-switch` (system / light / dark per device) replaced by `features/portal-theme`; theme
  controls removed from the login, top bar, sidebar and both profile pages; `ghadeer-night` deleted;
  `theme-boot.js` paints the last theme the server sent (localStorage `gh-theme`), then the app fetches the current one
- cupcake / autumn / wireframe are DaisyUI's own presets, enabled in `app.css` with per-theme brand extras (hero, viz)
- Turnstile pinned to `theme: 'light'`
- Tests: 3 PHPUnit (`PortalThemeTest`), 2 Vitest (`themes.test.ts`); profile test now checks the control is gone

### Roster prev/next loading state (2026-10-14)
- `TeacherRosterPage` uses RTK Query `currentData` + `isFetching`: while the previous / next session is fetched
  the stale roster is replaced by `LoadingBlocks`; refetches of the same session keep the roster on screen

### Portal stays signed in until logout (2026-10-14)
- `AuthController` logs in with remember-me (`login($user, true)`); logout uses `logoutCurrentDevice()` (forgets this
  device's cookie, other devices stay signed in); `SignsOutDevicesOnPasswordChange` (Student, User) cycles the
  remember token when the password changes
- SPA base query: 419 → `GET /sanctum/csrf-cookie` and retry once (stale CSRF after the server session ended);
  "session expired" text no longer says "inactivity"
- Service worker: a replayed offline roster save that gets 419 is handed to an open page (`ROSTER_REPLAY`,
  `useRosterHandoff`), which resends it with a fresh token; kept in the queue when no page is open
- Tests: 4 PHPUnit (remember cookie set, cookie alone restores the session, wrong cookie refused, password change
  cycles the token; logout forgets the cookie) + 1 Vitest (419 retry)

### Push outbox + CPR length (2026-10-13)
- Login CPR: digits only, 1–12 long (`digits_between:1,12` in `LoginRequest`, same rule + `maxLength` in the SPA)
- Push outbox (`push_deliveries`, `App\Services\Portal\PushOutbox`): teacher saves and messages record one row per
  reachable student (subscribed device + type on) and answer at once with `push_pending`; admin easy-form saves send
  at once. `GET /teacher/push-deliveries`, `POST /teacher/push-deliveries/{id}/send` (own rows, atomic claim,
  3 attempts, 24 h expiry, stuck `sending` retried after 5 min)
- SPA `features/send-notifications` (slice + one global runner: sequential sends, pauses offline / on 429, resumes
  on `online`, app start and `ROSTER_SYNCED`) and `pages/teacher/notify` (progress, per-student status, retry,
  "متابعة"); roster save and compose redirect there when `push_pending > 0`
- Tests: PushTest rewritten for the outbox (15 tests), roster→notify flow in Vitest

### Message pruning without cron (2026-10-13)
- `App\Jobs\PruneOldMessages` (plain job, `dispatchSync()`), used by `messages:prune` and by the admin dashboard
- `App\Http\Controllers\Admin\AdminController` extends Backpack's: first `/admin/dashboard` visit of the day prunes
  (`Cache::add` per date; failures reported, dashboard still opens). `backpack.base.setup_dashboard_routes = false`,
  dashboard + `/admin` redirect routes moved to `routes/backpack/custom.php`; 1 PHPUnit test

### Push notifications — P3.4 (2026-10-13)
- `laravel-notification-channels/webpush` 5.1 (minishlink/web-push 6, Laravel 8 / PHP 8.0); `config/webpush.php`,
  `push_subscriptions` (Student `HasPushSubscriptions`), `student_notification_settings`; expired subscriptions
  (404/410) are deleted by the package
- `AttendanceFirstRecorded` (fired after commit when the Attends record is created: teacher save or admin easy form;
  edits never) → `AttendanceRecordedPush` per student with their own status; `MessageSent` → `MessageReceivedPush`
  (centre name + first 80 chars). Queued notifications; skipped without VAPID keys or when the student turned the
  type off; listeners never let a push failure fail the save (found by test: with a `sync` queue it returned 500)
- API: `GET /push/config`, `POST|DELETE /push/subscriptions` (https endpoints, students only),
  `GET|PATCH /student/notification-settings`
- SW: `push` (rtl, tag per session/message) + `notificationclick` (focus / open `/beta/attendance?date=` or
  `/beta/messages#id`), pages refresh on `PUSH_RECEIVED`
- SPA: `features/enable-notifications` (profile card: enable / granted / denied / iPhone install note, two toggles,
  soft prompt once after first login), logout unsubscribes this device; 9 PHPUnit + 3 Vitest
- Not verified end to end against a real push service (needs VAPID keys and a browser on a phone)

### Messages — P3.3 (2026-10-13)
- Tables `messages` (soft delete, indexes) + `message_recipients` (`read_at`, unique per message/student);
  `messages:prune` daily (90 days, needs the `schedule:run` cron); `MessageSent` event for push (P3.4)
- API: teacher `GET|POST /teacher/messages`, `DELETE /teacher/messages/{id}` (own only); student
  `GET /student/messages`, `POST /student/messages/{id}/read` (idempotent, 404 when deleted/foreign);
  `/me.unread_messages`. Scope tests: other teacher's / archived classroom → 404, foreign student → 422,
  students cannot send, body 1–500, delete own only, prune
- SPA: `entities/message`, features read / send / delete; widgets inbox-list (pagination, `#id` deep link,
  "no longer available"), sent-messages (read progress, delete sheet); student inbox + top-bar envelope badge,
  sidebar entry, Home unread strip; teacher Messages tab + compose task screen (classroom chips, select all,
  500-char counter, leave guard); "مراسلة طلاب الصف" from a classroom
- `LeaveGuard` moved to `shared/ui` (roster + compose)

### Teacher SPA — P3.2 (2026-10-13)
- Backend: `students.photo_path`; teacher photo upload/delete (GD re-encode to 512 px WebP, EXIF/appended bytes
  dropped, previous file deleted, 5/hour per teacher+student); `photo_url` in /me, profile, roster, classroom rows;
  Backpack teacher pages redirect to `/beta/teacher` only when `portal.mode = live` (Q13)
- Dockerfile: GD with JPEG + WebP; Debian bullseye (EOL) packages from archive.debian.org; `.dockerignore`
- SPA: login teacher tab (email); viewer = student | teacher; role-home redirects; role-aware shell (teacher nav,
  task screens with back arrow and no dock)
- `entities/class-session`, `entities/classroom`, `entities/student`; `features/mark-attendance` (draft + undo,
  five-state control, quick all-present/absent, save bar with first-save / edit hints, leave guard via `useBlocker`,
  offline queue client), `features/manage-student-photo` (on-device resize ≤ 1024 px with EXIF rotation, rear
  camera / gallery, delete confirmation sheet); widgets `roster-editor`, `today-sessions`, `classroom-students`,
  `follow-up-list`; pages today, roster, classes, classroom, stats, profile (lazy)
- Service worker: roster PUTs that fail offline go to a Workbox Background Sync queue (replayed on reconnect or
  next worker start), pages are told when sent/refused; logout clears the queue
- Tests: 7 PHPUnit (photos), 7 Vitest (teacher panel). EXIF-rotation on the server is untested (GD cannot write
  EXIF to build a fixture); the client applies the rotation before upload anyway

### Teacher API — P3.1 (2026-10-13)
- Login: `role: teacher` + email (web guard, Spatie role Teacher required; other admin users get the generic
  failure); same lockout / throttle / Turnstile / honeypot (`PortalLogin`, keyed per account); `GET /me` →
  `TeacherMeResource`; `sanctum.guard = [student, web]` (Backpack keeps its own `backpack` guard)
- `portal.role:student|teacher` middleware on the route groups (mutation-tested)
- `TeacherScope`: classrooms whose `teachers[].user_id` is the teacher, in the latest active course only;
  within a classroom only the teacher's subjects
- `TeacherSessions`: expected sessions × own records (report hour rule), status recorded / pending / open /
  upcoming, editable = date ≤ today (Q9, Q10); code `date#HH:MM#classroom#curriculum`
- `AttendanceRecorder`: idempotent save (find by hour-match or create) + pivot sync; the admin easy form now
  syncs through it too
- `ClassroomStats`: per-student and overall metrics with the shared cell rule; follow-up = rate < 80% or ≥ 3 absences
- Endpoints: `/teacher/today`, `/classrooms`, `/classrooms/{id}`, `/classrooms/{id}/sessions`,
  `GET|PUT /sessions/{code}`, `/stats?classroom_id=`; 11 feature tests

### Portal beta launch wiring — P2.7 (2026-10-13)
- `/beta/{path?}` serves `public/beta/index.html` (no-cache); hashed assets and `sw.js` are static files
- Backpack setting `portal.mode` (migration `2026_10_13_000000_add_portal_mode_to_settings_table`, default beta) read
  by `App\Support\PortalMode`; `PortalMode` middleware (`portal.mode:legacy` on the old portal, `portal.mode:api` on
  `/api/v1`) runs before authentication (kernel `$middlewarePriority`): live → old URLs redirect to `/beta`,
  maintenance → notice + API 503 `portal_maintenance` (Retry-After), closed → notice + API 403 `portal_closed`;
  `/admin` untouched (tested in every mode)
- SPA `PortalGate`: maintenance / closed screens from any API answer, retry for maintenance
- Old portal: dismissible "try the new portal" banner (beta only, ar + en), old `POST /login` rate-limited like
  the new one (`legacy-student-login`)
- P0.1 code part: `TrustProxies` from `TRUSTED_PROXIES` (`cloudflare` keyword expands to Cloudflare ranges,
  `127.0.0.1` for a Tunnel); spoofed X-Forwarded-For from outside the proxies is ignored (tested)
- Verified in Chrome via Laravel: `/beta/login` renders, service worker active (scope `/beta/`), shell precached,
  manifest valid, no API response cached

### Portal PWA — P2.6b (2026-10-13)
- `vite-plugin-pwa` (injectManifest, `src/app/pwa/sw.ts`, own `tsconfig.sw.json`): app shell precached
  (26 files, no fonts), navigation fallback for `/beta/*`, fonts CacheFirst on first use, `/api` + `/sanctum`
  never routed (network only); manifest per PLAN §4 (ar/rtl, scope `/beta/`, standalone, shortcuts);
  icons generated from the arch logo (192, 512, maskable 512, apple-touch 180)
- Update prompt (`registerType: 'prompt'`, reload only on tap); install banner (`features/install-app`: Android
  native prompt, iOS how-to sheet, from 2nd visit or after login, 30-day dismiss, never in standalone);
  logout drops runtime caches
- Teacher offline save queue moves to P3.2 (needs the roster)

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

### Teacher classroom info tab fix (2026-10-14)
- `GET /teacher/classrooms/{id}` merged the detail `course` object with `+`, which kept the summary's
  string `course` — the info tab showed no course name or dates. Now `array_merge`; covered by
  `TeacherApiTest::test_a_classroom_carries_its_course_details`

### "Open portal" 419 fix (2026-10-16)
- Opening a second student / teacher from the same Backpack page answered 419: the session kept the first
  user's `portal_password_hash_*`, `PortalAuthenticateSession` saw a mismatch, signed the new user out and
  regenerated the CSRF token. `PortalImpersonator::start` now calls `PortalAuthenticateSession::remember`;
  covered by `PortalImpersonationTest::test_opening_a_second_student_keeps_the_portal_and_the_admin_csrf_token`
