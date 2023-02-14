<?php

namespace App\Http\Controllers;

use Alpaca\Alpaca;
use Alpaca\Market\Alpaca as AlpacaMarket;
use App\Libs\PlaidAPI;
use App\Libs\FmpAPI;
use App\Models\Bank;
use App\Models\User;
use App\Repositories\AlpacaRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Kutia\Larafirebase\Services\Larafirebase;

class StocksController extends Controller
{
    /**
     * @var \Alpaca\Alpaca
     */
    protected $alpaca;
    /**
     * @var \Alpaca\Market\Alpaca
     */
    protected $alpacaMarket;

    /**
     * @var \App\Libs\PlaidAPI
     */
    protected $plaid;
    /**
     * @var \App\Libs\FmpAPI
     */
    protected $fmp;

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
        $this->alpacaMarket = new AlpacaMarket($key, $secret, $mode == 'pepper' ? true: false);
        $this->plaid = new PlaidAPI();
        $this->fmp = new FmpAPI(config('fmp.api_key'));
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
                'relation_id' => $bank['id'],
                'routing_number' => $bank['bank_routing_number'],
                'account_number' => $bank['bank_account_number'],
                'owner_name' => $bank['account_owner_name'],
                'nickname' => $bank['nickname'],
                'status' => $bank['status']
            ]);

            $user->update(['bank_linked' => true]);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
        return response()->json($bank);
    }

    public function searchAssets(Request $request, $class)
    {
        try {
            $query = $request->q ?? '';
            $limit = $request->q ? 5 : 20;
            $exchange = $class == 'stocks' ? 'NASDAQ' : 'crypto';
            $data = $this->fmp->get_search($query, $limit, $exchange);
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
        if(!in_array($request->side, ['buy', 'sell']))
            return response()->json(['error' => 'The side field is required in buy or sell.'], 500);

        $subtag = $request->tag ?? 'es_equity';
        $user = Auth::user();
        $params = [
            'symbol' => $request->symbol,
            'notional' => $request->amount,
            'side' => $request->side, // buy or sell
            'type' => 'market',
            'time_in_force' => 'day',
            'commission' => $subtag == 'es_equity' ? 0.5 : $request->amount * 0.01,
            'subtag' =>  $subtag// es_equity / crypto
        ];
        try {
            $order = $this->alpaca->trade->createOrder($user->account_id, $params);

            return response()->json($order);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }

    public function cancelOrder($order_id)
    {
        $user = Auth::user();
        try {
            $this->alpaca->trade->deleteOrder($user->account_id, $order_id);
            $params['account_id'] = $user->account_id;
            $activities = $this->alpaca->account->getActivitiesByType('FILL',$params);
            return response()->json($activities);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }
    public function getOrders()
    {
        $user = Auth::user();
        try {
            $params['status'] = 'close';
            $openOrders = $this->alpaca->trade->getAllOrders($user->account_id, $params);
            return response()->json($openOrders);
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
        if(!$request->has('user_id'))
            return response()->json(['error' => 'The amount field is required.'], 500);

        try {
            $user = User::find($request->user_id);
            $params = $alpacaRepo->paramsForTransfer($user, $request->amount, 'INCOMING');

            $payment = $this->alpaca->funding->createTransferEntity($user->account_id, $params);
            return response()->json($payment);
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

            $payment = $this->alpaca->funding->createTransferEntity($user->account_id, $params);
            return response()->json($payment);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }

    public function getTransferHistory()
    {
        $user = Auth::user();
        try {
            $transfers = $this->alpaca->funding->getAllTransfersByAccount($user->account_id);
            return response()->json($transfers);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }

    public function getWatchList()
    {
        try {
            $user = Auth::user();
            $assets = [];
            if(isset($user->watchlist_id))
            {
                $watchlist = $this->alpaca->trade->getWatchlistById($user->account_id, $user->watchlist_id);
                $assets = $watchlist['assets'];
                $symbols = implode(',' , array_column($assets, 'symbol'));
                $quotes = $this->fmp->get_quote($symbols);
                foreach ($assets as $index => $asset) {
                    $idx = array_search($asset['symbol'], array_column($quotes, 'symbol'));
                    $assets[$index]['quote'] = $quotes[$idx];
                }
            }
            $watchlist['assets'] = $assets;
            return response()->json($watchlist);
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
                $watchlist = $this->alpaca->trade->createWatchlist($user->account_id, $params);
                $user->update(['watchlist_id' => $watchlist['id']]);
            } else {
                $watchlist = $this->alpaca->trade->addAssetsToWatchlist($user->account_id, $watchlistId, $request->symbol);
            }

            return response()->json($watchlist);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }

    public function removeAssetFromWatchList($symbol)
    {
        try {
            $user = Auth::user();
            $watchlist = $this->alpaca->trade->removeSymbolFromWatchlist($user->account_id, $user->watchlist_id, $symbol);
            return response()->json($watchlist);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }

    public function getTradingAccount()
    {
        try {
            $user = Auth::user();
            $account = $this->alpaca->account->getTradingAccount($user->account_id);
            return response()->json($account);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }

    public function getPortfolioHistory(Request $request)
    {
        try {
            $params = [
                'period' => $request->period,
                'timeframe' => $request->timeframe,
            ];
            $user = Auth::user();
            $portfolio = $this->alpaca->trade->getPortfolioHistories($user->account_id, $params);
            return response()->json($portfolio);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }

    public function getPositions()
    {
        try {
            $user = Auth::user();
            $positions = $this->alpaca->trade->getAllPositions($user->account_id);
            $symbols = implode(',' , array_column($positions, 'symbol'));
            $quotes = $this->fmp->get_quote($symbols);
            foreach ($positions as $index => $position) {
                $idx = array_search($position['symbol'], array_column($quotes, 'symbol'));
                $positions[$index]['name'] = $quotes[$idx]->name;
            }
            return response()->json($positions);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }

    public function getPosition($symbol)
    {
        try {
            $user = Auth::user();
            $position = $this->alpaca->trade->getOpenPosition($user->account_id, $symbol);

        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }

        return response()->json($position);
    }

    public function getQuote($symbol)
    {
        try {
            $user = Auth::user();
            $quotes = $this->fmp->get_quote($symbol);
            $quote = $quotes[0];
            $quote->isFavourite = false;
            if(isset($user->watchlist_id)) {
                $watchlist = $this->alpaca->trade->getWatchlistById($user->account_id, $user->watchlist_id);
                $assets = $watchlist['assets'];
                foreach($assets as $asset) {
                    if($asset['symbol'] == $quote->symbol)
                        $quote->isFavourite = true;
                }
            }
            $company = $this->fmp->get_company($symbol);
            $quote->company = $company;
            return response()->json($quote);

        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }

    public function getActivities()
    {
        try {
            $user = Auth::user();
            $params['account_id'] = $user->account_id;
            $activities = $this->alpaca->account->getActivitiesByType('FILL',$params);
            $openOrders = $this->alpaca->trade->getAllOrders($user->account_id);
            foreach ($openOrders as $order) {
                $order['transaction_time'] = $order['created_at'];
                $order['order_status'] = $order['status'];
                array_push($activities, $order);
            }
            return response()->json($activities);

        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }

    public function getMarketDataBars(Request $request, $symbol)
    {
        try {
            $params = [
                'timeframe' => $request->timeframe,
                'start' => $request->start
            ];
            $data = $this->alpacaMarket->stocks->historicalBars($symbol, $params);
            return response()->json($data);

        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }

    public function getAchRelationships()
    {
        $user = Auth::user();
        $accountId = $user->account_id;

        try {
            $res = $this->alpaca->funding->getAchRelationships($accountId);
            return response()->json($res);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }
    public function deleteBank()
    {
        /**
         * @var \App\Models\User
         */
        $user = Auth::user();
        $accountId = $user->account_id;
        if(isset($user->bank)) {

            $relationId = $user->bank->relation_id;

            try {
                $this->alpaca->funding->deleteAchRelationship($accountId, $relationId);
                Bank::where('relation_id', $relationId)->delete();
                // $user->update(['bank_linked' => false]);
                return response()->json(['success' => true]);
            } catch (\Throwable $th) {
                return response()->json(['error' => $th->getMessage()], 500);
            }
        } else {
            return response()->json(['error' => "No connected bank."], 500);
        }
    }
    public function deleteAchRelationship(Request $request)
    {
        /**
         * @var \App\Models\User
         */
        $user = Auth::user();
        $accountId = $user->account_id;
        $relationId = $request->relation_id;

        try {
            $this->alpaca->funding->deleteAchRelationship($accountId, $relationId);
            return response()->json(['success' => true]);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }
    public function checkAccounts(AlpacaRepository $alpacaRepo)
    {
        try {

            $accounts = $this->alpaca->account->getAll();
            foreach ($accounts as $account) {
                try {
                    $user = User::where('account_id', $account['id'])->first();
                    $status = $account['status'];
                    if(isset($user) && $status != $user->account_status) {
                        $message = $alpacaRepo->descriptionForAccountStatus($status);
                        (new Larafirebase)->withTitle('Account Status')
                            ->withBody($message)
                            ->sendMessage($user->device_token);
                        $user->update(['account_status' => $status]);
                    }

                } catch (\Throwable $th) {
                    Log::error('FCM error => '.$th->getMessage());
                }
            }
            // return response()->json($accounts);
        } catch (\Throwable $th) {
            // return response()->json(['error' => $th->getMessage()], 500);
        }
    }
}
