# Feature 02 — Teacher Role & Attendance Access

## Summary

Add a `Teacher` role to the admin `web` guard so that a teacher can:
- Log in to `/admin`
- Be automatically redirected to `/admin/add_attend_by_date`
- See only the classrooms they are assigned to
- Record attendance for those classrooms only

Admins assign teachers to classrooms through the existing ClassRoom CRUD by
linking a `User` (with Teacher role) inside the `teachers` JSON field per
curriculum slot.

---

## Decisions (from UX review)

| Question | Decision |
|---|---|
| Teacher account creation | Admin creates a `User`, assigns `Teacher` role via Backpack PermissionManager, then assigns to classroom(s) via ClassRoom CRUD |
| Post-login destination | Redirect Teacher straight to `/admin/add_attend_by_date` — no sidebar needed |
| Source of truth for teacher↔classroom | `ClassRoom.teachers` JSON column — add `user_id` to each entry alongside existing `teacher_name` and `curriculum_id` |

---

## Current State

- `ClassRoom.teachers` JSON structure (per entry):
  ```json
  { "curriculum_id": 1, "teacher_name": "احمد" }
  ```
- `ClassRoom::getByDate()` already has a Teacher filter using the
  `classTeachers()` pivot relation (`class_room_teacher` table).
- The pivot table and relation exist but will be **replaced** by the JSON
  approach to stay consistent with the existing JSON-first pattern.

---

## Target JSON Schema

Each entry in `ClassRoom.teachers` gains a `user_id` field:

```json
[
  { "curriculum_id": 1, "teacher_name": "احمد", "user_id": 5 },
  { "curriculum_id": 2, "teacher_name": "فاطمة", "user_id": 9 }
]
```

`teacher_name` stays as a human-readable cache; `user_id` links to `users.id`.

---

## Implementation Steps

### Step 1 — Update `ClassRoom.teachers` JSON to include `user_id`

**File:** `app/Http/Controllers/Admin/ClassRoomCrudController.php`

In `setupCreateOperation()` / `setupUpdateOperation()`, update the `teachers`
repeatable field so each sub-entry has a `user_id` select alongside
`curriculum_id` and `teacher_name`:

```php
// Inside the 'teachers' repeatable field subfields array:
[
    'name'        => 'user_id',
    'label'       => trans('user.teacher'),
    'type'        => 'select2_from_ajax',
    'data_source' => url('api/teacher'),   // new API endpoint
    'attribute'   => 'name',
    'model'       => \App\Models\User::class,
    'placeholder' => trans('user.select_teacher'),
    'minimum_input_length' => 0,
    'wrapper' => ['class' => 'form-group col-md-6'],
],
```

When `user_id` is selected the `teacher_name` field should auto-populate (or
the save handler can populate it from the User record). Keep `teacher_name`
as a hidden or read-only auto-filled field to avoid breaking existing
`getCurriculumsAttribute()` logic.

**File:** `routes/api.php`

Add a Teacher users endpoint:

```php
Route::get('teacher', [\App\Http\Controllers\Api\UserApiController::class, 'teachers']);
```

**File:** `app/Http/Controllers/Api/UserApiController.php` (new)

```php
public function teachers(Request $request)
{
    $search = $request->get('q', '');
    return User::role('Teacher')
        ->where('name', 'like', '%' . $search . '%')
        ->select('id', 'name')
        ->limit(30)
        ->get();
}
```

---

### Step 2 — Update `ClassRoom::getByDate()` to filter via JSON

**File:** `app/Models/ClassRoom.php`

Replace the `classTeachers` pivot filter with a JSON LIKE filter:

```php
// Before (pivot):
$query->whereHas('classTeachers', function ($q) {
    $q->where('user_id', auth()->id());
});

// After (JSON):
$userId = auth()->id();
$query->where(function ($q) use ($userId) {
    $q->where('teachers', 'like', '%"user_id":' . $userId . ',%')
      ->orWhere('teachers', 'like', '%"user_id":' . $userId . '}%');
});
```

> The two LIKE patterns cover both mid-array and end-of-object positions in
> the JSON string, matching MySQL's stored format. No migration needed.

