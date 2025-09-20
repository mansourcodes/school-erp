<?php

namespace App\Http\Controllers\StudentPanel;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class StudentAuthController extends Controller
{

    public function showLoginForm()
    {
        return view('auth.student-login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'cpr' => ['required'],
            'password' => ['required'],
        ]);

        if (Auth::guard('student')->attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->intended('/dashboard');
        }

        return back()->withErrors([
            'cpr' => 'Invalid CPR or password.',
        ]);
    }

    public function logout(Request $request)
    {
        Auth::guard('student')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }

    public function dashboard()
    {
        return view('students.dashboard'); // Make a dashboard view
    }
}
