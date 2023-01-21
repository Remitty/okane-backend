<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class LoginAdminController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('admin.guest')->except('logout');
    }

    /**
     * Get the guard to be used during authentication.
     *
     * @return \Illuminate\Contracts\Auth\StatefulGuard
     */
    protected function guard()
    {
        return Auth::guard('admin');
    }

    /**
     * Rdirect login.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function loginForm()
    {
        return view('admin.auth.login');
    }
    /**
     * Handle an authentication attempt.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function authenticate(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (!$this->guard()->attempt($credentials)) {
            return back()->withErrors([
                'email' => 'The provided credentials do not match our records.',
            ]);
        }

        Log::create(['content' => $request->email.' logged in']);

        return redirect('admin/index');
    }

    /**
     * Log the user out of the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function logout(Request $request)
    {
        !$this->guard()->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        Log::create(['content' => $request->email.' logged out']);

        return redirect('/admin/login');
    }
}
