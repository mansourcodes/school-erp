# Feature Plan: Student Frontend — Laravel Blade

## Overview

Build a mobile-first student portal using **Laravel Blade** templates styled
with **DaisyUI (fantasy theme)** on Tailwind CSS. Students can view their
profile, marks, attendance calendar, payments, and certificates. All pages are
server-rendered — no React, no SPA, no TypeScript. Alpine.js handles lightweight
interactivity (dropdowns, tabs, collapsibles). No Bootstrap in the student portal.

---

## Design Decisions (from UX brief)

| Dimension       | Decision                                                    |
| --------------- | ----------------------------------------------------------- |
| Device target   | Mobile-first (phone); responsive up to tablet/desktop       |
| Visual style    | Rich & branded — DaisyUI **fantasy** theme, gradient profile header |
| Component lib   | DaisyUI v4 (fantasy theme) on Tailwind CSS v3               |
| Direction       | RTL Arabic-first (same as existing portal)                  |
| Navigation      | Fixed bottom tab bar (5 tabs)                               |
| Rendering       | Server-rendered Blade; Alpine.js for UI interactivity only  |
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

### 1. Student Portal Controller

**File**: `app/Http/Controllers/Frontend/Student/StudentPortalController.php`

All methods guarded by `auth:student` middleware (except login/logout).
Each method fetches data from the database and returns a Blade view.

| Method | Route                                   | Returns                                          |
| ------ | --------------------------------------- | ------------------------------------------------ |
| `GET`  | `/student/login`                        | Login form view                                  |
| `POST` | `/student/login`                        | Authenticate + redirect to home                  |
| `POST` | `/student/logout`                       | Invalidate session + redirect to login           |
| `GET`  | `/student`                              | Home view (profile + quick stats)                |
| `GET`  | `/student/courses`                      | Courses list view                                |
| `GET`  | `/student/courses/{id}`                 | Course detail view (marks + certificate tab)     |
| `GET`  | `/student/payments`                     | Payments grouped by course                       |
| `GET`  | `/student/attendance`                   | Attendance calendar (course picker)              |
| `GET`  | `/student/profile`                      | Full profile view + logout button                |

### 2. Route Registration

Add to `routes/web.php` (or a separate `routes/student.php` included from web.php):

```php
Route::prefix('student')->name('student.')->group(function () {
    Route::get('login',  [StudentPortalController::class, 'loginForm'])->name('login');
    Route::post('login', [StudentPortalController::class, 'login']);
    Route::post('logout',[StudentPortalController::class, 'logout'])->name('logout');

    Route::middleware('auth:student')->group(function () {
        Route::get('/',              [StudentPortalController::class, 'home'])->name('home');
        Route::get('courses',        [StudentPortalController::class, 'courses'])->name('courses');
        Route::get('courses/{id}',   [StudentPortalController::class, 'courseDetail'])->name('courses.show');
        Route::get('payments',       [StudentPortalController::class, 'payments'])->name('payments');
        Route::get('attendance',     [StudentPortalController::class, 'attendance'])->name('attendance');
        Route::get('profile',        [StudentPortalController::class, 'profile'])->name('profile');
    });
});
```

### 3. Auth Guard

The existing `auth:student` guard is used as-is. No Sanctum SPA mode needed —
standard session-cookie auth with Blade forms and CSRF tokens via `@csrf`.

---

## Frontend Architecture

### Stack

| Concern      | Tool                                       | Why                                        |
| ------------ | ------------------------------------------ | ------------------------------------------ |
| Templates    | Laravel Blade                              | Server-rendered; no build step for views   |
| Styling      | Tailwind CSS v3 + **DaisyUI v4** (fantasy) | Pre-built RTL-compatible components        |
| JS           | Alpine.js v3                               | Tabs, collapsibles, dropdown — no full SPA |
| Build        | Vite (existing `vite.config.js`)           | Compiles Tailwind + any JS                 |
| Icons        | Lucide (via CDN or inline SVG)             | Consistent SVG icons                       |
| Date display | PHP `Carbon` / Blade                       | Gregorian date formatting server-side      |

### Directory Structure

