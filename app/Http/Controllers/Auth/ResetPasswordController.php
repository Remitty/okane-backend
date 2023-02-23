<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ResetPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset requests
    | and uses a simple trait to include this behavior. You're free to
    | explore this trait and override any methods you wish to tweak.
    |
    */

    use ResetsPasswords;

    /**
     * Where to redirect users after resetting their password.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    public function mobile(Request $request)
    {
        if(! $request->has('otp')) {
            return response()->json(['error' => 'The OTP is required.', 'code' => 0], 500);
        }
        if(! $request->has('email')) {
            return response()->json(['error' => 'The email is required.', 'code' => 0], 500);
        }
        if(! $request->has('password')) {
            return response()->json(['error' => 'The password is required.', 'code' => 0], 500);
        }

        try {
            $res = (new OtpController)->validateOtpForResetPassword($request);

            if(!$res) return response()->json(['success' => false]);

            $pw = Hash::make($request->password);
            User::where('email', $request->email)->update(['password' => $pw]);

            return response()->json(['success' => true]);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
