# Al-Ghadeer Portal — Student & Teacher Frontend Plan

> Status: **Phase 1 (design) delivered** in this folder, student panel + teacher panel · 2026-10-12
> Supersedes the Blade/Alpine approach in `_context/feature/01-frontend-student-react.md`.
> Phase 2 is a React SPA that talks to Laravel only through a versioned JSON API.
> **Rollout: the new panel launches as a beta at `/beta`, and the current student portal (`/login`, `/dashboard`) stays active and unchanged** until the centre decides to switch (§4 “Beta rollout”).

---

## 1. Scope

| Phase | What | Output |
|---|---|---|
| **1 — Design** (now) | Static HTML + Tailwind 4 + DaisyUI 5 mockups, every screen + state | `_brand/new_design/*.html` |
| **2 — Build** (next) | React + Redux Toolkit + RTK Query + DaisyUI SPA; Laravel JSON API under `/api/v1` | `frontend/` app + `app/Http/Controllers/Api/V1/*` |
| **3 — Teacher** (design done, build after student) | Same login, teacher role: attendance editing, classroom stats, student photos | `t-*.html` mockups; `/beta/teacher/*` routes in the same SPA |

### Student requirements → screens

| Requirement | Screen | File |
|---|---|---|
| Login | Login | `login.html` |
| Current course (active + latest + only one): timetable, payments, marks/certificate | Home | `home.html` |
| Old courses, archived (read-only) | Archive | `archive.html` |
| Check attendance | Attendance log | `attendance.html` |
| Attendance statistics | Statistics | `stats.html` |
| Profile + upload own photo | Profile | `profile.html` |
| Forgot/reset password → “visit the centre administration” | Login dialog + Profile note | `login.html`, `profile.html` |
| Protect login from attacks and bots | Login states + §6 | `login.html` |
| Shared error/empty/loading states | System states | `states.html` |

### Teacher requirements → screens (§8)

| Requirement | Screen | File |
|---|---|---|
| Teacher login (same login screen) | Login, “مدرّس” tab | `login.html?role=teacher` |
| Add or edit attendance records | Today + attendance roster | `t-home.html`, `t-attendance.html` |
| Read + edit the current course (active + latest + one) | My classes + classroom | `t-classes.html`, `t-classroom.html` |
| Read-only archived courses | Classroom in locked mode (no archive list page; entry point open, §10) | `t-classroom.html?id=870` |
| Classroom statistics | Classroom stats | `t-stats.html` |
| Upload / delete student photos (own classrooms) | Classroom → student sheet | `t-classroom.html` |
| Account, password notice, theme, logout | Profile | `t-profile.html` |

Open `index.html` for the screen gallery and live token swatches. Every page has a **“حالات التصميم”** pill (mockup-only) that switches between ready / loading / empty / error states.

**Previewing:** serve the folder (e.g. `python -m http.server` inside `_brand/new_design`) and open `http://localhost:8000/`. Fonts, DaisyUI, Tailwind and Lucide load from CDNs.

---

## 2. Design system

### Direction
Brand mark = teal **mihrab arch** with a Qur'an stand. The UI takes three motifs from it, and uses each sparingly:

1. **Arch mask** (`.arch`): frames student photos, empty-state icons, and the screen-gallery tiles.
2. **Riwaq (colonnade) pattern** (`.bg-pattern`): one row of the logo's outer arch silhouette (filled, no centre hole) repeated along the **bottom edge** of brand surfaces, faint (10% light / 8% dark). It never sits behind the text at the top. Used on the hero band, login panel, sidebar card, profile banner, and stats hero. Chosen from `pattern-samples.html` (sample 3).
3. **Divider** (`.brand-rule`): a solid 2px `base-200` line, no fade, used on brand surfaces.

The rest of the UI stays quiet: warm parchment page, white cards, deep ink-teal text.

### Themes (DaisyUI 5 custom themes, `assets/theme.css`)

| Token | `ghadeer` (light) | `ghadeer-night` (dark) | Notes |
|---|---|---|---|
| primary | `#127A70` | `#2BB3A5` | One step deeper than the logo teal `#16897E`: white on `#16897E` is ≈4.3:1 (fails AA), on `#127A70` ≈5.2:1 |
| secondary | `#0F3F3A` | `#A9D9D2` | Ink-teal headings / hero band |
| accent | `#C9A24B` | `#DCB862` | Illumination gold: grades, awards |
| base-100/200/300 | `#FFF` / `#F5F3EC` / `#E7E2D4` | `#14201E` / `#0E1715` / `#22312E` | Parchment page, white cards |
| viz-1 | `#0A8C7D` | `#1FA595` | Single-series chart teal (passes the dataviz validator; brand teal is too gray at chroma 0.096) |
| status | present `#0CA30C` · late `#FAB219` · absent `#D03B3B` | same | **Reserved.** Never used as a series colour; always shown with icon + label |