```
resources/views/frontend-student/
├── layouts/
│   └── app.blade.php          # Shell: <html dir="rtl">, bottom nav, @yield('content')
│
├── auth/
│   └── login.blade.php        # CPR + password login form
│
├── home.blade.php             # Profile header + quick stats + current courses
├── courses/
│   ├── index.blade.php        # All classrooms list
│   └── show.blade.php         # Marks + certificate tabs for one course
├── payments.blade.php         # Payments grouped by course
├── attendance.blade.php       # Course picker + monthly calendar grid
└── profile.blade.php          # Read-only profile fields + logout button

resources/views/frontend-student/components/
├── bottom-nav.blade.php       # 5-tab fixed bottom bar
├── profile-header.blade.php   # Gradient hero with avatar, name, student ID badge
├── course-card.blade.php      # Collapsible course summary (Alpine.js)
├── marks-table.blade.php      # Marks breakdown table (mid/final/project etc)
├── attendance-calendar.blade.php  # Monthly grid, green=present red=absent
├── payment-row.blade.php      # Single payment line item
├── spinner.blade.php          # DaisyUI loading spinner (for Alpine x-show)
└── empty-state.blade.php      # Illustration + Arabic label
```

### Build Integration

No new Vite entry point needed. The existing `resources/js/app.js` (or a new
`resources/js/student.js`) imports Tailwind and Alpine:

```js
// resources/js/student.js
import Alpine from 'alpinejs'
window.Alpine = Alpine
Alpine.start()
```

`vite.config.js` — add student JS entry if separating from admin bundle:

```js
input: {
  app:     'resources/js/app.js',
  student: 'resources/js/student.js',
}
```

### Layout Shell (`layouts/app.blade.php`)

```html
<!DOCTYPE html>
<html lang="ar" dir="rtl" data-theme="fantasy">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>{{ config('app.name') }} — بوابة الطالب</title>
  <link href="https://fonts.googleapis.com/css2?family=Nunito&family=Noto+Naskh+Arabic&display=swap" rel="stylesheet">
  @vite(['resources/css/app.css', 'resources/js/student.js'])
</head>
<body class="bg-base-200 font-arabic">
  <main class="pb-20 min-h-screen">
    @yield('content')
  </main>
  @include('frontend-student.components.bottom-nav')
</body>
</html>
```

---

## Screen Inventory

### 1. Login (`/student/login`)

- Full-screen branded splash: school logo (`/img/logo.svg`) + gradient background
- Arabic heading: "بوابة الطالب"
- `@csrf` hidden input
- DaisyUI `input input-bordered` for CPR + password (RTL)
- DaisyUI `btn btn-primary btn-block` for "دخول"
- `@if($errors->any())` → DaisyUI `alert alert-error`

### 2. Home (`/student`)

- **Profile header**: `hero` with gradient bg (fantasy primary → secondary),
  circle avatar via `avatar placeholder`, student name, `badge badge-accent`
  for student ID
- **Quick stats row**: 3 DaisyUI `stat` cards — current courses count,
  total payments, attendance %
- **Current courses**: list of `course-card` components (Alpine `x-data` for
  open/close)

### 3. Courses (`/student/courses`)

- All classrooms newest-first; `divider` between current and past
- Each `course-card` uses `collapse collapse-arrow card bg-base-100 shadow`
- "لا توجد مواد مسجلة" empty-state component

### 4. Course Detail (`/student/courses/{id}`)

- Course long name as `navbar` page title
- DaisyUI `tabs tabs-boxed` switcher powered by Alpine (`x-data="{ tab: 'marks' }"`)
- **Marks tab**: `table table-zebra` — rows per curriculum, columns per mark
  type (midexam, finalexam, project, practice, memorise, class, attend)
- **Certificate tab**: marks table + `<a>` styled as `btn btn-outline btn-primary`
  "تحميل الشهادة PDF" pointing to existing PDF route (opens in new tab)

### 5. Payments (`/student/payments`)

- Grouped by course using `collapse collapse-arrow` (Alpine)
- Each row in a `table`: amount, `badge` for type (color-coded), date, source
- Total per course shown in `collapse-title`
- "لا توجد مدفوعات" empty state

### 6. Attendance (`/student/attendance`)

