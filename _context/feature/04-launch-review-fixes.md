# Feature 04 — Launch Review Fixes (student portal redesign)

## Summary

Fixes for the code review of `main...feature/student-portal-redesign` (436 files,
~39k lines) before the portal launch. The review was a single pass. Each step
below starts with a failing test that proves the bug, then the fix.

Stack facts that shape the fixes: Laravel **8.29**, Sanctum **2.15**, Backpack
CRUD **4.1**, MySQL. Portal guards are `PortalLogin::GUARDS = ['student' => 'student',
'teacher' => 'web']`. Sanctum checks `['student', 'web']` in that order.

---

## Priority

| # | Issue | Blocks launch | Size | Status (2026-10-16) |
|---|---|---|---|---|
| 1 | Teacher login throttle ignores the username | **Yes** | XS | Fixed |
| 2 | Admin save deletes teacher photo / setting logo | **Yes** | S | **False positive**, regression tests added |
| 3 | Password change leaves other sessions signed in | **Yes** | M | Fixed |
| 4 | Public `/api/student` (and siblings) leak CPR + phones | **Yes** | M | Fixed |
| 5 | Login keeps the other portal guard signed in | No (soon) | S | Fixed |
| 6 | Concurrent attendance saves create duplicate records | No (soon) | S | Fixed (lock; race not testable in PHPUnit) |
| 7 | Service worker drops offline saves on a 419 replay | No (soon) | M | Fixed |
| 8 | `currentClassroom()` ordering — verified correct, add a test | No | XS | **False positive**, regression test added |
| 9 | Outbox poll runs two UPDATEs on every GET | No | S | Fixed (tidy on the first admin dashboard visit of the day; no cron in production) |

Step 2 turned out wrong once tested: Laravel 8's `Request::createFromBase()` runs `filterFiles()`, which drops
`UPLOAD_ERR_NO_FILE` entries, so an untouched upload field never reaches `exists()`. Only the clear button's hidden
input does. The code was left as it is.

Steps 1–4 ship tonight or the launch moves. Steps 5–9 go in the first patch
after launch.

---

## Step 1 — Teacher login throttle key

**File:** `app/Providers/RouteServiceProvider.php` (`student-login` limiter, ~line 127)

**Bug:** the per-account+IP key uses `input('email')`, but the teacher form sends
`username` (`AuthController::login`, `LoginForm.tsx`). Every teacher attempt from
one IP shares the key `teacher||<ip>`.

**Fix:** key on the field that is actually sent, normalised the same way
`PortalLogin` matches it:

```php
$account = $request->input('role') === 'teacher'
    ? strtolower(trim((string) $request->input('username')))
    : (string) $request->input('cpr');

Limit::perMinute(config('portal.login.per_cpr_ip_per_minute'))
    ->by('account-ip|' . sha1($request->input('role') . '|' . $account) . '|' . $request->ip())
```

Update the comment above it ("account = CPR or teacher username"). Check that
`LoginLockout` keys on `username` too, not `email`.

**Tests** (`tests/Feature/Api/V1/`):
- Two different teacher usernames from one IP: hitting the limit for the first does
  not throttle the second.
- One username, repeated bad attempts: throttled at `per_cpr_ip_per_minute`.

---

## Step 2 — Upload fields wipe the stored file on an unrelated save

**Files:**
- `app/Http/Controllers/Admin/UserCrudController.php` — `takePhotoInput()` (~line 168)
- `app/Http/Controllers/Admin/SettingCrudController.php` — `storeUploadedValue()` (~line 101)

**Bug:** Backpack 4.1's `upload` field always posts its `<input type="file">`, empty
when untouched. PHP reports `UPLOAD_ERR_NO_FILE`, Symfony's FileBag stores the key with
`null`, and Laravel's `exists()` merges files into `all()`. So "unchanged" is read as
"cleared". The clear button is different: it adds `<input type="hidden" name="{field}" value="">`
(`vendor/backpack/crud/src/resources/views/crud/fields/upload.blade.php:166`), and that
lands in the **request** bag, not the files bag.

**Fix:** treat it as cleared only when the hidden input is present in the request bag:

```php
// UserCrudController::takePhotoInput
$photo = $file ?: ($request->request->has('photo_path') ? false : null);

// SettingCrudController::storeUploadedValue
if (! $file && ! $request->request->has('value')) {
    return;
}
```

Fix the comment in `storeUploadedValue` too: an unchanged field posts an *empty file
input*, not nothing.

