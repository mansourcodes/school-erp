# Feature Plan: Student Frontend — React PWA

## Overview

Replace the current Blade/Bootstrap 5 student portal with a mobile-first React
Progressive Web App (PWA) that lets students view their profile, marks,
attendance calendar, payments, and certificate table. Styled with **DaisyUI
(fantasy theme)** on top of Tailwind CSS — no Bootstrap in the React app.
The Laravel backend remains unchanged except for new JSON API endpoints added
under `/api/student/*`.

---

## Design Decisions (from UX brief)

| Dimension       | Decision                                                    |
| --------------- | ----------------------------------------------------------- |
| Device target   | Mobile-first (phone); responsive up to tablet/desktop       |
| Visual style    | Rich & branded — DaisyUI **fantasy** theme, gradient profile header |
| Component lib   | DaisyUI v4 (fantasy theme) on Tailwind CSS v3               |
| Direction       | RTL Arabic-first (same as existing portal)                  |
| Navigation      | Fixed bottom tab bar (5 tabs)                               |
| Offline         | PWA install-only; no service-worker caching                 |
| Sections        | Profile, Marks & grades, Payments, Certificates, Attendance |

### Color Palette

DaisyUI **fantasy** theme is applied globally via `data-theme="fantasy"` on
`<html>`. Its built-in tokens are used directly — no custom palette needed.

| DaisyUI token   | fantasy value | Used for                              |
| --------------- | ------------- | ------------------------------------- |
| `primary`       | `#6e0b75`     | Active tab, primary buttons           |
| `secondary`     | `#007ebd`     | Secondary actions, links              |
| `accent`        | `#f6d860`     | Highlight badges, marks callouts      |
| `base-100`      | `#ffffff`     | Card background                       |
| `base-200`      | `#f9f0ff`     | Page background                       |
| `base-content`  | `#1a1a2e`     | Body text                             |
| `neutral`       | `#1a1a1a`     | BottomNav bar background              |

Profile header gradient uses `primary` → `secondary` (`#6e0b75 → #007ebd`)
to give the rich branded feel.

### Typography

- **UI / Latin**: Nunito (existing Google Fonts CDN) — kept for consistency
- **Arabic**: `'Noto Naskh Arabic', serif` — add to Google Fonts link
- Base size: `16px`; Arabic leading: `1.8`

---

## Backend Changes

### 1. New API Controller

**File**: `app/Http/Controllers/Api/StudentPortalController.php`

Endpoints (all guarded by `auth:student`):

| Method | Path                                   | Returns                                             |
| ------ | -------------------------------------- | --------------------------------------------------- |
| `GET`  | `/api/student/profile`                 | Student model fields + `student_id`                 |
| `GET`  | `/api/student/classrooms`              | Classrooms with nested `course`, ordered newest-first |
| `GET`  | `/api/student/classrooms/{id}/marks`   | `StudentMarks.brief_marks` for this classroom's course |
| `GET`  | `/api/student/classrooms/{id}/payments`| Payments filtered to this classroom's course        |
| `GET`  | `/api/student/classrooms/{id}/attendance`| `Attends` records for this student + classroom     |
| `POST` | `/api/student/auth/login`              | Issue session cookie (CPR + password)               |
| `POST` | `/api/student/auth/logout`             | Invalidate session                                  |

### 2. Route Registration

Add a new route file `routes/student-api.php` registered in
`RouteServiceProvider` (or inline in `routes/api.php`):

```php
Route::prefix('student')->group(function () {
    Route::post('auth/login',  [StudentPortalController::class, 'login']);
    Route::post('auth/logout', [StudentPortalController::class, 'logout']);

    Route::middleware('auth:student')->group(function () {
        Route::get('profile',                      [StudentPortalController::class, 'profile']);
        Route::get('classrooms',                   [StudentPortalController::class, 'classrooms']);
        Route::get('classrooms/{id}/marks',        [StudentPortalController::class, 'marks']);
        Route::get('classrooms/{id}/payments',     [StudentPortalController::class, 'payments']);
        Route::get('classrooms/{id}/attendance',   [StudentPortalController::class, 'attendance']);
    });
});
```

### 3. CSRF / Session Auth for the SPA

Use **Laravel Sanctum SPA mode** (cookie-based, no tokens needed):

1. Add `sanctum` guard to `config/auth.php` student guard definition (or use
   `EnsureFrontendRequestsAreStateful` middleware).
2. The React app calls `GET /sanctum/csrf-cookie` once on load, then all
   subsequent `axios` requests include `withCredentials: true`.
3. No changes to the existing `auth:student` guard logic — Sanctum wraps it.

### 4. API Resource Classes

Create lean resource classes to shape JSON output:

- `StudentResource` — profile fields only (exclude password, remember_token)
- `ClassroomResource` — classroom + nested course name, hijri year, semester
- `MarksResource` — wraps `brief_marks` accessor output
- `PaymentResource` — amount, type, source, created_at
- `AttendResource` — date, curriculum name, status (present/absent)

---

## Frontend Architecture

### Stack

