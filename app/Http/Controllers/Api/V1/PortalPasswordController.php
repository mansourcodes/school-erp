<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Services\Portal\PortalLogin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * A signed-in student or teacher changes their own password from the portal profile, confirming
 * the current one. A forgotten password is still reset by the administration (no online reset).
 */
class PortalPasswordController extends Controller
{
    /** PUT /student/password, PUT /teacher/password */
    public function update(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'string', 'max:128'],
            'password' => ['required', 'string', 'min:8', 'max:128', 'confirmed', 'different:current_password'],
        ], [
            'current_password.required' => __('portal.password.current_required'),
            'password.required' => __('portal.password.required'),
            'password.min' => __('portal.password.min'),
            'password.max' => __('portal.password.max'),
            'password.confirmed' => __('portal.password.confirmed'),
            'password.different' => __('portal.password.different'),
        ]);

        $user = $request->user();
        $role = $user instanceof Student ? 'student' : 'teacher';

        if (! Hash::check($request->input('current_password'), $user->password)) {
            $this->log('portal.password.wrong_current', $role, $user, $request);
            throw ValidationException::withMessages(['current_password' => [__('portal.password.current_wrong')]]);
        }

        // A new password rotates the remember token (SignsOutDevicesOnPasswordChange), so the
        // user's other devices sign in again; this device gets a fresh remember cookie.
        $user->password = Hash::make($request->input('password'));
        $user->save();

        Auth::guard(PortalLogin::GUARDS[$role])->login($user, true);
        $request->session()->regenerate();
        $this->log('portal.password.changed', $role, $user, $request);

        return response()->noContent();
    }

    protected function log($event, $role, $user, Request $request)
    {
        Log::channel('security')->notice($event, [
            'role' => $role,
            'user_id' => $user->id,
            'ip' => $request->ip(),
            'user_agent' => Str::limit((string) $request->userAgent(), 255),
        ]);
    }
}