**Tests** (extend `tests/Feature/Admin/SettingCrudTest.php`, add a teacher-photo test):
- Update the setting with a multipart request where `value` is an empty file (no
  upload): the stored file and `value` stay.
- Update with `value=''` in the body: the file is deleted and `value` is `''`.
- Same pair for a teacher's `photo_path` through the User CRUD update.

Before writing the fix, check the browser behaviour once by hand: edit a teacher who has a
photo, change only the name, save, and confirm the photo disappears.

---

## Step 3 — Password change must end other live sessions

**Files:** new `app/Http/Middleware/PortalAuthenticateSession.php`, `routes/api.php`,
`app/Http/Kernel.php`, `AuthController::login`

**Bug:** `SignsOutDevicesOnPasswordChange` only rotates `remember_token`. That kills
remember cookies, but not live sessions. `AuthenticateSession` is commented out in the `web`
group. Laravel 8's stock middleware can't simply be switched on either: it reads
`password_hash_{default driver}` and calls the *default* guard, so it would never check
the `student` guard.

**Fix:** a portal-aware version of the middleware:

1. Find the guard that authenticated the request: the first of `PortalLogin::GUARDS`
   whose `Auth::guard($g)->check()` matches `$request->user()`.
2. If the session has no `password_hash_{guard}` yet, store `$user->getAuthPassword()`
   and continue. This is how sessions from before the deploy get their baseline.
3. If it differs, call `Auth::guard($guard)->logoutCurrentDevice()`, invalidate the
   session and return 401 JSON (the SPA already routes 401 to the login screen).
4. After the response, refresh the stored hash (same as the stock middleware).

Register it as `portal.session` and add it to the `auth:sanctum` group in `routes/api.php`,
right after `auth:sanctum`. Store the hash in `AuthController::login` right after
`Auth::guard(...)->login()`, so the first request already has it.

**Impersonation:** check how `PortalImpersonator` signs the admin in as the user. An
impersonated session should also be ended by a password change, but must not log the
admin out of Backpack. Only the portal guard is logged out, never `backpack`.

Leave the Backpack admin (`web` group) as it is. This step is portal-only.

**Tests:**
- Student signed in on two sessions; change the password from session A (or as an admin).
  Session B's next `/api/v1/me` returns 401.
- The session that changed the password stays signed in (`PortalPasswordController`
  refreshes its own stored hash).
- Same for a teacher on the `web` guard.

---

## Step 4 — Lock down the legacy `/api/*` lookup routes

**Files:** `routes/api.php` (lines 21–25), `routes/backpack/custom.php`, and every
`data_source` / ajax URL in `app/Http/Controllers/Admin/*CrudController.php`

**Bug:** `/api/student`, `/api/teacher`, `/api/teacher/{id}`, `/api/course` and
`/api/classroom` have no auth. `/api/student` searches and returns CPR, `mobile`,
`mobile2` and `address`. CPR is now the student username, so the route hands out a
full list of logins plus personal data. This predates the branch, but the branch made it a
credential problem.

**Callers:** only the Backpack admin's `select2_from_ajax` fields (Attends, ClassRoom,
Course, ExamTool, Message, Payment, StudentMarks CRUDs). The SPA does not use them.

**Fix:** the `api` group has no session, so `auth:backpack` won't work there. Move
the five routes into `routes/backpack/custom.php` (prefix `admin`, Backpack web + admin
middleware) as `admin/api/student`, and so on. Then change each `url('api/...')` to
`backpack_url('api/...')`. Delete the old routes. Don't keep them as aliases.

Also limit what `StudentController::index` returns: `id`, `name`, `cpr`
(select2 needs only the label columns), instead of the whole row.

**Tests:**
- A guest GET to `/admin/api/student` is redirected or gets 401. The old `/api/student`
  returns 404.
- A signed-in admin gets results, and the JSON has no `mobile` or `address`.
- Click through each CRUD that uses an ajax select (list in "Callers") to confirm the
  dropdowns still load.

---

## Step 5 — Sign out the other portal guard on login

**File:** `app/Http/Controllers/Api/V1/AuthController.php` (`login`)

**Bug:** login signs into one guard but leaves the other signed in. Sanctum checks
`student` first, so a leftover student session (or remember cookie) on a shared phone
wins over a teacher who just signed in. The teacher then gets 403 on `/teacher/*`.

**Fix:** before `Auth::guard(...)->login($user, true)`, sign out every *other* portal
guard with `logoutCurrentDevice()`. Don't use `logout()`: it rotates that user's
`remember_token` and would sign them out on their own devices too.

