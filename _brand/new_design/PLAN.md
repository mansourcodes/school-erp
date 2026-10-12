# Al-Ghadeer Portal — Student & Teacher Frontend Plan

> Status: **Phase 1 (design) delivered** in this folder, student panel + teacher panel · 2026-10-12
> Supersedes the Blade/Alpine approach in `_context/feature/01-frontend-student-react.md`.
> Phase 2 is a React SPA that talks to Laravel only through a versioned JSON API.
> **Language: the frontend is Arabic only** (RTL, no language switch, no English UI strings).
> **Non-negotiables: maintainable, scalable, secure** (§9). The domain moves behind **Cloudflare** to absorb attacks before they reach the server (§10).
> **Rollout: the new panel launches as a beta at `/beta`, and the current student portal (`/login`, `/dashboard`) stays active and unchanged.** An admin switches the portal between **beta / live / maintenance / closed** in Backpack Settings (`portal.mode`, §4).
> **Decisions:** all open questions were answered on 2026-10-12, see §12 Decisions log.

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
| Profile (photo is set by teacher/admin; **students do not upload**, decision Q4) | Profile | `profile.html` |
| Read messages from teachers (no reply) | Messages (top-bar envelope + badge) | `messages.html` |
| Push notifications: attendance recorded (first save), message received | Profile → الإشعارات; lock-screen mockups | `profile.html`, `states.html` |
| Forgot/reset password → “visit the centre administration” | Login dialog + Profile note | `login.html`, `profile.html` |
| Protect login from attacks and bots | Login states + §6 | `login.html` |
| Shared error/empty/loading states | System states | `states.html` |

### Teacher requirements → screens (§8)

| Requirement | Screen | File |
|---|---|---|
| Teacher login (same login screen) | Login, “مدرّس” tab | `login.html?role=teacher` |
| Add or edit attendance records | Today + attendance roster | `t-home.html`, `t-attendance.html` |
| Read + edit the current course (active + latest + one) | My classes + classroom | `t-classes.html`, `t-classroom.html` |
| Classroom statistics | Classroom stats | `t-stats.html` |
| Upload / delete student photos (own classrooms) | Classroom → student sheet | `t-classroom.html` |
| Send a message to selected students | Messages → new message | `t-messages.html`, `t-compose.html` |
| Delete a sent message | Messages (delete + confirm) | `t-messages.html` |
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
| Drag-and-drop upload | **التقاط صورة** (`capture="environment"`, rear camera; the teacher photographs the student) + **من المعرض** buttons |

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

**Messages** (`/messages`) are not a dock tab, since the dock already has 5. They are reached from an **envelope icon with an unread badge in the top bar** (all screen sizes), a sidebar entry with a badge on desktop, and an unread strip on Home.

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
- **Forgot password** opens a dialog: “يرجى مراجعة قسم إدارة المركز شخصيًا مع إحضار البطاقة الشخصية”. There is no self-service reset, no email/SMS, and no reset endpoint. The dialog shows **generic text only** (no hours or phone), decision Q5.

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
- Arch-framed photo, **display only**. Students cannot upload or change it (Q4). The photo is set by the teacher (§8) or the admin, and if none is set the arch shows the student's initial. A note says “لتحديث الصورة راجع مدرّسك أو إدارة المركز”.
- Read-only cards: personal (name, CPR **masked** with reveal, DOB + age, degree), contact (mobiles, email, address), academic (student ID, registration date, Iqraa account).
- **Not shown to students:** `financial_*`, `health_*`, `hawza_history_details`, `student_notes`, `relationship_state`, `family_*` (admin-internal).
- **الإشعارات card:** a “فعّل الإشعارات على هذا الجهاز” button (the in-context permission request), then the granted / denied states (denied explains how to unblock in browser settings), then two toggles, **رصد الحضور** and **رسائل المدرّسين** (stored server-side per student), then the iPhone note (push requires the installed PWA, iOS 16.4+).
- Password card: “visit the administration” notice. Then appearance (system/light/dark) and logout.

### 3.7 Messages (`messages.html`)
- A **read-only inbox**: a note at the top says messages can't be replied to (ask the teacher in class or the administration).
- Cards, newest first: teacher (arch initial), subject · classroom, relative time (“اليوم 1:20 م”, “أمس”, date), and the body clamped to 3 lines with “عرض المزيد”.
- **Unread** cards are tinted with a dot and medium-weight text. **Opening a card marks it read** (`POST …/read`) and updates the top-bar badge.
- There is no reply box and no compose. A message deleted by the teacher disappears from the inbox. If a student opens it from an old notification, the app shows “لم تعد هذه الرسالة متاحة”.
- States: list / empty (“لا توجد رسائل”) / loading.

---

## 4. Phase 2 frontend architecture

### Stack
| Concern | Choice |
|---|---|
| Architecture | **Feature-Sliced Design (FSD) v2** for both panels (student + teacher), see “Architecture: Feature-Sliced Design” below |
| Build | Vite |
| UI | React 18, **TypeScript** (recommended: API types catch shape drift) |
| State | Redux Toolkit (`authSlice`, `uiSlice`) |
| Server data | **RTK Query** (single `api` slice, tag-based invalidation) |
| Styling | Tailwind CSS 4 + DaisyUI 5 via `@plugin`; `theme.css` tokens move into `@plugin "daisyui/theme"` blocks unchanged |
| Routing | React Router (data router) with role-guarded layouts |
| Icons | `lucide-react` (same names as the mockups) |
| Language | **Arabic only**: `lang="ar" dir="rtl"`, no language switch, no `en` bundle. UI strings are still kept in one file (`shared/i18n/ar.json` + `t()`) so copy is reviewed in one place, not scattered in components. Server-sent texts (API errors, push titles/bodies) are Arabic too. |
| Forms | React Hook Form + Zod (login, upload) |
| Tests | Vitest + Testing Library; MSW for API mocks (fixtures = the `GH.DEMO` / `GH.T` shapes) |
| Architecture lint | **Steiger** (official FSD linter) + ESLint import boundaries, both in CI |
| PWA | **`vite-plugin-pwa`** (Workbox): web-app manifest, service worker, install + update prompts, see “Progressive Web App (PWA)” below |

### Architecture: Feature-Sliced Design (FSD)
The SPA follows **Feature-Sliced Design v2**. Code is split by business meaning (layers → slices → segments), not by file type. The student and teacher panels share one codebase without growing into one tangled `components/` folder.

**Layers** (top → bottom). A module may import **only from layers below it**:

| Layer | Purpose in this project |
|---|---|
| `app` | Entry point: providers (Redux store, router, theme), route tree with role guards, global styles |
| `pages` | One slice per screen; composes widgets. Grouped by role: `pages/student/*`, `pages/teacher/*` |
| `widgets` | Large self-contained blocks reused across pages (app shell, roster editor, attendance calendar…) |
| `features` | User actions that change something (log in, mark attendance, upload a photo, switch theme…) |
| `entities` | Business nouns with their read API, model and display UI (course, attendance, student, classroom…) |
| `shared` | Domain-free foundation: base API, UI kit, formatting, config, i18n, styles |