- Theme choice: system / light / dark, stored per device (`localStorage gh-theme`). Phase 2 keeps this in a `uiSlice` and persists it the same way.
- Radii: box `1.25rem`, field `.75rem`, selector `2rem`. Depth on (light), off (dark).

### Typography
- **IBM Plex Sans Arabic**: titles and headers (`h1`–`h6`, `.font-display`), weights 500/600/700.
- **Rubik**: paragraphs, labels, small text, buttons, and numbers (`body` default), weights 400/500/600/700. Use `.num` (tabular figures) in tables and KPIs.
- Digits: Western (0-9) everywhere, matching existing reports and the CPR card. Dates use Gregorian with Hijri as secondary text (`Intl` `islamic-umalqura`).
- Currency: Bahraini dinar with 3 decimals, e.g. `45.000 د.ب`.

### Attendance status encoding
Codes match the admin `student_attend_list` report legend:

| State | Label | Code | Icon | Colour |
|---|---|---|---|---|
| present | حاضر | ✓ | circle-check | good |
| late | متأخر | ت | clock | warning |
| late_excused | متأخر بعذر | تع | clock-alert | warning + **hatch** |
| absent_excused | غائب بعذر | غع | file-check | critical + **hatch** |
| absent | غائب | ✗ | circle-x | critical |
| unrecorded | لم يُرصد | — | hourglass | neutral |

“Excused” is a texture (45° hatch), not a sixth hue. That keeps the status palette at three colours and stays readable for colour-blind users and in print.

### Layout: mobile is the primary target
About **90% of visits are on phones**, so every screen is designed at **360–430px first**. Tablet (`sm` 640px) and desktop (`lg` 1024px) are enhancements of that layout, not the starting point.

**Shell**
- Phones: slim sticky top bar (56px + notch inset: logo, page title, theme, avatar) and the **DaisyUI `dock`** with 5 tabs at the bottom, in thumb reach. Content gets bottom padding for the dock and the iOS home indicator (`env(safe-area-inset-bottom)`).
- `lg+`: the dock is replaced by a persistent **sidebar on the start (right) side**. Content max-width `72rem`.
- `viewport-fit=cover` with safe-area insets top and bottom; a `theme-color` meta follows light/dark, so the browser bar matches the app.

**Touch rules**
- Tap targets **≥ 44px** (rows, chips, day cells, dock items). Whole list rows are the tap target (payments, archive cards), not small inline buttons.
- Inputs and selects ≥ 16px so iOS doesn't zoom on focus; `touch-action: manipulation` (no double-tap delay); no tap-highlight flash.
- Keyboard hints: `inputmode="numeric"` for CPR, `enterkeyhint` next → go, and Enter on CPR moves to the password field.
- Primary actions are full-width `btn-lg` on phones (`w-full sm:w-auto`).

**Phone patterns used instead of desktop patterns**
| Desktop habit | Phone pattern here |
|---|---|
| Dropdown filter | **Chip rail** (horizontal swipe): subjects, attendance states |
| Custom-styled dropdown for the course | **Native `<select>`**, which opens the phone's own picker |
| Side panel / popover | **Bottom sheet** (`modal-bottom` + grab handle): calendar day details, photo upload, receipt |
| 5 timetable cards stacked | **Day-chip selector** (أحد … خميس), showing one day; today preselected |
| Wide data table | **List rows** with a thin progress bar (marks) |
| Scrolling tab strip | **Equal-width segmented tabs** (3 per bar) that stick under the top bar |
| Secondary info always open | **Collapsed `<details>`** on phones (course info), open from `sm` up |
| Drag-and-drop upload | **التقاط صورة** (`capture="user"`, front camera) + **من المعرض** buttons |

**Content priority on the first phone screen (home):** greeting + course name, next session, then the 2×2 KPI tiles. Everything else is one tap away.

- Cards = `.card-soft` (1px border, no heavy shadows), `p-4` on phones, `p-6` from `sm`.
- RTL everywhere: logical utilities only (`ms-*`, `me-*`, `ps-*`, `start-*`, `border-e`, `rounded-e`).

### Navigation (5 tabs)
| Tab | Label | Icon (Lucide) | Route (Phase 2) |
|---|---|---|---|
| Home | الرئيسية / الدورة الحالية | book-open | `/` |
| Attendance | الحضور / سجل الحضور | calendar-check | `/attendance` |
| Stats | إحصائيات | chart-pie | `/attendance/stats` |
| Archive | الأرشيف | archive | `/archive`, `/archive/:courseId` |
| Profile | حسابي | user-round | `/profile` |

Routes are relative to the SPA base. During the beta, the base is `/beta` (e.g. `/beta/attendance`), see §4 “Beta rollout”.

### Charts (stats page)
Built with the dataviz method:
- **Hero figure + meter:** overall rate.
- **KPI tiles:** recorded / on-time / late / absent.
- **One stacked composition bar:** status colours, 2px gaps, hatch for excused, hover tooltip, and a “عرض كجدول” table view.

Phase 2 keeps these as small hand-written HTML components, and no chart library is needed.

