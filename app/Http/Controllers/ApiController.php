<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Auth\OtpController;
use App\Models\Country;
use App\Models\Document;
use App\Models\User;
use Exception;
use Ferdous\OtpValidator\Constants\StatusCodes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ApiController extends Controller
{

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
    }

    public function authenticate(Request $request)
    {
        if(! $request->has('email')) {
            return response()->json(['error' => 'The email is required.', 'code' => 0], 500);
        }
        if(! $request->has('password')) {
            return response()->json(['error' => 'The password is required.', 'code' => 0], 500);
        }

        if (!filter_var($request->email, FILTER_VALIDATE_EMAIL)) {
            return response()->json(['error' => 'Invalid email format.', 'code' => 1], 500);
        }

        $credentials = $request->only('email', 'password');

        try {
            if (!Auth::attempt($credentials)) {
                return response()->json(['error' => 'The email address or password you entered is incorrect.'], 401);
            }
        } catch (\Throwable $e) {
            return response()->json(['error' => 'Something went wrong, Please try again later!'], 500);
        }

        /**
         * @var \App\Models\User
         */
        $user = Auth::user();
        $user->update(['last_login' => now()]);

        $data['token'] = "Bearer " . $user->createToken('api')->plainTextToken;
        $data['user'] = $user;
        $data['user']['bank'] = $user->bank;

        if(is_null($user->email_verified_at)) {
            try {
                $otp = (new OtpController)->requestForOtp($request->email);

                if($otp['code'] == StatusCodes::SUCCESSFULLY_SENT_OTP)
                    $data['otp_id'] = $otp['uniqueId'];
            } catch (\Throwable $e) {
                return response()->json(['error' => $e->getMessage()], 500);
            }

        }

        return response()->json($data);
    }

    public function register(Request $request)
    {
        if(! $request->has('email')) {
            return response()->json(['error' => 'The email is required.', 'code' => 0], 500);
        }
        if(! $request->has('password')) {
            return response()->json(['error' => 'The password is required.', 'code' => 0], 500);
        }
        // if(! $request->has('ip_address')) {
        //     return response()->json(['error' => 'The ip address is required.', 'code' => 0], 500);
        // }

        if (!filter_var($request->email, FILTER_VALIDATE_EMAIL)) {
            return response()->json(['error' => 'Invalid email format.', 'code' => 1], 500);
        }

        if(User::where('email', $request->email)->count() > 0)
            return response()->json(['error' => 'Already exists an user with the email.', 'code' => 0], 500);

        try {
            $otp = (new OtpController)->requestForOtp($request->email);
            if($otp['code'] != StatusCodes::SUCCESSFULLY_SENT_OTP)
                throw new Exception($otp['message']);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
        $userdata = $request->all();
        $userdata['password'] = Hash::make($request->password);
        $user = User::create($userdata);

        $credentials = $request->only('email', 'password');
        try {
            if (!Auth::attempt($credentials)) {
                return response()->json(['error' => 'The email address or password you entered is incorrect.'], 401);
            }
        } catch (\Throwable $e) {
            return response()->json(['error' => 'Something went wrong, Please try again later!'], 500);
        }

        /**
         * @var \App\Models\User
         */
        $user = Auth::user();
        $user->update(['last_login' => now()]);

        $data['token'] = "Bearer " . $user->createToken('api')->plainTextToken;
        $data['user'] = $user;
        $data['otp_id'] = $otp['uniqueId'];

        return response()->json($data);
    }

    public function updateProfile(Request $request)
    {
        /**
         * @var \App\Models\User
         */
        $user = Auth::user();
        $user->update($request->all());

        return response()->json($user);
    }

    public function uploadDocument(Request $request)
    {
        /**
         * @var \App\Models\User
         */
        $user = Auth::user();
        if($request->has('verify_doc')) {
            $file = $request->file('verify_doc');
            $verify_doc = $file->storeAs('documents', $user->id.'.'.$file->getClientOriginalExtension(), 'public');
            // Document::updateOrCreate([
            //     'user_id' => $user->id
            // ], [
            //     'content' => $verify_doc
            // ]);
            $user->update(['profile_completion' => 'document', 'doc' => get_file_link($verify_doc)]);
        }

        return response()->json(['success' =>true]);
    }

    public function countries()
    {
        $data = Country::all();
        return response()->json($data);
    }

    public function setDeviceToken(Request $request)
    {
        if(! $request->has('device_token')) {
            return response()->json(['error' => 'The device_token is required.', 'code' => 0], 500);
        }

        /**
         * @var \App\Models\User
         */
        $user = Auth::user();
        $user->update(['device_token' => $request->device_token]);
        return response()->json(['success' => true]);
    }
}