---

### Step 3 — Post-login redirect for Teacher role

**File:** `app/Http/Controllers/Admin/Auth/LoginController.php`
(publish/create if not already overridden from Backpack)

Override `redirectPath()` or `authenticated()`:

```php
protected function authenticated(Request $request, $user)
{
    if ($user->hasRole('Teacher')) {
        return redirect(backpack_url('add_attend_by_date'));
    }
    return redirect($this->redirectPath());
}
```

If Backpack's LoginController is not publishable, add a middleware instead
(see Step 4 alternative).

---

### Step 4 — Restrict Teacher to attendance routes only

**File:** `app/Http/Middleware/RedirectTeacherToAttendance.php` (new)

```php
public function handle($request, Closure $next)
{
    if (backpack_auth()->check() && backpack_auth()->user()->hasRole('Teacher')) {
        $allowed = [
            'add_attend_by_date',
            'attend_easy_form',
            'save_attend_easy_form',
        ];
        $segment = $request->segment(2); // /admin/{segment}
        if (!in_array($segment, $allowed)) {
            return redirect(backpack_url('add_attend_by_date'));
        }
    }
    return $next($request);
}
```

Register in `app/Http/Kernel.php` under `$routeMiddleware`:

```php
'teacher.attendance_only' => \App\Http\Middleware\RedirectTeacherToAttendance::class,
```

Apply to the Backpack admin route group in `config/backpack/base.php` or in
`routes/backpack/custom.php`:

```php
Route::middleware(['backpack.auth', 'teacher.attendance_only'])->group(function () {
    // all admin routes
});
```

---

### Step 5 — Hide sidebar for Teacher

**File:** `resources/views/vendor/backpack/base/inc/sidebar_content.blade.php`
(must be published first if not already)

Wrap all menu items:

```blade
@if(!backpack_user()->hasRole('Teacher'))
    {{-- all existing menu items --}}
@endif
```

Teacher will see a blank sidebar (or just the "logout" link), since they are
immediately redirected to the attendance page anyway.

---

## Route Summary

| Route | Method | Controller | Access |
|---|---|---|---|
| `/admin/add_attend_by_date` | GET | `AttendsCrudController@addAttendByDate` | Admin + Teacher |
| `/admin/attend_easy_form` | GET | `AttendsCrudController@AttendEasyForm` | Admin + Teacher |
| `/admin/save_attend_easy_form` | POST | `AttendsCrudController@SaveAttendEasyForm` | Admin + Teacher |
| All other `/admin/*` | ANY | — | Admin only (Teacher redirected) |
| `api/teacher` | GET | `UserApiController@teachers` | Admin (web guard) |

---

## Files Touched

| File | Change |
|---|---|
| `app/Http/Controllers/Admin/ClassRoomCrudController.php` | Add `user_id` subfield to teachers repeatable |
| `app/Http/Controllers/Api/UserApiController.php` | New file — teacher list endpoint |
| `app/Models/ClassRoom.php` | Update `getByDate()` JSON filter |
| `app/Http/Controllers/Admin/Auth/LoginController.php` | Override redirect for Teacher |
| `app/Http/Middleware/RedirectTeacherToAttendance.php` | New middleware |
| `app/Http/Kernel.php` | Register middleware |
| `resources/views/vendor/backpack/base/inc/sidebar_content.blade.php` | Hide menu for Teacher |
| `routes/api.php` | Add teacher API route |
| `resources/lang/ar/user.php` + `resources/lang/en/user.php` | Add translation keys |

---

## No Migration Needed

- `ClassRoom.teachers` is already a JSON column — adding `user_id` to
  existing entries is a data-level change only, done through the CRUD form.
- The `class_room_teacher` pivot table remains in the schema (to avoid
  breaking existing migration history) but `classTeachers()` relation will
  no longer be used for filtering.

---

## Out of Scope for This Feature

- Teacher creating/editing students, marks, payments, or reports
- Teacher seeing other teachers' classrooms
- Teacher managing curriculum or course structure
- Notifications or email for teacher assignment