---

## 3. Screen specs

### 3.1 Login (`login.html`)
- Role tabs **طالب / مدرّس**. The teacher tab is disabled with a “قريبًا” badge in Phase 2 and enabled in Phase 3.
- CPR: numeric, exactly 9 digits, `dir=ltr`, `autocomplete=username`. Password: show/hide toggle, Caps-Lock hint, `autocomplete=current-password`.
- Hidden honeypot field `website`, plus a human-verification slot (Turnstile, see §6).
- States: idle · submitting · invalid input · wrong credentials (generic message, remaining attempts) · **locked** (countdown, form disabled) · session expired.
- **Forgot password** opens a dialog: “يرجى مراجعة قسم إدارة المركز شخصيًا مع إحضار البطاقة الشخصية”. There is no self-service reset, no email/SMS, and no reset endpoint. Office hours are a placeholder for the admin to fill in (see §10).

### 3.2 Home: current course (`home.html`)
- **Current course rule:** the student's classroom whose course has `is_active = 1`, latest by `start_date` (then `id`). Exactly **one** or none. Every other enrollment counts as archived.
- Hero: arch avatar, “الدورة الحالية · نشطة”, course long name, semester · Hijri year · classroom, and a **next session** card computed from `attend_table`.
- KPI tiles: attendance rate (→ stats), attended/recorded (→ attendance), total paid (→ payments tab), marks recorded so far (→ marks tab).
- Tabs (deep-linkable `#payments`, `#marks`):
  - **الجدول الدراسي:** classroom number, location, start/end (Gregorian + Hijri), hours, path; weekly schedule grouped by day, today highlighted.
  - **المدفوعات:** total paid and receipts (number, date, type, source, amount). The “الإيصال” button opens a printable receipt (same fields as `payment_print_template`).
  - **الدرجات والشهادة:** `brief_marks` rows with progress bars, total /100, final grade. While the course is in progress the screen shows an info banner and the certificate button is disabled. After publication it shows **تنزيل الشهادة PDF**.
- States: loading skeleton · no active course (CTA to archive) · error with retry.

### 3.3 Archive (`archive.html`)
- Search + year filter in one row. Cards (newest first) show semester · Hijri year · date range, grade, total, and attendance rate.
- A card expands to the same three tabs, read-only, plus a link to that course's attendance log.
- “مؤرشفة / للاطلاع فقط” is shown at both card and page level.

### 3.4 Attendance log (`attendance.html`)
- Filters: course (current marked ●, archived selectable) and subject. View toggle: **calendar | list**.
- Summary strip: radial rate, attended/recorded, legend.
- Calendar: month grid (Sun→Sat, Fri/Sat dimmed), one coded pip per session, today ringed; selecting a day shows its sessions (subject, time, teacher, status chip) in a side panel.
- List: grouped by week (sticky headers), newest first, with status filter chips.
- `?course=<id>` preselects a course (used by archive and stats links).

### 3.5 Statistics (`stats.html`)
Course selector, plus the chart set in §2. Definitions are in §7.4.

### 3.6 Profile (`profile.html`)
- Arch-framed photo with a camera button → upload dialog. The dialog takes drag-and-drop or a file picker, and checks client-side: JPEG/PNG/WebP, ≤ 2 MB, ≥ 300×300. It shows a preview in the arch, a progress bar, and error messages.
- Read-only cards: personal (name, CPR **masked** with reveal, DOB + age, degree), contact (mobiles, email, address), academic (student ID, registration date, Iqraa account).
- **Not shown to students:** `financial_*`, `health_*`, `hawza_history_details`, `student_notes`, `relationship_state`, `family_*` (admin-internal).
- Password card: “visit the administration” notice. Then appearance (system/light/dark) and logout.

---

## 4. Phase 2 frontend architecture

### Stack
| Concern | Choice |
|---|---|
| Build | Vite |
| UI | React 18, **TypeScript** (recommended: API types catch shape drift) |
| State | Redux Toolkit (`authSlice`, `uiSlice`) |
| Server data | **RTK Query** (single `api` slice, tag-based invalidation) |
| Styling | Tailwind CSS 4 + DaisyUI 5 via `@plugin`; `theme.css` tokens move into `@plugin "daisyui/theme"` blocks unchanged |
| Routing | React Router (data router) with role-guarded layouts |
| Icons | `lucide-react` (same names as the mockups) |
| i18n | Arabic-only at launch; strings in `src/i18n/ar.json` behind a `t()` helper so `en` can be added later (project rule: no hard-coded UI strings) |
| Forms | React Hook Form + Zod (login, upload) |
| Tests | Vitest + Testing Library; MSW for API mocks (fixtures = the `GH.DEMO` shapes) |

