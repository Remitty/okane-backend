<?php

namespace App\Http\Controllers;

use Alpaca\Alpaca;
use App\Libs\PlaidAPI;
use App\Models\Bank;
use App\Models\Country;
use App\Models\User;
use App\Repositories\AlpacaRepository;
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

    public function countries()
    {
        $data = Country::all();
        return response()->json($data);
    }
}