- Course picker `<select>` (standard form GET with `?classroom_id=X`)
- Monthly calendar grid rendered in Blade:
  - Green cell = present, Red cell = absent, Grey = no session
  - Gregorian dates in cells; Gregorian month name in header
- Summary bar: present count / total sessions, percentage

### 7. Profile (`/student/profile`)

- `card bg-base-100 shadow` with read-only student fields as label–value pairs:
  CPR, DOB, age, mobile, mobile2, address, financial state, hawza history
- `<form method="POST" action="{{ route('student.logout') }}">` with `@csrf`
  + `btn btn-error btn-outline btn-block` "تسجيل الخروج"

### Bottom Navigation (`components/bottom-nav.blade.php`)

| Tab        | Icon (Lucide inline SVG) | Route                   | Active check                          |
| ---------- | ------------------------ | ----------------------- | ------------------------------------- |
| الرئيسية   | Home                     | `student.home`          | `request()->routeIs('student.home')`  |
| المواد     | BookOpen                 | `student.courses`       | `request()->routeIs('student.courses*')` |
| المدفوعات  | CreditCard               | `student.payments`      | `request()->routeIs('student.payments')` |
| الحضور     | CalendarDays             | `student.attendance`    | `request()->routeIs('student.attendance')` |
| الملف      | User                     | `student.profile`       | `request()->routeIs('student.profile')` |

Active tab uses `text-primary` (fantasy `#6e0b75`); inactive uses `text-base-content/50`.

---

## Implementation Phases

### Phase 1 — Layout & Auth

1. Create `layouts/app.blade.php` shell (RTL, DaisyUI fantasy theme, bottom nav)
2. Create `components/bottom-nav.blade.php` with active-state logic
3. Create `StudentPortalController` with `loginForm`, `login`, `logout`
4. Create `auth/login.blade.php`
5. Register routes in `routes/web.php`
6. Verify login → redirect to home → logout flow

### Phase 2 — Home & Profile

1. `home` controller method + `home.blade.php`
2. `profile-header` component (gradient hero)
3. Quick stats computed in controller (course count, payment total, attendance %)
4. `profile` controller method + `profile.blade.php`

### Phase 3 — Courses & Marks

1. `courses` controller method + `courses/index.blade.php`
2. `course-card` component (Alpine collapsible)
3. `courseDetail` controller method + `courses/show.blade.php`
4. `marks-table` component (DaisyUI `table table-zebra`)
5. Certificate tab with PDF link to existing route

### Phase 4 — Payments

1. `payments` controller method (group by course in PHP)
2. `payments.blade.php` with Alpine collapsible groups
3. `payment-row` component

### Phase 5 — Attendance

1. `attendance` controller method (course picker + calendar data)
2. `attendance-calendar` component (monthly grid, Carbon date helpers)
3. GET form for classroom switching

### Phase 6 — Polish

1. `empty-state` component (Arabic label, neutral illustration)
2. RTL audit across all screens
3. Mobile safe-area insets (`pb-safe`, viewport meta)
4. Arabic font loading verification
5. Active tab highlighting in bottom nav

---

## What Stays Unchanged

- All admin/Backpack CRUD — untouched
- `auth:student` guard and `Student` model — no changes
- PDF report generation (mPDF) — report routes still work for admin use
- `resources/lang/ar/localize.php` — strings referenced from Blade views

---

## Resolved Decisions

| Question                        | Decision                                                              |
| ------------------------------- | --------------------------------------------------------------------- |
| Rendering approach              | Server-rendered Blade — no React, no SPA                              |
| School logo                     | `public/img/logo.svg` — referenced via `asset('img/logo.svg')`        |
| Attendance calendar dates       | Gregorian only (no Hijri display), rendered in Blade with Carbon      |
| Certificate tab behavior        | Marks table + "تحميل الشهادة PDF" anchor to existing PDF route        |
| Language support                | Arabic-only (RTL throughout, no language toggle)                      |
| Component library               | DaisyUI v4 (fantasy theme) — no Bootstrap                             |
| JS interactivity                | Alpine.js v3 — tabs, collapsibles, dropdowns only                     |
| PWA                             | Not included — standard browser experience                            |