### Folder layout
```
frontend/
  src/
    app/           store.ts, router.tsx, providers.tsx
    api/           baseApi.ts (fetchBaseQuery + CSRF + 401/419/429 handling)
                   auth.api.ts, student.api.ts, (teacher.api.ts later)
    features/
      auth/        LoginPage, ForgotPasswordDialog, authSlice, RequireAuth
      course/      HomePage, CourseHero, Timetable, Payments, Marks, ReceiptDialog
      archive/     ArchivePage, ArchivedCourseCard
      attendance/  AttendancePage, MonthCalendar, SessionList, StatusChip, StatusPip
      stats/       StatsPage, HeroRate, StatusStack
      profile/     ProfilePage, PhotoUploadDialog
      teacher/     (Phase 3)
    components/    AppShell, Sidebar, Dock, TopBar, EmptyState, ErrorState, Skeletons, Toast
    lib/           format.ts (fmtDate, fmtHijri, fmtTime, money, pct), attendance.ts (STATES)
    styles/        app.css (tailwind + daisyui + theme tokens + motifs)
    i18n/          ar.json
```
Mockup → component mapping: `GH.ui.timetable` → `<Timetable/>`, `GH.ui.payments` → `<Payments/>`, `GH.ui.marks` → `<Marks/>`, `GH.chip/pip` → `<StatusChip/>/<StatusPip/>`, `shell()` → `<AppShell/>`.

### RTK Query
```ts
export const api = createApi({
  baseQuery: baseQueryWithAuth,                // credentials:'include', X-XSRF-TOKEN
  tagTypes: ['Me', 'Course', 'Attendance', 'Stats'],
  endpoints: b => ({
    me:              b.query<Me, void>({ query: () => '/me', providesTags: ['Me'] }),
    currentCourse:   b.query<CourseDetail | null, void>({ query: () => '/student/courses/current', providesTags: ['Course'] }),
    archivedCourses: b.query<CourseSummary[], void>({ query: () => '/student/courses?status=archived', providesTags: ['Course'] }),
    course:          b.query<CourseDetail, number>({ query: id => `/student/courses/${id}` }),
    attendance:      b.query<Session[], { courseId: number; from?: string; to?: string }>({ query: p => ({ url: '/student/attendance', params: p }), providesTags: ['Attendance'] }),
    attendanceStats: b.query<AttendanceStats, { courseId: number }>({ query: p => ({ url: '/student/attendance/stats', params: p }), providesTags: ['Stats'] }),
    uploadPhoto:     b.mutation<{ photo_url: string }, FormData>({ query: body => ({ url: '/student/profile/photo', method: 'POST', body }), invalidatesTags: ['Me'] }),
    login:           b.mutation<Me, LoginBody>({ query: body => ({ url: '/auth/login', method: 'POST', body }) }),
    logout:          b.mutation<void, void>({ query: () => ({ url: '/auth/logout', method: 'POST' }) }),
  }),
});
```
- `baseQueryWithAuth`: on **401/419**, clear auth and redirect to `/login?expired=1`. On **429**, show the lockout/“طلبات كثيرة” state using the `Retry-After` header. On network failure, show the offline banner.
- Do not store tokens in JS. Auth uses HttpOnly cookies (§6).
- `keepUnusedDataFor` defaults; current course and attendance refetch on focus/reconnect.

### Mobile performance budget
Most students are on mobile data, so Phase 2 targets:
- **≤ 170 KB gzipped JS** on first load; routes are code-split (`React.lazy`), and the stats and profile code is only loaded when opened.
- **Fonts:** Rubik (400–700) + IBM Plex Sans Arabic (500–700), Arabic + Latin subsets only, `font-display: swap`, Rubik preloaded (it renders most of the first screen). Self-host them in Phase 2 (one less third-party connection).
- **LCP < 2.5s on a mid-range Android over 4G**; skeletons render immediately from the shell.
- RTK Query cache + `refetchOnFocus`/`refetchOnReconnect`. The last data stays visible offline with the "غير متصل" banner.
- Photos: the server re-encodes to WebP ~512px; the client shows thumbnails at 2× density at most.
- Optional (§10): a web-app manifest + icons, so students can "Add to Home Screen" and open the portal like an app (no service worker needed at first).

### Beta rollout: new panel at `/beta`, old portal stays active
The new student panel ships **side by side** with the current one. Nothing about the existing portal is removed or redirected during the beta.

| URL | Serves | During beta |
|---|---|---|
| `/login`, `/logout`, `/`, `/dashboard` | Current Blade student portal (`StudentAuthController`, `StudentPanelController`) | **Unchanged, still the default** |
| `/beta`, `/beta/*` | New React SPA (this design) | New, opt-in |
| `/api/v1/*`, `/sanctum/csrf-cookie` | JSON API used by the SPA | New |
| `/admin/*` | Backpack admin | Unchanged |

