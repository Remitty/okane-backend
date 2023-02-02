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

class StocksController extends Controller
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

    /**
     * @param \App\Repositories\AlpacaRepository
     */
    public function createAccount(AlpacaRepository $alpacaRepo)
    {
        /**
         * @var \App\Models\User
         */
        $user = Auth::user();

        if(isset($user->account_id))
            return response()->json($user);

        $params = $alpacaRepo->paramsForCreateAccount($user);
        try {
            $res = $this->alpaca->account->create($params);

            $alpacaRepo->updateAccountToUser($user, $res);
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

        } catch (\Throwable $th) {
            // Log::info('user plaid token: '.$th->getMessage());
            return response()->json(['error' => $th->getMessage()], 500);
        }
        return response()->json(['link_token' => $token]);
    }

    public function connectPlaid(Request $request)
    {
        if(!$request->has('public_token'))
            return response()->json(['error' => 'The public_token field is required.'], 500);
        if(!$request->has('account_id'))
            return response()->json(['error' => 'The account_id field is required.'], 500);

        try {
            $processorToken = $this->plaid->connectPlaid($request->public_token, $request->account_id, 'alpaca');
            /**
             * @var \App\Models\User
             */
            $user = Auth::user();
            $bank = $this->alpaca->funding->createAchRelationship($user->account_id, ['processor_token' => $processorToken]);

            Bank::create([
                'user_id' => $user->id,
                'type' => 'ach',
                'relation_id' => $bank->id,
                'routing_number' => $bank->bank_routing_number,
                'account_number' => $bank->bank_account_number,
                'owner_name' => $bank->account_owner_name,
                'nickname' => $bank->nickname,
                'status' => $bank->status
            ]);

            $user->update(['bank_linked' => true]);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
        return response()->json($bank);
    }

    public function searchAssetsAll()
    {
        try {
            $data = $this->alpaca->asset->getAssetsAll(['status' => 'active', 'asset_class' => 'us_equity']);
            return response()->json($data);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }

    public function searchAsset($symbol)
    {
        try {
            $data = $this->alpaca->asset->getAssetBySymbol($symbol);
            return response()->json($data);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }

    public function createOrder(Request $request)
    {
        if(!$request->has('symbol'))
            return response()->json(['error' => 'The symbol field is required.'], 500);
        if(!$request->has('amount'))
            return response()->json(['error' => 'The amount field is required.'], 500);
        if(!$request->has('side'))
            return response()->json(['error' => 'The side field is required.'], 500);
        if(!$request->has('asset_class'))
            return response()->json(['error' => 'The asset_class field is required.'], 500);
        if(!in_array($request->side, ['buy', 'sell']))
            return response()->json(['error' => 'The side field is required in buy or sell.'], 500);

        $user = Auth::user();
        $params = [
            'symbol' => $request->symbol,
            'notional' => $request->amount,
            'side' => $request->side, // buy or sell
            'type' => 'market',
            'time_in_force' => 'day',
            'subtag' => $request->asset_class // es_equity / crypto
        ];
        try {
            $this->alpaca->trade->createOrder($user->account_id, $params);

            return response()->json(['status' => true]);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }

    public function cancelOrder(Request $request)
    {
        if(!$request->has('order_id'))
            return response()->json(['error' => 'The order_id field is required.'], 500);

        $user = Auth::user();
        try {
            $this->alpaca->trade->deleteOrder($user->account_id, $request->order_id);
            return response()->json(['status' => true]);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }

    /**
     * @param \App\Repositories\AlpacaRepository
     */
    public function fund(Request $request, AlpacaRepository $alpacaRepo)
    {
        if(!$request->has('amount'))
            return response()->json(['error' => 'The amount field is required.'], 500);

        try {
            $user = Auth::user();
            $params = $alpacaRepo->paramsForTransfer($user, $request->amount, 'INCOMING');

            $this->alpaca->funding->createTransferEntity($user->account_id, $params);
            return response()->json(['status' => true]);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }

    /**
     * @param \App\Repositories\AlpacaRepository
     */
    public function withdraw(Request $request, AlpacaRepository $alpacaRepo)
    {
        if(!$request->has('amount'))
            return response()->json(['error' => 'The amount field is required.'], 500);

        try {
            $user = Auth::user();
            $params = $alpacaRepo->paramsForTransfer($user, $request->amount, 'OUTGOING');

            $this->alpaca->funding->createTransferEntity($user->account_id, $params);
            return response()->json(['status' => true]);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }

    public function getWatchList()
    {
        try {
            $user = Auth::user();
            $list = $this->alpaca->trade->getWatchLists($user->account_id);
            return response()->json($list);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }

    public function setWatchList(Request $request)
    {
        try {
            /**
             * @var \App\Models\User
             */
            $user = Auth::user();
            $watchlistId = $user->watchlist_id;
            if(!isset($watchlistId)) {
                $params = [
                    'name' => $user->email.' watchlist',
                    'symbols' => [$request->symbol]
                ];
                $res = $this->alpaca->trade->createWatchlist($user->account_id, $params);
                $user->update(['watchlist_id' => $res->id]);
            } else {
                $this->alpaca->trade->addAssetsToWatchlist($user->account_id, $watchlistId, [$request->symbol]);
            }
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }
}