| Concern           | Library / Tool                         | Why                                          |
| ----------------- | -------------------------------------- | -------------------------------------------- |
| Framework         | React 18 + TypeScript                  | Component model; TS catches shape mismatches |
| Build             | Vite 5 (Laravel Vite plugin)           | Fast HMR; integrates with existing `vite.config.js` |
| Routing           | React Router DOM v6                    | File-like nested routes, history API         |
| Data fetching     | TanStack Query v5                      | Caching, stale-while-revalidate, loading states |
| HTTP client       | Axios (already in `package.json`)      | Interceptors for CSRF; `withCredentials`     |
| Styling           | Tailwind CSS v3 + **DaisyUI v4** (fantasy theme) | Pre-built RTL-compatible components; replaces Bootstrap |
| Icons             | Lucide React                           | Consistent SVG icon set; tree-shakeable      |
| PWA               | `vite-plugin-pwa`                      | Generates manifest + install prompt          |
| Date display      | `date-fns`                             | Gregorian date formatting for attendance calendar |

### Directory Structure

```
resources/js/student-app/
├── main.tsx                  # React root mount
├── App.tsx                   # Router + QueryClientProvider + auth gate
├── router.tsx                # Route definitions
│
├── lib/
│   ├── axios.ts              # Axios instance (baseURL, CSRF, withCredentials)
│   ├── api.ts                # Typed API functions (one per endpoint)
│   └── types.ts              # TypeScript interfaces matching API resources
│
├── hooks/
│   ├── useAuth.ts            # Login, logout, current student
│   ├── useClassrooms.ts      # Classrooms list query
│   ├── useMarks.ts           # Marks for a classroom
│   ├── usePayments.ts        # Payments for a classroom
│   └── useAttendance.ts      # Attendance for a classroom
│
├── components/
│   ├── layout/
│   │   ├── AppShell.tsx      # Bottom nav + page outlet
│   │   └── BottomNav.tsx     # 5-tab fixed bottom bar
│   ├── ui/
│   │   ├── Spinner.tsx       # DaisyUI `loading loading-spinner`
│   │   └── EmptyState.tsx    # Illustration + Arabic label
│   ├── ProfileHeader.tsx     # Gradient header with avatar, name, ID
│   ├── CourseCard.tsx        # Collapsible course summary card
│   ├── MarksTable.tsx        # Marks breakdown table (mid/final/project etc)
│   ├── AttendanceCalendar.tsx# Monthly grid, green=present red=absent
│   └── PaymentRow.tsx        # Single payment line item
│
└── pages/
    ├── Login.tsx             # CPR + password, branded splash
    ├── Home.tsx              # Profile card + quick stats (current courses)
    ├── Courses.tsx           # List of all classrooms
    ├── CourseDetail.tsx      # Marks table + certificate for one course
    ├── Payments.tsx          # All payments grouped by course
    ├── Attendance.tsx        # Course picker + attendance calendar
    └── Profile.tsx           # Full student profile fields
```

### Build Integration

`vite.config.js` — add a second input entry for the student app:

```js
input: {
  app:         'resources/js/app.js',        // existing admin/other
  studentApp:  'resources/js/student-app/main.tsx',
}
```

Compiled assets output to `public/build/` via Laravel Vite manifest.

### Laravel Blade Shell

The existing `resources/views/frontend-student/layouts/app.blade.php` becomes
a minimal SPA shell:

```html
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>{{ config('app.name') }}</title>
  @vite(['resources/js/student-app/main.tsx'])
</head>
<body>
  <div id="student-root"></div>
</body>
</html>
```

The existing web routes (`/`, `/dashboard`, `/login`) continue to serve this
shell. React Router handles all in-app navigation client-side.

### PWA Manifest (`vite-plugin-pwa` config)

```js
{
  name: 'بوابة الطالب',
  short_name: 'الطالب',
  theme_color: '#1b3a5c',
  background_color: '#f5f7fa',
  display: 'standalone',
  orientation: 'portrait',
  icons: [ 192px, 512px PNGs derived from provided school logo ],
  workbox: { globPatterns: [] }   // no offline caching
}
```

**Logo**: Already exists at `public/img/logo.svg`. Reference directly via
`/img/logo.svg` in the React app (no copy needed). Use in:
- Login page branded header
- PWA icon generation (resize to 192×192 and 512×512 PNGs at build time)
- Home profile header (small 32px white version)

---

## Screen Inventory

### 1. Login (`/login`)

- Full-screen branded splash: school logo (`/img/logo.svg`) + gradient
  background using fantasy `primary → secondary`
- Arabic heading: "بوابة الطالب"
- DaisyUI `input input-bordered` for CPR + password (RTL)
- DaisyUI `btn btn-primary btn-block` for "دخول"
- DaisyUI `alert alert-error` for inline error message

### 2. Home (`/`)

- **Profile header**: `hero` component with gradient bg (fantasy primary →
  secondary), circle avatar via `avatar placeholder`, student name, `badge
  badge-accent` for student ID