**How it is served** (the app runs in a Laravel Sail-style container, with no separate nginx):
- Vite builds with `base: '/beta/'` and `build.outDir: '../public/beta'`. The hashed JS/CSS in `public/beta/assets/` are served as static files.
- React Router uses `basename="/beta"`. Internal routes become `/beta`, `/beta/attendance`, `/beta/attendance/stats`, `/beta/archive`, `/beta/archive/:courseId`, `/beta/profile`, `/beta/login`.
- One Laravel fallback route for deep links / refreshes, registered **after** the existing portal routes:
  ```php
  // routes/web.php: does not touch the existing /login and /dashboard routes
  Route::get('/beta/{path?}', function () {
      return response()->file(public_path('beta/index.html'));
  })->where('path', '.*')->name('beta');
  ```
- `public/beta/` is a build artifact. Add it to `.gitignore` and build it in deploy/CI.
- **Same origin** for SPA and API, so cookies are first-party, SameSite=Lax, and the CORS surface is minimal.

**Sessions between the two portals:**
- Both portals use the same `student` guard and the same Laravel session cookie (Sanctum's stateful mode is session-based). A student who signs in on either portal is signed in on both, and signing out of one signs out of both. This is expected and convenient.
- The new login at `/beta/login` posts to `/api/v1/auth/login`, which has the new protections (§6). The old `/login` form keeps working as it does today. Adding the same rate limiter to the old `POST /login` is recommended as a separate small change, since otherwise attackers can simply use the old form.

**Switching between versions:**
- New panel: a “تجريبي” badge in the top bar, and a “العودة للواجهة السابقة” link (to `/dashboard`) in the profile screen and sidebar.
- Old portal: a dismissible banner, “جرّب بوابة الطالب الجديدة (نسخة تجريبية)”, linking to `/beta`. This is the only change to the old Blade views, and it is optional.
- Feature flag: `config('portal.beta_enabled')` (env `PORTAL_BETA_ENABLED`). When `false`, `/beta/*` returns 404 and the banner is hidden. It is the kill-switch if anything goes wrong.

**Promotion (after the beta):** once the centre approves (§10), the default can move to the new panel. Either `/` and `/login` redirect to `/beta`, or the SPA moves to `/` with `/beta` kept as a redirect for bookmarks. The old Blade portal is retired in a separate, later step.

---

## 5. Laravel backend work (Phase 2)

Constraints: Laravel 8.12, PHP 7.3/8.0, so no PHP 8.1 syntax. This is an API boundary, so all code goes in `app/Http/Controllers/Api/V1/`.

1. **Sanctum SPA auth** (`laravel/sanctum` 2.x supports Laravel 8): stateful domains = the portal host; `sanctum.guard` = `['student']` (add `'web'` for teachers in Phase 3). API routes use `auth:sanctum`.
2. **Routes** `routes/api.php` → `Route::prefix('v1')`, see §7.
3. **API Resources** (whitelist fields, never `toArray()` the model): `MeResource`, `CourseSummaryResource`, `CourseDetailResource` (timetable from `attend_table` + `teachers` JSON, payments, `brief_marks`), `SessionResource`.
4. **Current-course query**, in one place (e.g. `Student::currentClassroom()`): `classrooms()->whereHas('course', fn($q) => $q->where('is_active', 1))->orderByDesc(course.start_date)->first()`.
5. **Attendance query**: build sessions from the classroom `attend_table` (expected) joined with `Attends` + the five `attends_student_*` relations (actual). Reuse the hour-matching logic already in `AcademiaReportsController::reportStudentAttendList`, extracted into a service so the admin report and the API share it.
6. **Stats endpoint**: computed server-side from the same service (§7.4), so the numbers match the admin report.
7. **Certificate**: `GET /student/courses/{id}/certificate` returns the existing mPDF certificate, rendered synchronously (invariant 5). Only allowed when marks are published and the course belongs to the student.
8. **Photo upload**: migration adds nullable `photo_path` to `students`. Store on the `public` disk at `students/photos/{student_id}/{uuid}.webp` (invariant 6: no binaries in DB). Validate `image|mimes:jpeg,png,webp|max:2048|dimensions:min_width=300,min_height=300`. **Re-encode server-side** (strips EXIF/GPS and polyglot payloads; needs an image library such as Intervention Image, which is not installed yet). Delete the previous file on replace. Throttle to 5 uploads/hour.
9. **Policies**: every `/student/*` query is scoped to `auth('student')->id()`. Never accept a `student_id` from the client.
10. Translations: any server-side messages go into both `ar/` and `en/` (project rule).

---

## 6. Login security: attacks and bots

| Layer | Measure |
|---|---|
| Rate limiting | `RateLimiter::for('student-login')`: **5 attempts / minute per (CPR + IP)** and **20 / minute per IP**. On hit: `429` + `Retry-After`, and the UI shows the countdown (`locked` state). |
| Progressive lockout | After 10 failures for one CPR within 15 min, lock that CPR for 15 min regardless of IP (stops distributed guessing). Logged. |
| Bot challenge | **Cloudflare Turnstile** (managed/invisible mode, no puzzles, privacy-friendly), verified server-side with `siteverify` before checking credentials. Fallback if a third party is unwanted: only show a challenge after 3 failures. |
| Honeypot | Hidden `website` field. If filled, return the same generic failure after a fixed delay. |
| No user enumeration | One message for every failure (“الرقم الشخصي أو كلمة المرور غير صحيحة”), with constant-time comparison and identical response timing for unknown CPRs. |
| Input validation | CPR `digits:9`, password `string|max:128`, rejected before hitting the DB. |
| Sessions | Sanctum stateful cookies: `HttpOnly`, `Secure`, `SameSite=Lax`, `session()->regenerate()` on login, invalidate + regenerate token on logout, idle timeout (e.g. 60 min) leading to the “session expired” state. |
| CSRF | `GET /sanctum/csrf-cookie` before login; RTK Query sends `X-XSRF-TOKEN`. |
| Transport & headers | HTTPS only + HSTS; CSP limited to self (+ Turnstile + fonts); `X-Frame-Options: DENY`; `Referrer-Policy: same-origin`. |
| Audit | Log failed/locked logins (CPR hash, IP, UA, time) for the admin. Optional Backpack list later. |
| Password reset | **None online, by policy.** Admin resets in Backpack after an in-person ID check. No reset endpoint exists, so there is no reset attack surface. |
| Out of scope (no “remember me”) | Shared family devices are common, so no long-lived cookies. |

---

## 7. API contract (v1)

Envelope: `{ "data": …, "meta": { … } }`. Errors: `{ "message": "…", "errors": { field: [..] } }` with standard status codes (401, 403, 404, 419, 422, 429).

### 7.1 Auth
| Method | Path | Body / notes |
|---|---|---|
| GET | `/sanctum/csrf-cookie` | sets XSRF cookie |
| POST | `/api/v1/auth/login` | `{ role:"student", cpr, password, turnstile_token, website }` → `MeResource` |
| POST | `/api/v1/auth/logout` | 204 |
| GET | `/api/v1/me` | `{ role, id, name, first_name, student_id, photo_url, … }` |

### 7.2 Student
| Method | Path | Returns |
|---|---|---|
| GET | `/api/v1/student/courses/current` | `CourseDetail` or `data: null` |
| GET | `/api/v1/student/courses?status=archived` | `CourseSummary[]` (newest first) |
| GET | `/api/v1/student/courses/{courseId}` | `CourseDetail` (`readonly: true` if archived) |
| GET | `/api/v1/student/courses/{courseId}/certificate` | `application/pdf` (403 until published) |
| GET | `/api/v1/student/attendance?course_id=&from=&to=` | `Session[]` |
| GET | `/api/v1/student/attendance/stats?course_id=` | `AttendanceStats` |
| GET | `/api/v1/student/profile` | `Profile` (whitelisted fields, §3.6) |
| POST | `/api/v1/student/profile/photo` | multipart `photo` → `{ photo_url }` |

### 7.3 Shapes (match `assets/app.js` → `GH.DEMO`)
```jsonc
// CourseDetail
{ "id": 41, "classroom_id": 912, "status": "active",        // active | archived
  "long_name": "…", "academic_path": "…", "year": 2026, "hijri_year": "1448", "semester": "…",
  "start_date": "2026-09-06", "end_date": "2026-12-24", "duration": 96,
  "class_room_number": "2-ب", "class_room_name": "…",
  "curricula": [{ "id": 11, "name": "…", "teacher": "…",
                  "slots": [{ "day": 0, "start": "16:00", "end": "17:00" }] }],   // day: 0=Sun … 6=Sat
  "payments": [{ "id": 1043, "date": "2026-09-07", "amount": 30, "type": "…", "source": "…" }],
  "marks_published": false,
  "marks": [{ "key": "finalexam", "label": "الامتحان النهائي", "max": 40, "mark": null }],
  "total_mark": null, "final_grade": null }

// Session
{ "date": "2026-10-11", "start": "16:00", "end": "17:00", "curriculum_id": 11,
  "curriculum": "…", "teacher": "…",
  "state": "present" }   // present | late | late_excused | absent | absent_excused | unrecorded

// AttendanceStats
{ "counts": { "present": 40, "late": 2, "late_excused": 1, "absent": 1, "absent_excused": 2, "unrecorded": 1 },
  "recorded": 46, "attended": 43, "rate": 93.5, "punctual": 87.0 }
```

### 7.4 Metric definitions (proposed; confirm with admin, §10)
- **recorded** = sessions with any record. **unrecorded** sessions are excluded from every rate.
- **attendance rate** = (present + late + late_excused) ÷ recorded.
- **punctual** = present ÷ recorded.
- **absences** = absent + absent_excused (excused still counts as absent, but is shown separately).

---

## 8. Teacher panel (same login, same SPA)

Designed in this folder (`t-*.html`, data + components in `assets/teacher.js`). It uses the same design system, shell, and mobile-first rules as the student panel. The phone is again the main target, because teachers record attendance **in class, one-handed**.

### Access rules (enforced by the API, reflected in the UI)
| Rule | Detail |
|---|---|
| Who | `User` with the Spatie `Teacher` role, `web` guard via Sanctum (`sanctum.guard = ['student','web']`). `/me.role` decides which shell and nav render. |
| Which classrooms | Only classrooms where `ClassRoom.teachers[].user_id = me` (the existing JSON filter in `ClassRoom::getByDate()`). A teacher never sees other teachers' classrooms. |
| Current course | Active + latest + one only. **Read + edit**: attendance records and student photos. |
| Archived courses | `is_active = 0`, **read only**. The UI shows lock badges and hides edit controls; the API answers writes with 403. |
| Future sessions | Can be opened (to view the list) but not recorded until the session starts. **Proposed** (§10). |

### Navigation (teacher dock / sidebar)
| Tab | Label | Icon | Route (under `/beta/teacher`) |
|---|---|---|---|
| Today | اليوم | calendar-clock | `/` (+ `/attendance/:code` opened from here) |
| Classes | صفوفي | school | `/classes`, `/classes/:id` |
| Stats | إحصائيات | chart-pie | `/stats?room=` |
| Profile | حسابي | user-round | `/profile` |

The attendance roster is a **task screen**: a back arrow replaces the logo, the dock is hidden, and a sticky save bar sits at the bottom.

### Screens
1. **Login** (`login.html?role=teacher`): the “مدرّس” tab swaps the CPR field for **email** (`inputmode=email`, `autocomplete=username`) and keeps the same password field, bot check, lockout and generic errors (§6). Success goes to the teacher “اليوم” screen.
2. **اليوم** (`t-home.html`):
   - Greeting with Gregorian + Hijri date and the current course.
   - A **warning card for past sessions that were never recorded**, each with “رصد الحضور الآن”.
   - Today's sessions in time order: the session **in progress** is highlighted with a full-width “رصد الحضور” button; recorded sessions show their tally and “تعديل”; later sessions show “القائمة”.
   - Tiles: classrooms, students, attendance rate, pending sessions. Then the next 3 upcoming sessions.
3. **رصد الحضور** (`t-attendance.html?code=`):
   - Header: the **date is the largest line** (weekday + day + month), then Hijri date and time, then classroom, subject, place, status badge, “last edited” time, and previous/next session buttons.
   - Sticky toolbar: two quick actions, **“الكل حاضر”** and **“الكل غائب”**, which set every student at once (undo restores the saved state), plus a live tally per state and “بدون تحديد”. There is no search: a class roster is short enough to scroll.
   - One card per student: a large arch photo (64×96) spanning both rows of the card, then name, ID and current state chip, then a **five-button segmented control** (✓ حاضر / ت متأخر / تع متأخر بعذر / ✗ غائب / غع غائب بعذر). Buttons are 48px high, use the status colours, hatch the excused states, and give haptic feedback where supported. Changed rows get an edge marker.
   - Sticky save bar: “x من y تم تحديدهم”, a hint, **undo**, and **حفظ**. Saving is enabled only when every student has a state and something changed. Leaving with unsaved changes warns first.
   - Read-only variants: archived course (lock banner, chips only) and not-started session (info banner, list only).
4. **صفوفي** (`t-classes.html`): one card per current classroom with number, subject, place, weekly slots, students, attendance rate, and pending count (highlighted when above 0).
5. **الصف** (`t-classroom.html?id=`): hero with classroom, subject, place, course and slots. Three tabs:
   - **الطلاب:** search, then rows with photo / “بدون صورة” marker, ID, rate (red below 80%) and absences. Tapping a row opens the **student sheet**: big arch photo, rate / late / absent, **التقاط صورة** (`capture="environment"`, the rear camera, since the teacher photographs the student), **من المعرض**, and **حذف الصورة**, which asks for confirmation in a second in-page sheet (never `confirm()`).
   - **الحصص:** pending first, then the next 2 upcoming, then recorded sessions newest-first. Each opens the roster.
   - **معلومات:** classroom and course details.
   - In an archived classroom, the photo actions are replaced by a lock note and sessions open read-only.
6. **إحصائيات الصفوف** (`t-stats.html`): a classroom chip rail (كل الصفوف / each classroom), then:
   - The headline rate with a meter.
   - Tiles for students, on time, late, and absent.
   - The status breakdown bar (status colours + hatch, same as the student stats).
   - **طلاب يحتاجون متابعة**: rate < 80% or ≥ 3 absences, sorted worst first and linking to the classroom.
   - Rate per classroom (in “all” view only, a single-series bar).
   - A folded table of all students.
7. **حسابي** (`t-profile.html`): name, email (login), phone, assigned classrooms, the “visit the administration” password note, theme, and logout.

### Teacher API (v1)
| Method | Path | Notes |
|---|---|---|
| GET | `/api/v1/teacher/today` | Today's sessions across my current classrooms + pending (unrecorded past) sessions |
| GET | `/api/v1/teacher/classrooms?status=current\|archived` | My classrooms with counts and rate |
| GET | `/api/v1/teacher/classrooms/{id}` | Classroom detail: slots, students (with `photo_url`, per-student summary), `readonly` |
| GET | `/api/v1/teacher/classrooms/{id}/sessions` | Expected sessions from `attend_table` + recorded status |
| GET | `/api/v1/teacher/sessions/{code}` | Roster + states for one session. `code` = `date#time#classroom#curriculum` (URL-encoded) |
| PUT | `/api/v1/teacher/sessions/{code}` | `{ states: { student_id: state } }`, all students required. Writes `Attends` + the five `attends_student_*` pivots through the same service as `SaveAttendEasyForm`. Returns `updated_at`. 403 if archived or not started. |
| GET | `/api/v1/teacher/stats?classroom_id=` | Same metrics as §7.4, plus `students[]` and `follow_up[]` |
| POST | `/api/v1/teacher/classrooms/{id}/students/{sid}/photo` | Multipart upload, same validation and re-encode as the student upload (§5.8). 403 unless it's my current classroom. |
| DELETE | `/api/v1/teacher/classrooms/{id}/students/{sid}/photo` | Removes the file + `photo_path`. Same scope check. |

`PUT` is idempotent (it is the attendance-sheet save), so a retry after a flaky connection is safe. RTK Query invalidates `Session`, `Today`, `Classroom` and `Stats` tags on save.

### Audit
Every teacher write (attendance save, photo upload/delete) is logged with user, classroom, session code and timestamp. The “last edited” line in the roster comes from this. **Proposed** (§10).

---

## 9. Milestones and acceptance

| # | Milestone | Done when |
|---|---|---|
| P1 | Design mockups (this folder) | All screens + states reviewed by the centre ✅ (awaiting review) |
| P2.1 | API foundation | Sanctum + login/logout/me + rate limiter + Turnstile + honeypot; PHPUnit feature tests for lockout and enumeration |
| P2.2 | SPA shell + auth | Vite app, theme, AppShell, login flow, 401/419/429 handling |
| P2.3 | Current course | `/courses/current` + Home tabs; certificate download |
| P2.4 | Archive | List + read-only detail |
| P2.5 | Attendance + stats | Shared attendance service; numbers match the admin `student_attend_list` report for the same student |
| P2.6 | Profile + photo | Migration, upload with re-encode, masked CPR |
| P2.7 | **Beta launch at `/beta`** | SPA built into `public/beta`; `/beta/{path?}` fallback route + `PORTAL_BETA_ENABLED` flag; “تجريبي” badge + link back to the old portal; old `/login` and `/dashboard` verified unchanged; RTL + a11y audit (keyboard, contrast, screen reader on charts) |
| P2.8 | Beta feedback | A beta period with real students (length set by the centre); bugs fixed; usage compared between old and new portal |
| P2.9 | Promote to default | After the centre approves: `/` and `/login` point to the new panel; `/beta` kept as a redirect; old Blade portal retired later as its own change |
| P3.1 | Teacher API | §8 endpoints + scope policy (own classrooms, current = write, archived = read) + feature tests for 403 cases |
| P3.2 | Teacher SPA | Teacher shell + today, roster (save / undo / leave-guard), classes, classroom + photo sheet, stats, profile, under `/beta/teacher` |

---

## 10. Open questions

1. **Multiple active courses:** if a student has more than one active enrollment, is only the latest “current” and the rest shown in the archive (proposed), or should they be hidden?
2. **Metric policy (§7.4):** should excused absences count against the attendance rate? Should late count as attended?
3. **Marks publication:** is there an explicit “published” flag, or is a marks record considered final when it exists? (The certificate button depends on this.)
4. **Photo moderation:** does a student-uploaded photo go live immediately, or does it need admin approval?
5. **Administration contact details** for the forgot-password dialog: office hours, location, phone?
6. **Turnstile:** is a Cloudflare dependency acceptable, or should we use only rate limiting + honeypot + a challenge after N failures?
7. ~~**URL:** `/` or `/portal`?~~ **Decided:** beta at `/beta`, and the old portal stays active (§4). Still open: how long is the beta, and who approves promoting it to the default?
8. **Add to Home Screen:** ship a web-app manifest + icons so the portal installs like an app on phones?
9. **Teacher: future sessions:** block recording until the session starts (proposed), or allow it any time?
10. **Teacher: edit window:** can a teacher edit any past session of the current course (proposed), or only within N days?
11. **Teacher: photos:** should a teacher-uploaded student photo replace the student's own upload directly, or does the admin approve it? Should students be told?
12. **Teacher: audit log:** keep a per-change log of attendance edits and photo actions (proposed)?
13. **Admin vs teacher roles:** the current Backpack redirect sends teachers to `/admin/add_attend_by_date`. Once the teacher panel is live, should teacher logins be sent to `/beta/teacher` instead?
14. **Teacher: archived courses:** the teacher archive page was removed. Archived classrooms still have a read-only view (`t-classroom.html?id=870`), but no screen links to it. Should they be reachable (e.g. a “سابقة” section in صفوفي), or are archived courses not needed for teachers?
