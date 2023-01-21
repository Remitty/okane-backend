<?php

namespace App\Repositories;

use App\Models\Bank;
use App\Models\BankMerchant;
use App\Models\Log;
use App\Models\Merchant;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class BankRepository
{

    public function getActiveBankToUser($userId = null)
    {
        if (is_null($userId))
        {
            $user = Auth::user();
        } else {
            $user = User::find($userId);
        }

        if(is_null($user->bank_id))
        {
            $bank = $this->bankAlgorithm($user->group_no, $user->email);
            $user->bank_id = $bank->id;
            $user->update();
        } else {
            $bank = Bank::find($user->bank_id);
        }

        return $bank;
    }

    public function assignActiveBankToUser($userId = null)
    {
        if (is_null($userId))
        {
            $user = Auth::user();
        } else {
            $user = User::find($userId);
        }

        $bank = $bank = $this->bankAlgorithm($user->group_no, $user->email);

        $user->bank_id = $bank->id;
        $user->update();

    }

    public function getGroupNo($merchant)
    {
        return intval($merchant->id / 100);
    }

    public function getAccountPosition($group_no, $email)
    {
        $unique_int = crc32($email);
        $positions = Bank::selectRaw('count(position_no) as position_cnt')
            ->where('is_active', true)
            ->where('group_no', $group_no)
            ->groupBy('position_no')->get();
        $total_positions = count($positions);
        if ($total_positions == 0 ) return -1;
        $account_position = $unique_int%$total_positions;

        return $account_position;
    }

    public function bankAlgorithm($group_no, $email)
    {
        $account_position = $this->getAccountPosition($group_no, $email);
        $bank = Bank::where('group_no', $group_no)
            ->where('position_no', $account_position)
            ->where('is_active', true)
            ->first();
        return $bank;
    }

    public function requestBank($email)
    {
        $merchant = Merchant::where('email', $email)->first();
        $bank = null;
        if(is_null($merchant->bank_id)) {
            $previousActiveBanks = $merchant->previousActiveBanks();
            if(count($previousActiveBanks) > 0) {
                $bank = $previousActiveBanks[0];
            } else {
                $group_no = $this->getGroupNo($merchant);
                $bank = $this->bankAlgorithm($group_no, $email);
            }
            if(isset($bank)) {
                $merchant->bank_id = $bank->id;
                $merchant->update();
                BankMerchant::updateOrCreate([
                    'merchant_id' => $merchant->id,
                    'bank_id' => $bank->id
                ],[
                    'merchant_id' => $merchant->id,
                    'bank_id' => $bank->id
                ]);
                Log::create(['content' => 'Assigned a bank to '.$email]);
            }
        } else {
            $bank = Bank::find($merchant->bank_id);
        }

        return $bank;
    }

}
