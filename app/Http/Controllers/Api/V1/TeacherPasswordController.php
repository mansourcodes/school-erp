<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/** The teacher changes their own password from the portal profile, confirming the current one. */
class TeacherPasswordController extends Controller
{
    /** PUT /teacher/password */
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

        if (! Hash::check($request->input('current_password'), $user->password)) {
            $this->log('portal.password.wrong_current', $user, $request);
            throw ValidationException::withMessages(['current_password' => [__('portal.password.current_wrong')]]);
        }

        // A new password rotates the remember token (SignsOutDevicesOnPasswordChange), so the
        // teacher's other devices sign in again; this device gets a fresh remember cookie.
        $user->password = Hash::make($request->input('password'));
        $user->save();

        Auth::guard('web')->login($user, true);
        $request->session()->regenerate();
        $this->log('portal.password.changed', $user, $request);

        return response()->noContent();
    }

    protected function log($event, $user, Request $request)
    {
        Log::channel('security')->notice($event, [
            'user_id' => $user->id,
            'ip' => $request->ip(),
            'user_agent' => Str::limit((string) $request->userAgent(), 255),
        ]);
    }
}
