<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class StudentLoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        $this->middleware('guest:student')->except('logout');
    }



    public function showStudentLoginForm()
    {
        return view('auth.login', ['url' => 'student']);
    }

    public function studentLogin(Request $request)
    {
        $this->validate($request, [
            'cpr'   => 'required|numeric',
            'password' => 'required|min:6'
        ]);

        // if (Auth::guard('student')->attempt(['cpr' => $request->cpr, 'password' => $request->password], $request->get('remember'))) {
        if ($this->attempt(['cpr' => $request->cpr, 'password' => $request->password], $request->get('remember'))) {

            return redirect()->intended('/student');
        }
        return back()->withInput($request->only('cpr', 'remember'));
    }

    public function attempt(array $credentials = [], $remember = false)
    {

        $student = Student::where('cpr', $credentials['cpr'])->get()->first();
        if (
            $student &&
            Hash::check($credentials['password'], $student->getAuthPassword())
        ) {

            Auth::login(User::all()->first(), $remember);
            // Auth::guard('student')->login($student, $remember);
            return true;
        }

        return false;
    }
}
