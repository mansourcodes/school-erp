<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\TeacherMeResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * The teacher changes their own sign-in username from the portal profile (same rules as the admin users CRUD),
 * confirming their current password, so a signed-in session alone cannot change how they sign in.
 */
class TeacherUsernameController extends Controller
{
    /** PUT /teacher/username */
    public function update(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'username' => User::usernameRules($user->id),
            'current_password' => ['required', 'string', 'max:128'],
        ], [
            'username.required' => __('portal.username.required'),
            'username.min' => __('portal.username.min'),
            'username.max' => __('portal.username.max'),
            'username.regex' => __('portal.username.regex'),
            'username.unique' => __('portal.username.unique'),
            'current_password.required' => __('portal.password.current_required'),
        ]);

        if (! Hash::check($request->input('current_password'), $user->password)) {
            $this->log('portal.username.wrong_current', $user, $request);
            throw ValidationException::withMessages(['current_password' => [__('portal.password.current_wrong')]]);
        }

        $previous = $user->username;
        $user->username = $request->input('username');
        $user->save();

        if ($user->username !== $previous) {
            $this->log('portal.username.changed', $user, $request);
        }

        return new TeacherMeResource($user);
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