**Rules**
- **Imports flow downward only** (`pages → widgets → features → entities → shared`). Slices on the same layer never import each other. When two entities must reference each other, use FSD's cross-import notation `@x` (e.g. `entities/student/@x/classroom.ts`).
- **Every slice has a public API** (`index.ts`). Other slices import `@/entities/course`, never `@/entities/course/ui/Timetable`.
- **Segments** inside a slice: `ui/` (components), `model/` (Redux slice, selectors, types), `api/` (RTK Query endpoints), `lib/` (pure helpers), `config/` (constants).
- `app` and `shared` have no slices, only segments.
- Path aliases: `@/app`, `@/pages`, `@/widgets`, `@/features`, `@/entities`, `@/shared`.
- **Enforced in CI:** `steiger ./src` (the FSD linter) plus ESLint `no-restricted-imports` for upward and cross-slice imports. A layering violation fails the build.

### Folder layout (FSD)
```
frontend/src/
├── app/
│   ├── providers/        StoreProvider, RouterProvider, ThemeProvider
│   ├── routes/           route tree (basename /beta), RequireRole('student'|'teacher'), error boundaries
│   ├── store/            configureStore: baseApi reducer + middleware, viewer, ui, roster-draft
│   ├── pwa/              registerSW (virtual:pwa-register), update-available prompt, cache purge on logout
│   └── styles/           app.css: @import tailwind, @plugin daisyui, ghadeer themes, arch + riwaq motifs
│
├── pages/
│   ├── login/                      shared login (student / teacher tab)
│   ├── student/
│   │   ├── home/                   current course
│   │   ├── attendance/
│   │   ├── stats/
│   │   ├── archive/
│   │   ├── messages/               inbox (read-only)
│   │   └── profile/
│   ├── teacher/
│   │   ├── today/
│   │   ├── messages/               sent list
│   │   ├── compose/                new message
│   │   ├── roster/                 /attendance/:code
│   │   ├── classes/
│   │   ├── classroom/
│   │   ├── stats/
│   │   └── profile/
│   └── system/                     not-found, forbidden, session-expired
│
├── widgets/
│   ├── app-shell/                  TopBar, Sidebar, Dock, BetaBadge (role-aware nav config)
│   ├── course-hero/                greeting + current course + next session
│   ├── course-details-tabs/        timetable / payments / marks tabs
│   ├── archived-course-card/
│   ├── attendance-calendar/        month grid + day bottom sheet
│   ├── attendance-log/             week-grouped list
│   ├── attendance-summary/         hero rate + KPI tiles + status stack
│   ├── today-sessions/             teacher: today's cards + pending alert
│   ├── roster-editor/              teacher: header, quick actions, tally, student cards, save bar
│   ├── classroom-students/         teacher: roster list + student sheet
│   ├── follow-up-list/             teacher: students needing follow-up
│   ├── inbox-list/                 student: message cards, unread state
│   └── sent-messages/              teacher: sent cards, read progress, recipients
│
├── features/
│   ├── auth/
│   │   ├── login/                  form, role switch, Turnstile, honeypot, lockout countdown
│   │   └── logout/
│   ├── password-help/              "visit the centre administration" dialog
│   ├── theme-switch/
│   ├── install-app/                PWA install banner (Android prompt / iOS "Add to Home Screen" sheet)
│   ├── filter-attendance/          course select, subject + status chip rails
│   ├── download-certificate/
│   ├── print-receipt/
│   ├── mark-attendance/            teacher: segmented control, all-present / all-absent, undo, save, leave guard
│   ├── manage-student-photo/       teacher: upload (rear camera) / delete with confirm sheet
│   ├── send-message/               teacher: classroom → students (select all) → text, 500 chars
│   ├── delete-message/             teacher: delete with confirm sheet
│   ├── read-message/               student: mark read on open
│   └── enable-notifications/       permission soft-prompt, push subscribe/unsubscribe, per-type toggles
│
├── entities/
│   ├── viewer/                     signed-in user: me query, role, viewerSlice
│   ├── course/                     current / archived / detail queries; CourseMeta, Timetable
│   ├── payment/                    PaymentList, ReceiptView
│   ├── marks/                      MarksList, GradeSummary
│   ├── attendance/                 status catalog (✓ ت تع ✗ غع), summarize(); StatusChip, StatusPip, StatusStack
│   ├── class-session/              teacher sessions (code = date#time#classroom#curriculum); SessionCard
│   ├── classroom/                  teacher classrooms; ClassroomCard
│   ├── message/                    inbox / sent queries, unread count; MessageCard
│   ├── student/                    StudentPhoto (arch), StudentRow
│   └── teacher/                    TeacherAvatar
│
└── shared/
    ├── api/                        baseApi (createApi, empty endpoints), baseQueryWithAuth, tag types
    ├── config/                     env, route paths (/beta, /beta/teacher), feature flags
    ├── lib/                        format (fmtDate, fmtHijri, fmtTime, money, pct), media query, haptics
    ├── i18n/                       ar.json + t()
    └── ui/                         Arch, ArchPhoto, BottomSheet, ChipRail, SegmentedTabs, KpiTile, Meter,
                                    EmptyState, ErrorState, Skeleton, Toast
```

**Mockup → FSD mapping**

| Mockup code | FSD location |
|---|---|
| `shell()` + `GH.shellConfig` | `widgets/app-shell` (nav config per role from `shared/config`) |
| `GH.ui.timetable`, `GH.ui.courseMeta` | `entities/course/ui` |
| `GH.ui.payments`, `GH.ui.receiptModal` | `entities/payment/ui` + `features/print-receipt` |
| `GH.ui.marks` | `entities/marks/ui` (+ `features/download-certificate` for the button) |
| `GH.STATES`, `GH.chip`, `GH.pip`, `GH.summarize` | `entities/attendance` (`config/`, `ui/`, `lib/`) |
| `GH.T.ui.sessionCard` | `entities/class-session/ui` |
| `GH.T.ui.photo`, `GH.ui.avatar` | `entities/student/ui`, `entities/teacher/ui` (built on `shared/ui/ArchPhoto`) |
| Roster script in `t-attendance.html` | `features/mark-attendance` (draft model + actions) inside `widgets/roster-editor` |
| Photo sheet in `t-classroom.html` | `features/manage-student-photo` inside `widgets/classroom-students` |
| Login script in `login.html` | `features/auth/login` |
| `theme` in `app.js` | `features/theme-switch` (+ `app/providers/ThemeProvider`) |
| `GH.fmt*`, `GH.money`, `GH.pct` | `shared/lib/format` |
| `theme.css` | `app/styles` (tokens) + `shared/ui` (motif components) |

