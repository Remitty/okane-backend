<?php

namespace App\Repositories;

class AlpacaRepository
{
    /**
     * @param \App\Models\User $user
     */
    public function paramsForCreateAccount($user)
    {
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

        return $params;
    }

    /**
     * @param \App\Models\User $user
     */
    public function updateAccountToUser($user, $data)
    {
        $user->update([
            'account_id' => $data['id'],
            'account_number' => $data['account_number'],
            'account_status' => $data['status'],
            'account_currency' => $data['currency'],
            'account_type' => $data['account_type'],
        ]);
    }

    /**
     * @param \App\Models\User $user
     */
    public function paramsForTransfer($user, $amount, $direction)
    {
        $params = [
            'amount' => $amount,
            'direction' => $direction
        ];

        $bank = $user->bank();
        if($bank->type == 'ach') {
            $params = [
                'transfer_type' => 'ach',
                'relationship_id' => $bank->relation_id,
            ];
        } else { // wire
            $params = [
                'transfer_type' => 'wire',
                'bank_id' => $bank->relation_id,
            ];
        }

        return $params;
    }
}
