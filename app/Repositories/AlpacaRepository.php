<?php

namespace App\Repositories;

use Exception;

class AlpacaRepository
{
    /**
     * @param \App\Models\User $user
     */
    public function paramsForCreateAccount($user)
    {
        $doc = '';

        try {
            $doc = $user->doc == null || $user->doc == '' ? '' : base64_encode(file_get_contents(get_file_link($user->doc)));
        } catch (\Throwable $th) {
            //throw $th;
        }
        $params = [
            "enabled_assets"=> ["us_equity", "crypto"],
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
            'documents' => [
                [
                    "document_type"=> "identity_verification",
                    // "document_sub_type"=> "passport",
                    "content"=> $doc,
                    "mime_type"=> "image/jpeg"
                ]
            ],
            'agreements' => [[
                'agreement' => 'customer_agreement',
                'signed_at' => today(),
                'ip_address' => $user->ip_address ?? ''
            ],
            [
                'agreement' => 'crypto_agreement',
                'signed_at' => today(),
                'ip_address' => $user->ip_address ?? ''
            ]],
            'currency' => $user->country_code == 'USA' ? "USD" : 'EUR'
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
    public function paramsForUpdateAccount($user)
    {
        $params = [
            "enabled_assets"=> ["us_equity", "crypto"],
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
            ]
        ];

        return $params;
    }

    /**
     * @param \App\Models\User $user
     */
    public function paramsForTransfer($user, $amount, $direction)
    {

        $bank = $user->bank;
        if(isset($bank)) {
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

            $params['amount'] = $amount;
            $params['direction'] = $direction;

            return $params;
        } else {
            throw new Exception("You have no connected bank. Please connect your bank.");
        }
    }

    public function descriptionForAccountStatus($status)
    {
        $description = '';
        switch ($status) {
            case 'INACTIVE':
                $description = "Account not enabled to trade equities.";
                break;
            case 'ONBOARDING':
                $description = "The account has been created but we haven’t performed KYC yet.";
                break;
            case 'SUBMITTED':
                $description = "Application has been submitted and in process of review.";
                break;
            case 'ACTION_REQUIRED':
                $description = "Application requires manual action.";
                break;
            case 'EDITED':
                $description = "Application was edited.";
                break;
            case 'APPROVAL_PENDING':
                $description = "Application approval process is in process.";
                break;
            case 'APPROVED':
                $description = "Account application has been approved, waiting to be active.";
                break;
            case 'REJECTED':
                $description = "Account application is rejected.";
                break;
            case 'ACTIVE':
                $description = "Equities account is fully active and can start trading.";
                break;
            case 'DISABLED':
                $description = "Account is disabled, comes after active.";
                break;
            case 'ACCOUNT_CLOSED':
                $description = "Account is closed.";
                break;

            default:
                $description = "Application has been submitted and in process of review.";
                break;
        }

        return $description;
    }
}
