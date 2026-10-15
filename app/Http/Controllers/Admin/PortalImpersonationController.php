<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\User;
use App\Services\Portal\PortalImpersonator;
use App\Support\PortalMode;
use Illuminate\Http\Request;

/** Backpack → Students / Users → "Open portal": a Superadmin opens the portal as that student or teacher. */
class PortalImpersonationController extends Controller
{
    /** POST admin/student/{id}/portal */
    public function student(Request $request, PortalImpersonator $impersonator, $id)
    {
        $admin = $this->superadmin();

        $impersonator->start($admin, Student::findOrFail($id), 'student', $request);

        return redirect(PortalMode::appPath('/'));
    }

    /** POST admin/user/{id}/portal */
    public function teacher(Request $request, PortalImpersonator $impersonator, $id)
    {
        $admin = $this->superadmin();

        $teacher = User::findOrFail($id);
        abort_unless($teacher->hasRole('Teacher') && $teacher->id !== $admin->id, 404);

        $impersonator->start($admin, $teacher, 'teacher', $request);

        return redirect(PortalMode::appPath('/teacher'));
    }

    protected function superadmin()
    {
        $admin = backpack_user();
        // CheckIfAdmin also lets Teachers into Backpack.
        abort_unless($admin->hasRole('Superadmin'), 403);

        return $admin;
    }
}
