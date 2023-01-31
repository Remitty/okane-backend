<?php

namespace App\Http\Controllers;

use Alpaca\Alpaca;
use App\Libs\PlaidAPI;
use App\Models\Country;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class ApiController extends Controller
{
    /**
     * @var \Alpaca\Alpaca
     */
    protected $alpaca;

    /**
     * @var \App\Libs\PlaidAPI
     */
    protected $plaid;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $key = config('alpaca.api_key');
        $secret = config('alpaca.secret_key');
        $mode = config('alpaca.mode');
        $this->alpaca = new Alpaca($key, $secret, $mode == 'pepper' ? true: false);
        $this->plaid = new PlaidAPI();
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

    public function completeOnboard()
    {
        /**
         * @var \App\Models\User
         */
        $user = Auth::user();

        if(isset($user->account_id))
            return response()->json($user);

        $params = [
            "enabled_assets"=> ["us_equity"],
            'contact' => [
                'email_address' => $user->email,
                'phone_number' => $user->mobile,
                'street_address' => $user->address,
                'city' => $user->city,
                'state' => $user->state,
                'country' => $user->country_code,
                'postal_code' => $user->postal_code
            ],
            'identity' => [
                'given_name' => $user->first_name,
                'family_name' => $user->last_name,
                'date_of_birth' => $user->dob,
                'tax_id' => $user->tax_id,
                'tax_id_type' => $user->tax_id_type,
                'country_of_citizenship' => $user->country_code,
                'country_of_tax_residence' => $user->country_code,
                'funding_source' => explode(",", $user->funding_source)
            ],
            'disclosures' => [
                'is_control_person' => $user->public_shareholder == 1 ? true : false,
                'is_affiliated_exchange_or_finra' => $user->is_affiliated_exchange_or_finra == 1 ? true : false,
                'is_politically_exposed' => $user->is_politically_exposed == 1 ? true : false,
                'immediate_family_exposed' => $user->immediate_family_exposed == 1 ? true : false
            ],
            'trusted_contact' => [
                'given_name' => $user->first_name,
                'family_name' => $user->last_name,
                'email_address' => $user->email
            ],
            'agreements' => [[
                'agreement' => 'customer_agreement',
                'signed_at' => today(),
                'ip_address' => $user->ip_address ?? ''
            ]]
        ];
        if($user->public_shareholder || $user->is_affiliated_exchange_or_finra) {
            if($user->public_shareholder) $context = 'CONTROLLED_FIRM';
            if($user->is_affiliated_exchange_or_finra) $context = 'AFFILIATE_FIRM';
            $params['disclosures']['context'] = [[
                'context_type' => $context,
                'company_name' => $user->shareholder_company_name,
                'company_street_address' => $user->shareholder_company_address,
                'company_city' => $user->shareholder_company_city,
                'company_state' => $user->shareholder_company_state,
                'company_country' => $user->shareholder_company_country,
                'company_compliance_email' => $user->shareholder_company_email
            ]];
        }
        if($user->immediate_family_exposed) {
            $params['disclosures']['context'] = [[
                'context_type' => 'IMMEDIATE_FAMILY_EXPOSED',
                'given_name' => $user->first_name,
                'family_name' => $user->last_name
            ]];
        }
        try {
            $res = $this->alpaca->account->create($params);

            $user->update([
                'account_id' => $res['id'],
                'account_number' => $res['account_number'],
                'account_status' => $res['status'],
                'account_currency' => $res['currency'],
                'account_type' => $res['account_type'],
            ]);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }

        return response()->json($user);
    }

    public function createPlaidLinkToken()
    {
        /**
         * @var \App\Models\User
         */
        $user = Auth::user();

        try {
            $token = $this->plaid->createLinkToken(strval($user->id));
            $user->update(['plaid_token' => $token]);
        } catch (\Throwable $th) {
            // Log::info('user plaid token: '.$th->getMessage());
            return response()->json(['error' => $th->getMessage()], 500);
        }
        return response()->json(['link_token' => $token]);
    }

    public function connectPlaid(Request $request)
    {
        try {
            $processorToken = $this->plaid->connectPlaid($request->public_token, $request->account_id, 'alpaca');

            $user = Auth::user();
            $bank = $this->alpaca->funding->createAchRelationship($user->account_id, ['processor_token' => $processorToken]);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
        return response()->json($bank);
    }

}