### RTK Query in FSD
One empty base API lives in `shared`. Each slice **injects its own endpoints**, so data access stays next to the business code that owns it:

```ts
// shared/api/base-api.ts
export const baseApi = createApi({
  reducerPath: 'api',
  baseQuery: baseQueryWithAuth,   // credentials:'include', X-XSRF-TOKEN, 401/419/429/offline handling
  tagTypes: ['Viewer', 'Course', 'Attendance', 'Stats', 'Classroom', 'ClassSession', 'Today', 'Inbox', 'Sent'],
  endpoints: () => ({}),
});

// entities/course/api/course-api.ts (read model)
export const courseApi = baseApi.injectEndpoints({
  endpoints: b => ({
    currentCourse:   b.query<CourseDetail | null, void>({ query: () => '/student/courses/current', providesTags: ['Course'] }),
    archivedCourses: b.query<CourseSummary[], void>({ query: () => '/student/courses?status=archived', providesTags: ['Course'] }),
    course:          b.query<CourseDetail, number>({ query: id => `/student/courses/${id}`, providesTags: ['Course'] }),
  }),
});

// entities/class-session/api/class-session-api.ts
export const classSessionApi = baseApi.injectEndpoints({
  endpoints: b => ({
    session: b.query<SessionRoster, string>({ query: code => `/teacher/sessions/${encodeURIComponent(code)}`, providesTags: (_r, _e, code) => [{ type: 'ClassSession', id: code }] }),
  }),
});

// features/mark-attendance/api/save-attendance.ts (the write belongs to the user action)
export const saveAttendanceApi = baseApi.injectEndpoints({
  endpoints: b => ({
    saveAttendance: b.mutation<{ updated_at: string }, { code: string; states: Record<number, AttendanceState> }>({
      query: ({ code, states }) => ({ url: `/teacher/sessions/${encodeURIComponent(code)}`, method: 'PUT', body: { states } }),
      invalidatesTags: (_r, _e, { code }) => [{ type: 'ClassSession', id: code }, 'Today', 'Classroom', 'Stats'],
    }),
  }),
});
```

| Endpoint group | Owner slice |
|---|---|
| `login`, `logout` | `features/auth/login`, `features/auth/logout` |
| `me` | `entities/viewer` |
| student courses, payments, marks | `entities/course` (payments and marks come inside `CourseDetail`) |
| student attendance + stats | `entities/attendance` |
| teacher today, classrooms, sessions, stats | `entities/class-session`, `entities/classroom` |
| `saveAttendance` | `features/mark-attendance` |
| `uploadStudentPhoto`, `deleteStudentPhoto` | `features/manage-student-photo` |
| `inbox`, `unreadCount`, `sentMessages` | `entities/message` |
| `sendMessage` / `deleteMessage` / `markMessageRead` | `features/send-message` / `features/delete-message` / `features/read-message` |
| `subscribePush`, `unsubscribePush`, `notificationSettings` | `features/enable-notifications` |

- **Client state** stays small and lives in the owning slice's `model/`: `entities/viewer` (role, signed-in flag), `features/theme-switch` (theme), `features/mark-attendance` (the unsaved roster draft + undo snapshot). Reducers are registered only in `app/store`.
- **Typed hooks without breaking layering:** `RootState` / `AppDispatch` are declared once in `app/store` as global types, so `shared/lib/store` can export `useAppSelector` / `useAppDispatch` without importing from `app`.
- `baseQueryWithAuth`: on **401/419**, clear the viewer and redirect to `/beta/login?expired=1`. On **429**, show the lockout / “طلبات كثيرة” state using the `Retry-After` header. On network failure, show the offline banner.
- Do not store tokens in JS. Auth uses HttpOnly cookies (§6).
- Current course and today's sessions refetch on focus/reconnect.

### Mobile performance budget
Most students are on mobile data, so Phase 2 targets:
- **≤ 170 KB gzipped JS** on first load; routes are code-split (`React.lazy`), and the stats and profile code is only loaded when opened.
- **Fonts:** Rubik (400–700) + IBM Plex Sans Arabic (500–700), Arabic + Latin subsets only, `font-display: swap`, Rubik preloaded (it renders most of the first screen). Self-host them in Phase 2 (one less third-party connection).
- **LCP < 2.5s on a mid-range Android over 4G**; skeletons render immediately from the shell.
- RTK Query cache + `refetchOnFocus`/`refetchOnReconnect`. The last data stays visible offline with the "غير متصل" banner.
- Photos: the server re-encodes to WebP ~512px; the client shows thumbnails at 2× density at most.
- **PWA** (next section): the app shell is precached, so repeat visits open instantly even on weak mobile data.

### Progressive Web App (PWA)
About 90% of visits are on phones, so the portal ships as an **installable PWA**. Students and teachers add it to their home screen and it opens full-screen like an app, with no app store. It is built with **`vite-plugin-pwa`** (Workbox) inside the same Vite build.

**Manifest** (`/beta/manifest.webmanifest`)
| Field | Value |
|---|---|
| `name` / `short_name` | `بوابة الغدير` / `الغدير` |
| `lang` / `dir` | `ar` / `rtl` |
| `start_url` / `scope` | `/beta/` / `/beta/` (during the beta; see "Scope and the beta" below) |
| `display` | `standalone` (falls back to `minimal-ui`) |
| `theme_color` / `background_color` | `#ffffff` light status bar, matching the top bar and the `theme-color` meta; `#f5f3ec` splash (parchment) |
| `icons` | Arch mark from `logo-icon.svg`: 192 + 512 PNG, plus a **maskable** 512 with safe padding, plus `apple-touch-icon` 180 |
| `shortcuts` | Student: الدورة الحالية, سجل الحضور. Teacher: اليوم, رصد الحضور |

iOS: `apple-mobile-web-app-capable`, `apple-mobile-web-app-status-bar-style=default`, `apple-touch-icon`. The safe-area insets (§2 Layout) already handle the notch in standalone mode.

**Service worker: what is cached and what is not**
| Request | Strategy | Why |
|---|---|---|
| App shell (`index.html`, hashed JS/CSS, fonts, icons, logo) | **Precache** (Workbox manifest), versioned per build | Instant start, works on flaky data |
| Google Fonts / self-hosted fonts | CacheFirst, 1 year | Immutable |
| `GET /api/v1/*` (personal data) | **NetworkOnly, never stored in the SW cache** | Privacy: phones are often shared in a family. RTK Query's in-memory cache still shows the last data while offline, with the "غير متصل" banner. |
| `POST/PUT/DELETE /api/v1/*`, `/sanctum/*` | NetworkOnly | Auth + CSRF must always hit the server |
| Navigation to `/beta/*` | App-shell fallback (`index.html`) | Deep links work offline (they show the offline state) |
| `/admin/*`, old portal `/login`, `/dashboard` | **Excluded** (outside the SW scope) | The old portal and the admin keep working exactly as today |

