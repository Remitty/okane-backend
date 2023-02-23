<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Http\Request;

class ForgotPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset emails and
    | includes a trait which assists in sending these notifications from
    | your application to your users. Feel free to explore this trait.
    |
    */

    use SendsPasswordResetEmails;

    public function mobile(Request $request)
    {
        if(! $request->has('email')) {
            return response()->json(['error' => 'The email is required.', 'code' => 0], 500);
        }
        $user = User::where('email', $request->email)->first();
        if(is_null($user)) return response()->json(['error' => 'No exist with the email'], 500);

        try {
            $otp = (new OtpController)->requestForOtp($request->email);

            return response()->json($otp);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
