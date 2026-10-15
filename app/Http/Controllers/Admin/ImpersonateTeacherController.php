<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Portal\TeacherImpersonation;
use Illuminate\Http\Request;

/** Backpack → Users → "Open portal": a Superadmin opens the teacher portal as that teacher. */
class ImpersonateTeacherController extends Controller
{
    /** POST admin/user/{id}/portal */
    public function start(Request $request, TeacherImpersonation $impersonation, $id)
    {
        $admin = backpack_user();
        // CheckIfAdmin also lets Teachers into Backpack.
        abort_unless($admin->hasRole('Superadmin'), 403);

        $teacher = User::findOrFail($id);
        abort_unless($teacher->hasRole('Teacher') && $teacher->id !== $admin->id, 404);

        $impersonation->start($admin, $teacher, $request);

        return redirect('/beta/teacher');
    }
}
