<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use Exception;
use Ferdous\OtpValidator\Constants\StatusCodes;
use Ferdous\OtpValidator\Object\OtpRequestObject;
use Ferdous\OtpValidator\OtpValidator;
use Ferdous\OtpValidator\Object\OtpValidateRequestObject;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Date;

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
        $clientId = time();
        $type = isset($email) ? 'email' : 'phone';
        $otp = OtpValidator::requestOtp(
            new OtpRequestObject($clientId, $type, $phone, $email)
        );
        if($otp['code'] == StatusCodes::SUCCESSFULLY_SENT_OTP) return $otp;
        else throw new Exception($otp['message']);
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
        if($request->has('email') && $res['code'] == StatusCodes::OTP_VERIFIED) {
            $user = User::where('email', $request->email)->first();
            $user->update(['email_verified_at' => now()]);
        }
        return response()->json($res);
    }

    public function validateOtpForResetPassword(Request $request)
    {
        $uniqId = $request->input('uniqueId');
        $otp = $request->input('otp');
        $res = OtpValidator::validateOtp(
            new OtpValidateRequestObject($uniqId,$otp)
        );
        if($res['code'] == StatusCodes::OTP_VERIFIED) return true;
        else return false;
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