- **Quick stats row**: 3 DaisyUI `stat` cards inside a `stats stats-horizontal`
  — current courses count, total payments, attendance %
- **Current courses section**: `collapse collapse-arrow` DaisyUI cards list

### 3. Courses (`/courses`)

- List of all classrooms newest-first; `divider` between current and past
- Each `CourseCard` uses `collapse collapse-arrow card bg-base-100 shadow`
- "لا توجد مواد مسجلة" empty state (`EmptyState` component)

### 4. Course Detail (`/courses/:classroomId`)

- Course long name as `navbar` page title
- DaisyUI `tabs tabs-boxed` switcher: Marks | Certificate
- **Marks tab**: `table table-zebra` — rows per curriculum, columns per mark
  type (midexam, finalexam, project, practice, memorise, class, attend)
- **Certificate tab**: same mark data in `table` + `btn btn-outline btn-primary`
  "تحميل الشهادة PDF" that opens the existing PDF route in a new tab

### 5. Payments (`/payments`)

- Grouped by course using `collapse collapse-arrow`
- Each row in a `table`: amount, `badge` for type (color-coded), date, source
- Total per course shown in `collapse-title`
- "لا توجد مدفوعات" empty state

### 6. Attendance (`/attendance`)

- Course picker dropdown (defaults to most recent classroom)
- Monthly calendar grid:
  - Green cell = present, Red cell = absent, Grey = no session
  - **Gregorian dates only** in cells; Gregorian month name in header
  - No Hijri display needed
- Summary bar: present count / total sessions, percentage

### 7. Profile (`/profile`)

- `card bg-base-100 shadow` with read-only student fields displayed as
  `kbd` / label pairs: CPR, DOB, age, mobile, mobile2, address,
  financial state, hawza history
- `btn btn-error btn-outline btn-block` "تسجيل الخروج" at bottom

### Bottom Navigation

| Tab        | Icon (Lucide)  | Route         |
| ---------- | -------------- | ------------- |
| الرئيسية   | `Home`         | `/`           |
| المواد     | `BookOpen`     | `/courses`    |
| المدفوعات  | `CreditCard`   | `/payments`   |
| الحضور     | `CalendarDays` | `/attendance` |
| الملف      | `User`         | `/profile`    |

---

## Implementation Phases

### Phase 1 — Backend API (no frontend yet)

1. Add `StudentPortalController` with all 7 endpoints
2. Register routes in `routes/api.php`
3. Configure Sanctum SPA stateful domains
4. Add `StudentResource`, `ClassroomResource`, `MarksResource`,
   `PaymentResource`, `AttendResource`
5. Verify all endpoints return correct JSON with Postman / `php artisan tinker`

### Phase 2 — React scaffold + Auth

1. Install dependencies: `react`, `react-dom`, `react-router-dom`,
   `@tanstack/react-query`, `lucide-react`, `tailwindcss`, `daisyui`,
   `vite-plugin-pwa`, `date-fns`
2. Configure Vite, Tailwind + DaisyUI plugin (fantasy theme, RTL via `dir="rtl"` on `<html>`)
3. `App.tsx` with `QueryClientProvider`, `RouterProvider`
4. Login page + `useAuth` hook (CSRF fetch → POST login → redirect)
5. Auth-guarded route wrapper (redirect to `/login` if no session)
6. `AppShell` with `BottomNav`

### Phase 3 — Core screens

1. Home page (profile header + quick stats + current courses)
2. Courses list + CourseDetail (marks + certificate tabs)
3. Payments page

### Phase 4 — Attendance calendar

1. `useAttendance` hook
2. `AttendanceCalendar` component (monthly grid, Hijri month labels)
3. Course picker

### Phase 5 — PWA + polish

1. `vite-plugin-pwa` config (manifest, icons)
2. Install prompt banner
3. Pull-to-refresh on Home, Courses, Payments
4. Loading skeletons for all data queries
5. RTL audit, Arabic font loading
6. Viewport meta, safe-area insets for notched phones

---

## What Stays Unchanged

- All admin/Backpack CRUD — untouched
- `auth:student` guard and `Student` model — no changes
- PDF report generation (mPDF) — report routes still work for admin use
- Existing web routes `/`, `/login`, `/dashboard` — now serve the React shell
- `resources/lang/ar/localize.php` — strings referenced from API JSON responses

---

## Resolved Decisions

| Question                        | Decision                                                         |
| ------------------------------- | ---------------------------------------------------------------- |
| School logo                     | `public/img/logo.svg` — reference as `/img/logo.svg` in React   |
| Attendance calendar dates       | Gregorian only (no Hijri display)                                |
| Certificate tab behavior        | Show table + "تحميل الشهادة PDF" button linking to existing PDF route |
| Language support                | Arabic-only (RTL throughout, no language toggle)                 |

| Component library              | DaisyUI v4 (fantasy theme) — replaces Bootstrap entirely         |

> `date-fns-jalali` and `tailwindcss-rtl` are not needed — dropped from dependencies.
> RTL handled by `dir="rtl"` on `<html>` + Tailwind's built-in `rtl:` variant.