**Teacher: saving attendance with a weak connection.** Classrooms may have poor Wi-Fi. If `PUT /teacher/sessions/{code}` fails because the device is offline, the roster keeps the draft and shows "سيُحفظ عند عودة الاتصال". A **Workbox Background Sync queue** then retries when the connection returns, which is safe because the PUT is idempotent (§8). The save bar shows a "بانتظار الإرسال" badge until the server confirms. This is the only write that is queued. **Decided** (Q8).

**Updates.** `registerType: 'prompt'`: when a new build is deployed, the SW downloads it in the background and the app shows a toast, "يتوفر تحديث جديد · تحديث". The page reloads only when the user taps it, so a teacher is never interrupted mid-roster. A forced update is used only when an API version change requires it.

**Install prompt** (`features/install-app`)
- **Android/Chrome:** capture `beforeinstallprompt`, then show a small dismissible banner after the second visit, or right after a successful login: "ثبّت بوابة الغدير على شاشتك الرئيسية".
- **iOS Safari:** there is no install API, so a bottom sheet shows the steps (Share → "إضافة إلى الشاشة الرئيسية").
- If the user dismisses it, it stays hidden for 30 days (`localStorage`). It is never shown inside the installed app (`display-mode: standalone`).

**Security and privacy**
- On **logout** (and on 401/419), clear the RTK Query cache, any queued attendance drafts for that user, and `caches` entries other than the precached shell.
- The SW is served from `/beta/sw.js` with `Cache-Control: no-cache`, so updates are never stuck behind an HTTP cache.
- HTTPS is required (it already is, §6).

**Scope and the beta.** During the beta, the SW scope is `/beta/`, so it can never intercept the old portal or the admin. When the panel is promoted to `/` (§11 P2.9), the manifest `scope` / `start_url` move to `/`, and the old `/beta/sw.js` unregisters itself on its next update so no stale worker remains.

**Push notifications (Web Push).** Two events notify **students**. Teachers receive none in this phase.

| Event | When | Title / body (Arabic) | Opens |
|---|---|---|---|
| Attendance recorded | **First save only** of a session (`Attends` record created). Edits never notify. | “تم رصد حضورك: حاضر ✓” / “متأخر” / “متأخر بعذر” / “غائب بعذر”; for absent: “تم تسجيلك غائبًا ✗” + “إن كان لديك عذر راجع الإدارة”. Body: subject · day date · time | `/beta/attendance?date=YYYY-MM-DD` |
| Message received | When a teacher sends a message to the student | Title: the **centre name** “جمعية الغدير لعلوم القرآن”; body: first ~80 characters of the text (Q16) | `/beta/messages#{id}` |

- **Mechanism:** standard Web Push (VAPID) → the service worker's `push` handler shows the notification (`dir: 'rtl'`, `lang: 'ar'`, icon/badge from the arch logo, `tag` per session/message so duplicates collapse) → `notificationclick` focuses or opens the URL. The push handler needs custom SW code, so vite-plugin-pwa uses the **`injectManifest`** strategy (`app/pwa/sw.ts`) instead of `generateSW`.
- **Permission:** never asked on page load. A soft prompt sheet appears **once after the first successful login** (“هل تريد تلقي الإشعارات؟” → تفعيل / لاحقًا). It can also be turned on later from **حسابي → الإشعارات**. Only “تفعيل” triggers the browser permission dialog.
- **Per-type toggles** (attendance / messages) are stored server-side, so they apply to every device of that student.
- **iOS:** Web Push works only for the installed PWA (iOS 16.4+). The profile card explains this, and the install sheet (above) leads into it.
- **Logout** deletes this device's push subscription (`DELETE /push/subscriptions`), so a shared phone stops receiving the previous student's notifications.
- A deleted message cannot recall a notification that was already delivered. Opening it shows “لم تعد هذه الرسالة متاحة”.

**Not included:** notifications to teachers or admins, offline browsing of personal data.

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
- Old portal: a dismissible banner, “جرّب بوابة الطالب الجديدة (نسخة تجريبية)”, linking to `/beta`, shown only while `portal.mode = beta`. This is the only change to the old Blade views. **No PWA install banner** on the old portal (Q8).
- **Portal mode switch (Backpack Settings, Q7).** The beta has no fixed length; an admin decides by changing one setting. Key: `portal.mode`, managed in the existing Backpack Settings screen (the project already uses `Setting::get`) and cached for 60 s. A `PortalMode` middleware applies it to the old portal routes, `/beta/*` and `/api/v1/*`. `/admin` is **never** affected.

  | `portal.mode` | Old portal (`/login`, `/dashboard`) | New panel | API |
  |---|---|---|---|
  | `beta` (default) | Active and the default | Opt-in at `/beta` with the “تجريبي” badge | Open |
  | `live` | `/`, `/login`, `/dashboard` redirect to the new panel | The default | Open |
  | `maintenance` | Shows the maintenance page | Shows the maintenance screen (“البوابة تحت الصيانة”), PWA shell still loads | `503` + `Retry-After` |
  | `closed` | Shows the closed page | Shows the closed screen (“البوابة مغلقة حاليًا”), no login form | `403 portal_closed` |

  Signed-in users are not logged out by `maintenance`; their next request sees the screen. Mockups for the maintenance and closed screens are in `states.html`. **Gate:** `live` may only be chosen once P0.2 (platform upgrade) is done (§11).

**Promotion (after the beta):** an admin sets `portal.mode = live` (Q7). Then `/` and `/login` redirect to the new panel, and later the SPA moves to `/` with `/beta` kept as a redirect for bookmarks. The old Blade portal is retired in a separate, later step.

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
8. **Student photo** (set by **teachers and admin only**; there is no student upload endpoint, Q4): migration adds nullable `photo_path` to `students`. Store on the `public` disk at `students/photos/{student_id}/{uuid}.webp` (invariant 6: no binaries in DB). Validate `image|mimes:jpeg,png,webp|max:2048|dimensions:min_width=300,min_height=300`. **Re-encode server-side** (strips EXIF/GPS and polyglot payloads; needs an image library such as Intervention Image, which is not installed yet). Delete the previous file on replace. Throttle to 5 uploads/hour.
9. **Policies**: every `/student/*` query is scoped to `auth('student')->id()`. Never accept a `student_id` from the client.
10. **Language:** the portal API and push texts are **Arabic only**, kept in `resources/lang/ar/portal.php`. The Laravel `ar` + `en` rule still applies to the admin panel and Blade views; the portal is the documented exception (see `_context/code-standards.md`).
11. **Messages** (Phase 3, teacher → students, one-way):
    - `messages`: `id`, `sender_user_id` (FK users), `class_room_id` (FK class_rooms), `body` (text, ≤ 500), timestamps, **`deleted_at`** (soft delete, project rule).
    - **Retention: 90 days** (Q15). A scheduled `messages:prune` command (daily, Laravel scheduler, so the server needs the `schedule:run` cron) soft-deletes messages older than 90 days.
    - `message_recipients`: `message_id`, `student_id`, `read_at` (nullable). Unique `(message_id, student_id)`.
    - `MessagePolicy`: create only for **my current** classrooms (`ClassRoom.teachers[].user_id = me`, course active + latest). Every recipient must be enrolled in that classroom. Delete own messages only. Students: read their own recipient rows only, and there is no create endpoint for students at all.
    - The inbox query excludes soft-deleted messages, so a teacher delete hides the message everywhere at once.
