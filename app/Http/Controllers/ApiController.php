<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Auth\OtpController;
use App\Models\Country;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Google_Client;

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

    public function googleSign(Request $request)
    {
        $client = new Google_Client(['client_id' => config('services.google.client_id')]);  // Specify the CLIENT_ID of the app that accesses the backend
        $payload = $client->verifyIdToken($request->id_token);
        if ($payload) {

            $user = User::where('email', $payload['email'])->first();
            if(! isset($user)) {
                $user = User::create([
                    'email' => $payload['email'],
                    'password' => 'empty',
                    'last_login' => now(),
                    'email_verified_at' => now()
                ]);
            } else {
                $user->last_login = now();
                $user->save();
            }

            $data['token'] = "Bearer " . $user->createToken('api')->plainTextToken;
            $data['user'] = $user;
            $data['user']['bank'] = $user->bank;

            return response()->json($data);
        } else {
          // Invalid ID token
          return response()->json(['status' => false, 'message' => 'Invalid gmail']);
        }
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

                $data['otp_id'] = $otp['uniqueId'];
            } catch (\Throwable $e) {
                // return response()->json(['error' => $e->getMessage()], 500);
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
        } catch (\Throwable $e) {
            return response()->json(['error' => 'Can not send OTP with the email'], 500);
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
        $data = $request->all();
        if($request->has('tax_id')) {
            $country = Country::where('short_code', $user->country_code)->first();
            $data['tax_id_type'] = isset($country) ? $country->tax_id_type : 'SSN';
        }
        $user->update($data);

        return response()->json($user);
    }

    public function uploadDocument(Request $request)
    {
        /**
         * @var \App\Models\User
         */
        $user = Auth::user();
        $file = $request->file('verify_doc');
        if (is_null($file)) {
            return response()->json(['error' => 'The verify_doc field must be a file.', 'code' => 1], 500);
        }
        if(! in_array(strtolower($file->getClientOriginalExtension()), ['png', 'jpeg', 'jpg', 'pdf'])) {
            return response()->json(['error' => 'The file must be in png, jpeg, jpg, pdf.', 'code' => 1], 500);
        }
        if($file->getSize() > 2097152) // byte => 2M
            return response()->json(['error' => 'The file must be less than 2M', 'code' => 1], 500);

        $verify_doc = $file->storeAs('documents', $user->id.'.'.$file->getClientOriginalExtension(), 'public');
        // Document::updateOrCreate([
        //     'user_id' => $user->id
        // ], [
        //     'content' => $verify_doc
        // ]);
        $user->update(['profile_completion' => 'document', 'doc' => get_file_link($verify_doc)]);

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