**Test:** with an active student session, log in as a teacher. `/api/v1/me` returns the
teacher and `/api/v1/teacher/...` returns 200.

---

## Step 6 — Serialise concurrent attendance saves

**File:** `app/Services/Attendance/AttendanceRecorder.php` (`save`, ~line 29)

**Bug:** a find-then-create inside a transaction, with no lock. A Background Sync replay
and the page's handoff resend (or two tabs) can both miss the record, which gives two
`Attends` rows, two `AttendanceFirstRecorded` events, duplicate pushes and doubled report
counts. A unique index doesn't fit, because `find()` matches by hour (or by day for slots
with no time), not by an exact key.

**Fix:** take a row lock on the classroom at the start of the transaction, so saves
for one classroom run one at a time:

```php
ClassRoom::whereKey($classroom->id)->lockForUpdate()->first();
$attend = $this->find($classroom, $curriculumId, $date, $start);
```

The lock is per classroom and short (one roster write), so contention is negligible.

**Test:** the race itself can't be reproduced in PHPUnit. Instead, test that calling `save()`
twice in a row gives one record, `created` is true only the first time, and the event fires
once. Note in the PR that the lock is checked on MySQL only.

---

## Step 7 — Don't lose offline saves on a 419 replay

**Files:** `frontend/src/app/pwa/sw.ts` (~line 61),
`frontend/src/features/mark-attendance/model/use-roster-handoff.ts`

**Bug:** on a 419, the worker posts `ROSTER_REPLAY` to `clients[0]` and drops the entry.
If that window doesn't have `useRosterHandoff` mounted (login page, student shell, stale
tab), the save is lost and the "waiting to send" badge stays forever.

**Fix:** a handoff that the page acknowledges:
1. On 419, put the entry back in the queue (`unshiftRequest`) *before* posting, and post
   `ROSTER_REPLAY` to **all** window clients.
2. `useRosterHandoff` resends with a fresh CSRF token, then posts
   `ROSTER_REPLAY_DONE { code }` back to the worker.
3. On `ROSTER_REPLAY_DONE`, the worker removes the entry for that `code` from the queue.
   With no ack, it stays and is retried on the next sync or worker start.
4. If several tabs answer, the resend is safe: the PUT is idempotent, and Step 6
   removes the race.

**Tests** (Vitest): the handoff hook posts the ack only after the resend succeeds. A worker
unit test (or a manual check in DevTools → Application → Background Sync) shows that a 419
with only a login tab open keeps the entry.

---

## Step 8 — `currentClassroom()` ordering (verified: not a bug, test only)

**File:** `app/Models/Student.php` (~line 126)

**Review claim:** it orders by `class_rooms.id` only. **Checked:** wrong. `classRooms()`
already applies `orderBy('courses.start_date', 'desc')`, and `currentClassroom()` adds
`class_rooms.id desc` as the second key. That matches the docblock and `TeacherScope`.

**Action:** no code change. Add a regression test so the order can't silently drift:
a student in two active courses, where the newer classroom id belongs to the
earlier-starting course. `currentClassroom()` returns the later course's classroom.

---

## Step 9 — Move outbox cleanup out of the poll

**File:** `app/Services/Portal/PushOutbox.php` (`pendingFor`, ~line 81)

**Issue:** every `GET /teacher/push-deliveries` runs two bulk UPDATEs (expire and un-stick)
before reading. That's write load on every poll from every teacher tab.

**Fix:** move both UPDATEs into the scheduled prune (or a new `push:outbox-maintain`
every minute in `Console/Kernel`). Keep `pendingFor()` read-only and make the SELECT
skip expired rows (it already filters `created_at >= cutoff`). Have it also treat
`SENDING` rows older than `STUCK_MINUTES` as pending, so behaviour doesn't change
between scheduler runs.

**Test:** existing outbox tests still pass. A stuck `SENDING` row is still returned
by `pendingFor()` without being updated.

---

## Launch checklist (tonight)

- [ ] Steps 1–4 merged, with their tests green (`php artisan test` inside the container, as `www-data`)
- [ ] Manual: a teacher photo survives a name-only edit; the logo setting survives Save
- [ ] Manual: `/api/student` returns 404 for a guest; the admin ajax selects still work
- [ ] Manual: two teacher logins from one network aren't throttled together
- [ ] Manual: reset a student's password as admin; their other open browser is signed out
- [ ] Rollback: the branch has no migrations in Steps 1–4, so a code revert is enough
- [ ] After launch: Steps 5–9 in one patch, then update `progress-tracker.md` and `completed-tracker.md`