12. **Push notifications** (Web Push, VAPID):
    - A Web Push library for Laravel notifications (e.g. `laravel-notification-channels/webpush`; pick the release line that supports Laravel 8 / PHP 7.3+). VAPID keys go in `.env`.
    - `push_subscriptions` (from the package): polymorphic owner (`Student`), `endpoint`, keys, `user_agent`. One student can have several devices.
    - `student_notification_settings`: `student_id`, `attendance` (bool, default true), `messages` (bool, default true).
    - **Attendance trigger:** in the shared attendance service (§5.5), when a session's `Attends` record is **created** (first save), dispatch `SendAttendancePush` per student with that student's state. Updates to an existing record dispatch nothing. The flag is the creation itself, so an edit can never re-notify.
    - **Message trigger:** after `POST /teacher/messages` commits, dispatch `SendMessagePush` for the recipients.
    - No quiet hours: pushes are sent immediately (Q18). Every student gets their own status on the first save (Q17).
    - Both jobs run on the existing **database queue**. They skip students whose setting is off, and prune subscriptions that return 404/410.
    - ⚠ **This changes architecture invariant 5** (“only exam ZIP packaging is queued”). Update `_context/architecture.md` when this is implemented: the push jobs join the queue, and PDFs stay synchronous.

---

## 6. Login security: attacks and bots

| Layer | Measure |
|---|---|
| Edge (Cloudflare) | First line of defence before Laravel: DDoS absorption, WAF, edge rate limits on the login endpoints, bot management, and the origin is reachable only through Cloudflare (§10). The Laravel limits below still apply and use the **real client IP** from `CF-Connecting-IP` (§10.5). |
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
| GET | `/api/v1/student/messages` | Inbox (not deleted), newest first: `{ id, teacher, curriculum, classroom, body, sent_at, read_at }` |
| POST | `/api/v1/student/messages/{id}/read` | Sets `read_at` (idempotent). 404 if the message was deleted or isn't mine |
| GET | `/api/v1/student/notification-settings` · PATCH | `{ attendance: bool, messages: bool }` |
| POST · DELETE | `/api/v1/push/subscriptions` | Store / remove this device's Web Push subscription (`endpoint`, `keys`) |

`GET /me` also returns `unread_messages` for the top-bar badge, refetched on focus and after a message push arrives.

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
  "marks_published": false,      // = final_grade IS NOT NULL (no new column, Q3)
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

### 7.4 Metric definitions (decided, Q2)
- **recorded** = sessions with any record. **unrecorded** sessions are excluded from every rate.
- **attendance rate** = (present + late + late_excused) ÷ recorded.
- **punctual** = present ÷ recorded.
- **absences** = absent + absent_excused (excused still counts as absent, but is shown separately).

---

## 8. Teacher panel (same login, same SPA)

Designed in this folder (`t-*.html`, data + components in `assets/teacher.js`). In the SPA it lives in the same FSD tree: `pages/teacher/*`, the teacher widgets (`today-sessions`, `roster-editor`, `classroom-students`, `follow-up-list`), `features/mark-attendance` and `features/manage-student-photo`, and `entities/classroom` and `entities/class-session` (§4). It uses the same design system, shell, and mobile-first rules as the student panel. The phone is again the main target, because teachers record attendance **in class, one-handed**.

### Access rules (enforced by the API, reflected in the UI)
| Rule | Detail |
|---|---|
| Who | `User` with the Spatie `Teacher` role, `web` guard via Sanctum (`sanctum.guard = ['student','web']`). `/me.role` decides which shell and nav render. |
| Which classrooms | Only classrooms where `ClassRoom.teachers[].user_id = me` (the existing JSON filter in `ClassRoom::getByDate()`). A teacher never sees other teachers' classrooms. |
| Current course | Active + latest + one only. **Read + edit**: attendance records and student photos. |
| Archived courses | **Not shown to teachers** (Q14). Teacher endpoints only return classrooms of the current course. |
| Future sessions | **Today's** sessions can be recorded at any time of the day, even before they start. Sessions on **later days** open read-only (list only), Q9. |
| Edit window | Any past session of the current course can be edited (Q10). |
| Teacher login | Teachers use the **new panel only** (Q13). The Backpack `RedirectTeacherToAttendance` flow is retired: a Teacher-role user hitting `/admin` is redirected to the teacher panel, and the Backpack attendance pages stay for admins. |

### Navigation (teacher dock / sidebar)
| Tab | Label | Icon | Route (under `/beta/teacher`) |
|---|---|---|---|
| Today | اليوم | calendar-clock | `/` (+ `/attendance/:code` opened from here) |
| Classes | صفوفي | school | `/classes`, `/classes/:id` |
| Messages | الرسائل | mail | `/messages`, `/messages/new?room=` |
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
   - Notifications hint in the save bar: on the **first** save “عند الحفظ يصل لكل طالب إشعار بحالته” (toast: “تم الحفظ وأُرسل إشعار لكل طالب بحالته”); on later edits “تعديل — لن يُرسل إشعار جديد للطلاب”.
   - Read-only variant: a session on a **later day** (info banner, list only). Today's sessions are editable even before they start (Q9).
4. **صفوفي** (`t-classes.html`): one card per current classroom with number, subject, place, weekly slots, students, attendance rate, and pending count (highlighted when above 0).
5. **الصف** (`t-classroom.html?id=`): hero with classroom, subject, place, course and slots. Three tabs:
   - **الطلاب:** search, then rows with photo / “بدون صورة” marker, ID, rate (red below 80%) and absences. Tapping a row opens the **student sheet**: big arch photo, rate / late / absent, **التقاط صورة** (`capture="environment"`, the rear camera, since the teacher photographs the student), **من المعرض**, and **حذف الصورة**, which asks for confirmation in a second in-page sheet (never `confirm()`).
   - **الحصص:** pending first, then the next 2 upcoming, then recorded sessions newest-first. Each opens the roster.
   - **معلومات:** classroom and course details.
6. **إحصائيات الصفوف** (`t-stats.html`): a classroom chip rail (كل الصفوف / each classroom), then:
   - The headline rate with a meter.
   - Tiles for students, on time, late, and absent.
   - The status breakdown bar (status colours + hatch, same as the student stats).
   - **طلاب يحتاجون متابعة**: rate < 80% or ≥ 3 absences, sorted worst first and linking to the classroom.
   - Rate per classroom (in “all” view only, a single-series bar).
   - A folded table of all students.
