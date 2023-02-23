<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use Ferdous\OtpValidator\Constants\StatusCodes;
use Ferdous\OtpValidator\Object\OtpRequestObject;
use Ferdous\OtpValidator\OtpValidator;
use Ferdous\OtpValidator\Object\OtpValidateRequestObject;
use Ferdous\OtpValidator\Services\OtpService;
use Illuminate\Support\Facades\Config;

class OtpController extends Controller
{
    /**
     * @param string $email
     * @param string|null $phone
     * @return array
     */
    public function requestForOtp($email, $phone=null)
    {
        is_null($email) ? Config::set('otp.send-by.email', 0) : Config::set('otp.send-by.email', 1);
        is_null($phone) ? Config::set('otp.send-by.sms', 0) : Config::set('otp.send-by.sms', 1);
        return OtpValidator::requestOtp(
            new OtpRequestObject(OtpService::otpGenerator(), 'email-verification', $phone, $email)
        );
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function validateOtp(Request $request)
    {
        $uniqId = $request->input('uniqueId');
        $otp = $request->input('otp');
        $res = OtpValidator::validateOtp(
            new OtpValidateRequestObject($uniqId,$otp)
        );
        if($request->has('email')) {
            $user = User::where('email', $request->email)->first();
            $user->update(['email_verified_at' => now()]);
        }
        return response()->json($res);
    }

    /**
     * @param Request $request
     * @return array
     */
    public function resendOtp(Request $request)
    {
        $uniqueId = $request->input('uniqueId');
        $res = OtpValidator::resendOtp($uniqueId);
        return response()->json($res);
    }

}