7. **الرسائل** (`t-messages.html`):
   - A full-width “رسالة جديدة” button and a note that students can't reply.
   - Sent messages newest first: classroom · subject, “إلى n طلاب / إلى جميع الطلاب” · time, the text, and an expandable **read progress** (avatar stack, “قرأها x من n”, meter, per-student قرأها / لم تُقرأ).
   - A **delete** button opens a confirmation sheet (“ستُحذف من صناديق n طالب فورًا… الإشعارات التي وصلت لا يمكن استرجاعها”).
   - Also reachable from a classroom: “مراسلة طلاب الصف” opens the compose screen with that classroom preselected.
8. **رسالة جديدة** (`t-compose.html?room=`), a task screen (back arrow, no dock):
   - ① **الصف**: chips, only my current classrooms.
   - ② **الطلاب**: checkbox rows with photo, name and ID, “تحديد الكل / إلغاء التحديد”, and an “x / n” counter.
   - ③ **الرسالة**: textarea, 500-character limit with a counter, and a “لا يمكن للطلاب الرد” note.
   - A sticky **“إرسال إلى n / إرسال للجميع”** button, enabled with ≥ 1 student and some text. Leaving with a draft warns first.
   - On success it returns to the sent list with “تم إرسال الرسالة”.
9. **حسابي** (`t-profile.html`): name, email (login), phone, assigned classrooms, the “visit the administration” password note, theme, and logout.

### Teacher API (v1)
| Method | Path | Notes |
|---|---|---|
| GET | `/api/v1/teacher/today` | Today's sessions across my current classrooms + pending (unrecorded past) sessions |
| GET | `/api/v1/teacher/classrooms` | My classrooms **in the current course** with counts and rate |
| GET | `/api/v1/teacher/classrooms/{id}` | Classroom detail: slots, students (with `photo_url`, per-student summary), `readonly` |
| GET | `/api/v1/teacher/classrooms/{id}/sessions` | Expected sessions from `attend_table` + recorded status |
| GET | `/api/v1/teacher/sessions/{code}` | Roster + states for one session. `code` = `date#time#classroom#curriculum` (URL-encoded) |
| PUT | `/api/v1/teacher/sessions/{code}` | `{ states: { student_id: state } }`, all students required. Writes `Attends` + the five `attends_student_*` pivots through the same service as `SaveAttendEasyForm`. Returns `updated_at`. 403 if the session is on a later day (Q9). |
| GET | `/api/v1/teacher/stats?classroom_id=` | Same metrics as §7.4, plus `students[]` and `follow_up[]` |
| POST | `/api/v1/teacher/classrooms/{id}/students/{sid}/photo` | Multipart upload with the validation and re-encode rules of §5.8. 403 unless it's my current classroom. Takes effect immediately, with no approval step (Q11). |
| DELETE | `/api/v1/teacher/classrooms/{id}/students/{sid}/photo` | Removes the file + `photo_path`. Same scope check. |

| GET | `/api/v1/teacher/messages` | Sent messages (not deleted): classroom, recipients, `read_count`, per-recipient `read_at` |
| POST | `/api/v1/teacher/messages` | `{ classroom_id, student_ids[], body }`. `body` 1–500 chars. Every `student_id` must belong to that classroom, and the classroom must be one of **my current** ones (else 422/403). Creates the message + recipients, then queues the push. |
| DELETE | `/api/v1/teacher/messages/{id}` | Soft delete (own messages only). It disappears from every recipient's inbox immediately. |

`PUT` is idempotent (it is the attendance-sheet save), so a retry after a flaky connection is safe. RTK Query invalidates `Session`, `Today`, `Classroom` and `Stats` tags on save.

### Audit
No audit log of teacher actions (Q12). The roster's “last edited” line uses the attendance record's `updated_at`. Security logging of failed/locked logins (§6) is separate and stays.
---

## 9. Quality attributes: maintainable, scalable, secure

These are acceptance criteria for every milestone, not extras. A feature is not "done" if it breaks one of them.

### 9.1 Maintainable
| Practice | Rule |
|---|---|
| Architecture | **FSD** (§4) with public APIs per slice. **Steiger + ESLint boundaries fail CI** on layering violations. Backend: thin controllers → Form Requests (validation) → services (attendance, messaging) → API Resources (output). No business logic in controllers or Blade. |
| One contract | The `/api/v1` contract is written as an **OpenAPI spec** (`docs/openapi.yaml`). Frontend types are generated from it (`openapi-typescript`), so a backend change that breaks the frontend fails the type check, not production. |
| Types & style | TypeScript `strict`, no `any` in `entities`/`features`. ESLint + Prettier (front), PHP-CS-Fixer / Pint-style rules (back), enforced in CI. |
| Tests | Unit (Vitest: `shared/lib`, `entities/*/lib`, e.g. `summarize()`), component (Testing Library + MSW), **end-to-end on a phone viewport (Playwright, 390×844, RTL)** for login, current course, roster save, send message. Backend: **PHPUnit feature tests for every policy** (other teacher's classroom → 403, archived → 403, foreign student → 422) and for the attendance-service edge cases already handled in `reportStudentAttendList`. |
| Single sources of truth | Design tokens in one theme file; attendance status catalog in `entities/attendance`; Arabic copy in `ar.json` / `portal.php`. The admin report and the API share one attendance service (§5.5). |
| Decisions on paper | Architecture decisions recorded as short ADRs in `_context/` (FSD, Sanctum cookies, PWA cache policy, Cloudflare); `progress-tracker.md` updated per milestone (project rule). |
| Dependencies | Renovate/Dependabot for npm + Composer; pinned versions; `npm audit` + `composer audit` in CI. |
| Feature flags | `portal.mode` (Backpack Settings: beta / live / maintenance / closed), plus per-feature flags for messages and push, so a feature can be switched off without a deploy. |

### 9.2 Scalable
Load is small on average but **spiky**: many classes start at the same minute (e.g. 4:00 م), so many teachers save rosters together and many students open the app after the attendance push.

| Area | Plan |
|---|---|
| Stateless app | Move `SESSION_DRIVER`, `CACHE_DRIVER` and `QUEUE_CONNECTION` from `file`/`sync` (today's `.env.example`) to **Redis**, which already runs in `docker-compose`. Then any number of app containers can run behind Cloudflare. |
| Background work | A dedicated **queue worker** container (`php artisan queue:work`, supervised) for push sends and exam ZIPs. Push jobs are chunked (e.g. 100 recipients per job), so a "message to all" never blocks a request. |
| Files | Student photos and exam files move from the local `public` disk to **S3-compatible object storage (e.g. Cloudflare R2)** once there is more than one app container. The local disk is not shared between containers. |
| Static assets | `public/beta/assets/*` are content-hashed and served with `Cache-Control: immutable`, so they are cached at the Cloudflare edge and on the device (PWA precache). The origin serves almost no static traffic. |
| Database | Indexes for the new hot paths: `attends (class_room_id, date)`, `message_recipients (student_id, read_at)`, `messages (sender_user_id, created_at)`. **Pagination** on inbox, sent list and attendance history. **Eager loading** in every API Resource (no N+1). Laravel's query log is checked in tests for the main endpoints. |
| API efficiency | Composite endpoints sized for one screen (`/teacher/today`, `/student/courses/current`) instead of many small calls on mobile. `ETag` / `304` on read endpoints. RTK Query dedupes and caches on the client. |
| Capacity check | A k6 load test before promotion: the "4:00 م peak" (N teachers saving rosters + M students opening the app within one minute). Target p95 < 500 ms for roster save, no queue backlog > 1 min. |

### 9.3 Secure
Target: **OWASP ASVS Level 2** for the portal API, plus the login hardening in §6.

| Area | Measures |
|---|---|
| **Platform currency** ⚠ (approved, Q21) | The app runs **Laravel 8 / PHP 7.3–8.0**, which as far as I know no longer receive security fixes. This is the biggest security risk in the plan, and it can't be solved by Cloudflare. **Decided (Q21):** a separate upgrade track (Laravel 8 → a currently supported LTS version, PHP 8.2+, and Backpack to a matching version) **before** the beta is promoted to all students (§11 P0.2). |
| Authorization | Every endpoint goes through a Policy (student: only own records; teacher: only own current classrooms, archived = read-only). IDs from the client are never trusted for scoping. Tests cover each 403. |
| Input / output | Form Requests with explicit rules (never `$request->all()`, project rule). Message bodies are **plain text**, rendered as text (React escaping, never `dangerouslySetInnerHTML`). Uploads are re-encoded (§5.8). |
| Data minimization | API Resources whitelist fields. Health/financial/family fields never leave the server (§3.6). The CPR is masked in the UI. Push payloads carry no CPR or marks. |
| Sessions & CSRF | Sanctum HttpOnly + Secure + SameSite=Lax cookies, CSRF token, idle timeout, logout clears the push subscription and caches (§4 PWA). |
| Headers | CSP (self + fonts + Turnstile), HSTS, `X-Content-Type-Options`, `Referrer-Policy`, `Permissions-Policy` (camera only where needed), `frame-ancestors 'none'`. |
| Secrets | `.env` never committed; VAPID / Turnstile / DB secrets per environment; least-privilege DB user (no DDL in production runtime). |
| Admin panel | `/admin` stays reachable behind the **Backpack login only** (Q22), so protect it at the edge: WAF, a strict rate limit on `POST /admin/login`, strong admin passwords, and a short admin session lifetime. Teachers no longer use `/admin` (Q13). |
| Monitoring | Failed/locked login log (§6); no teacher audit log (Q12). Error tracking without PII. Alerts on spikes of 401/429/5xx. |
| Backups | Encrypted daily DB backups (the project already has a backup config). **A restore is tested** before the beta goes live and every quarter after. |
| Verification | `composer audit` / `npm audit` in CI; a short **penetration test** of `/api/v1` + login before promotion (§11 P2.9). |

---

## 10. Cloudflare: move the domain behind Cloudflare

Goal: attacks (DDoS, bots, credential stuffing, scanners) are absorbed at Cloudflare's edge, and the origin server is not reachable directly. It protects **both** the old portal and the new `/beta` panel, so it can be done **now**, before any SPA work (§11 P0.1).

### 10.1 Migration (low risk, reversible)
1. **Inventory DNS** at the current provider: A/AAAA for the app, MX/SPF/DKIM/DMARC for email, any other subdomains.
2. **Add the site in Cloudflare**, let it import the records, and check every record against the inventory.
3. **Proxy only web records** (orange cloud): the app host(s). **Mail records stay "DNS only"** (grey cloud).
4. Lower the TTLs a day ahead, then **switch the nameservers at the registrar** to Cloudflare's. Propagation takes hours. Keep the old DNS zone untouched as the rollback path.
5. Verify: the site loads over HTTPS via Cloudflare, `/admin` works, email still sends and arrives, Web Push still delivers (it goes browser ↔ push service, not through Cloudflare).

### 10.2 TLS and the origin
- SSL/TLS mode **Full (strict)** with a **Cloudflare Origin Certificate** on the server. "Always Use HTTPS", minimum TLS 1.2, HSTS (after confirming everything is HTTPS).
- **Lock the origin:** the server firewall accepts HTTP(S) **only from Cloudflare's IP ranges**, or better, use a **Cloudflare Tunnel** (`cloudflared`), so the server has no open inbound web ports at all. Otherwise attackers can bypass Cloudflare by hitting the server IP directly.

### 10.3 Protection rules
| Feature | Configuration |
|---|---|
| DDoS protection | On by default (L3/4 + HTTP). |
| WAF managed rules | Cloudflare managed ruleset (+ OWASP core ruleset where the plan allows), in **log mode for a week**, then block. |
| Rate limiting rules (edge) | `POST /login` and `POST /api/v1/auth/login`: e.g. 10 / minute per IP → challenge, then block. `/api/v1/*`: a generous per-IP ceiling. These sit **in front of** Laravel's own limiter (§6), not instead of it. |
| Bots | Bot Fight Mode (or Super Bot Fight Mode on a paid plan). Turnstile (§6) is already a Cloudflare product, so it fits naturally. |
| Admin | No Cloudflare Access (Q22). An edge rate limit on `POST /admin/login` plus the WAF; scanner paths blocked. |
| Geo / scanners | Optional: challenge traffic from countries the centre doesn't serve; block common scanner paths (`/.env`, `/wp-admin`, `/vendor/`). |
| "Under Attack" mode | Documented runbook: who turns it on, and when (sustained L7 attack). Note that it adds an interstitial, so it is a short-term measure. |

### 10.4 Caching rules (do not break the app)
| Path | Rule |
|---|---|
| `/beta/assets/*`, fonts, icons | Cache everything, long TTL (hashed file names) |
| `/beta/sw.js`, `/beta/manifest.webmanifest`, `/beta/index.html` | **Bypass cache** (no-cache), so PWA updates are never stuck |
| `/api/*`, `/sanctum/*`, `/login`, `/logout`, `/dashboard`, `/admin/*` | **Bypass cache**, since it is personal or session data |

### 10.5 Laravel changes behind Cloudflare (required)
- **Real client IP:** trust Cloudflare's proxy ranges in `TrustProxies` and read the visitor IP from `CF-Connecting-IP`. **Without this, every request looks like it comes from Cloudflare**, and the per-IP login rate limiter (§6) would lock out everyone at once or no one at all.
- Logs and the audit trail record the real client IP.
- The Turnstile server-side check sends the real IP as `remoteip`.
- Secure cookies and HSTS assume HTTPS at the edge **and** at the origin (Full strict).

### 10.6 Plan & ownership
- **Cloudflare plan: Free** (Q19). It covers DNS, proxy, DDoS, the free managed ruleset and a limited number of rate-limiting rules. Use those rules on the login endpoints (`/login`, `/api/v1/auth/login`, `/admin/login`), and rely on Laravel's limiter + Turnstile for the rest. Revisit Pro if attacks show up in the analytics.
- **Owner: the developer** for now (Q19). Turn on **2FA** on the registrar and Cloudflare accounts, store recovery codes safely, and add a centre account as a second Super Administrator so access doesn't depend on one person.
- **Origin: Hostinger KVM 2 VPS, Ubuntu + Webmin** (Q20). Use a **Cloudflare Tunnel** (`cloudflared` as a systemd service), or UFW allowing 80/443 only from Cloudflare's IP ranges. ⚠ **Webmin** (port 10000) is a full root panel: never expose it publicly. Allow it only through an SSH tunnel or from a fixed admin IP, keep it updated, and use 2FA. SSH: keys only, no root password login, fail2ban. Redis, the queue worker (systemd/supervisor) and the scheduler cron run on the same VPS. Photos stay on the local disk while there is one server, and move to R2 if a second server is added (§9.2).

---

## 11. Milestones and acceptance

| # | Milestone | Done when |
|---|---|---|
| P0.1 | **Cloudflare** (can start now) | DNS moved, web records proxied, mail verified, Full (strict) + origin locked to Cloudflare / Tunnel, WAF in log → block, edge rate limits on login, Access on `/admin`, `TrustProxies` + `CF-Connecting-IP` deployed (§10) |
| P0.2 | **Platform upgrade** (parallel track) | Laravel + PHP on currently supported versions, Backpack upgraded to match, full regression of admin + reports + old portal (§9.3) |
| P1 | Design mockups (this folder) | All screens + states reviewed by the centre ✅ (awaiting review) |
| P2.1 | API foundation | Sanctum + login/logout/me + rate limiter + Turnstile + honeypot; PHPUnit feature tests for lockout and enumeration |
| P2.2 | SPA shell + auth | Vite app with the **FSD skeleton** (layers, aliases, Steiger + ESLint boundaries in CI), `widgets/app-shell`, theme, `features/auth/login`, 401/419/429 handling |
| P2.3 | Current course | `/courses/current` + Home tabs; certificate download |
| P2.4 | Archive | List + read-only detail |
| P2.5 | Attendance + stats | Shared attendance service; numbers match the admin `student_attend_list` report for the same student |
| P2.6 | Profile | Display-only photo, masked CPR, notification settings card (photo upload belongs to the teacher panel, P3.2) |
| P2.6b | **PWA** | Manifest + icons (incl. maskable), service worker precache, update prompt, install banner (Android + iOS sheet), cache purge on logout, teacher offline save queue; installable in Chrome DevTools → Application, verified on Android + iOS |
| P2.7 | **Beta launch at `/beta`** | SPA built into `public/beta`; `/beta/{path?}` fallback route + `portal.mode` setting and `PortalMode` middleware; “تجريبي” badge + link back to the old portal; old `/login` and `/dashboard` verified unchanged; RTL + a11y audit (keyboard, contrast, screen reader on charts) |
| P2.8 | Beta feedback | A beta period with real students (length set by the centre); bugs fixed; usage compared between old and new portal |
| P2.9 | Promote to default | After the centre approves: `/` and `/login` point to the new panel; `/beta` kept as a redirect; old Blade portal retired later as its own change. **Gate:** P0.1 + P0.2 done, pen test passed, restore tested, k6 peak test passed (§9) |
| P3.1 | Teacher API | §8 endpoints + scope policy (own classrooms, current = write, archived = read) + feature tests for 403 cases |
| P3.2 | Teacher SPA (incl. `photo_path` migration + upload/re-encode) | Teacher shell + today, roster (save / undo / leave-guard), classes, classroom + photo sheet, stats, profile, under `/beta/teacher` |
| P3.3 | **Messages** | Tables + teacher send/list/delete + student inbox/read/unread badge; scope tests (other teacher's classroom, archived classroom, foreign student → 403/422) |
| P3.4 | **Push notifications** | VAPID keys, push-subscription endpoints, SW `push`/`notificationclick` (injectManifest), soft prompt + profile toggles, queued sends on first attendance save and on message send, unsubscribe on logout; tested on Android Chrome + installed iOS PWA |

---

## 12. Decisions log (answered 2026-10-12)

| # | Question | Decision |
|---|---|---|
| Q1 | Several active enrollments | **Latest active course = current**; the others appear in the archive |
| Q2 | Attendance rate | **(present + late + late-excused) ÷ recorded**; excused absences count as absent but are shown separately |
| Q3 | When is the certificate available? | **When `final_grade` is set**; no new column (`marks_published = final_grade IS NOT NULL`) |
| Q4 | Student photo upload | **Students cannot upload.** The photo is set by the teacher/admin. ⚠ This changes the original requirement “profile (upload student image)” |
| Q5 | Forgot-password dialog | **Generic text only**, no hours or phone |
| Q6 | Bot check | **Cloudflare Turnstile** on login |
| Q7 | Beta length / promotion | **No fixed length.** Backpack Settings `portal.mode` = beta / live / maintenance / closed, switched by an admin (§4) |
| Q8 | PWA extras | **Teacher offline save queue: yes.** Install banner on the old portal: no |
| Q9 | Recording sessions that haven't started | **Allowed for the same day**; later days are read-only |
| Q10 | Teacher edit window | **Any past session** of the current course |
| Q11 | Teacher photo upload/delete | **Applies immediately**, no approval |
| Q12 | Audit log of teacher actions | **No** (the “last edited” line uses `updated_at`) |
| Q13 | Where teachers log in | **New panel only**; the Backpack teacher redirect is retired. ⚠ Needs the teacher panel (Phase 3) live first |
| Q14 | Teacher view of archived courses | **Not needed**. ⚠ This removes the original requirement “teacher can read archived courses” |
| Q15 | Message retention | **90 days**, then pruned automatically |
| Q16 | Message notification text | **Centre name** as the title + the first ~80 characters |
| Q17 | Who gets attendance pushes | **Every student, with their own status** (first save only) |
| Q18 | Quiet hours | **None**; sent immediately |
| Q19 | Cloudflare plan & owner | **Free plan**, owned by the **developer** for now (2FA + a second admin recommended) |
| Q20 | Origin hosting | **Hostinger KVM 2 VPS, Ubuntu + Webmin** (lock Webmin down, §10.6) |
| Q21 | Platform upgrade | **Yes, before `portal.mode = live`** (P0.2 gates going live) |
| Q22 | Cloudflare Access on `/admin` | **No**; Backpack login only, protected by the WAF + rate limits |

### Still open
- None from the original list. The three ⚠ items above (Q4, Q13, Q14) change earlier requirements, so confirm them with the centre before Phase 3.
