<?php

namespace App\Libs;

use Exception;
use Illuminate\Support\Facades\Log;

class PlaidAPI
{
	/**
	 * Plaid API host environment.
	 *
	 * @var string
	 */
	private $environment;

	/**
	 * Plaid API version.
	 *
	 * @var string
	 */
	private $version;

	/**
	 * Plaid API environments and matching hostname.
	 *
	 * @var array<string,string>
	 */
	private $plaidEnvironments = [
		"production" => "https://production.plaid.com/",
		"development" => "https://development.plaid.com/",
		"sandbox" => "https://sandbox.plaid.com/",
	];

	/**
	 * Plaid API versions.
	 *
	 * @var array<string>
	 */
	private $plaidVersions = [
		"2017-03-08",
		"2018-05-22",
		"2019-05-29",
        "2020-09-14",
	];

	/**
	 * Plaid client Id.
	 *
	 * @var string
	 */
	private $client_id;

	/**
	 * Plaid client secret.
	 *
	 * @var string
	 */
	private $secret;

	/**
	 * PSR-18 ClientInterface instance.
	 *
	 * @var ClientInterface|null
	 */
	private $httpClient;

	/**
	 * Plaid client constructor.
	 *
	 * @param string|null $client_id
	 * @param string|null $secret
	 * @param string|null $environment
	 * @param string|null $version
	 */
	public function __construct($client_id=null, $secret=null, $environment=null, $version="2020-09-14")
	{
		$this->client_id = $client_id ?? config('plaid.client_id');
		$this->secret = $secret ?? config('plaid.secret_key');

		$this->setEnvironment($environment ?? config('plaid.mode'));
		$this->setVersion($version);
	}

	public function setEnvironment(string $environment): void
	{
		if( !\array_key_exists($environment, $this->plaidEnvironments) ){
			throw new Exception("Unknown or unsupported environment \"{$environment}\".");
		}

		$this->environment = $environment;
	}

	/**
	 * Get the current environment.
	 *
	 * @return string
	 */
	public function getEnvironment(): string
	{
		return $this->environment;
	}

	/**
	 * Set the Plaid API version to use.
	 *
	 * Possible values: "2017-03-08", "2018-05-22", "2019-05-29"
	 *
	 * @param string $version
	 * @throws PlaidException
	 * @return void
	 */
	public function setVersion(string $version): void
	{
		if( !\in_array($version, $this->plaidVersions) ){
			throw new Exception("Unknown or unsupported version \"{$version}\".");
		}

		$this->version = $version;
	}

	/**
	 * Get the current Plaid version.
	 *
	 * @return string
	 */
	public function getVersion(): string
	{
		return $this->version;
	}

	/**
	 * Get the specific environment host name.
	 *
	 * @param string $environment
	 * @return string|null
	 */
	private function getHostname(): ?string
	{
		return $this->plaidEnvironments[$this->environment] ?? null;
	}

	/**
	 * Build request body with client credentials.
	 *
	 * @param array<string, mixed> $params
	 * @return array
	 */
	private function clientCredentials(array $params = []): array
	{
		return \array_merge([
			"client_id" => $this->client_id,
			"secret" => $this->secret
		], $params);
	}

	private function call_curl($path, $params){

		$host = $this->getHostname();

		$url = $host.$path;

		$headers = ['Content-Type: application/json', 'Plaid-Version:'.$this->getVersion()];

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
		curl_setopt($ch, CURLOPT_TIMEOUT, 80);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

		if(!$result = curl_exec($ch)) {
		   trigger_error(curl_error($ch));
		}
		curl_close($ch);

		$jsonParsed = json_decode($result);

        if(isset($jsonParsed->error_code))
            throw new Exception($jsonParsed->error_message);

		return $jsonParsed;
	}

    /**
     * @param string $uuid
     *
     * @return string
     */
	public function createLinkToken($uuid){
		$params = [
            'user' => [
                'client_user_id' => $uuid
            ],
            'client_name' => config('app.name'),
		    'products' => ['auth', 'transactions'],
		    'country_codes' => ['US'],
		    'language' => 'en',
		];
		try {
			$jsonParsed = $this->call_curl("link/token/create", $this->clientCredentials($params));
            return $jsonParsed->link_token;

		} catch (\Throwable $th) {
			throw $th;
		}

	}


	private function createAccessToken($public_token){

		$params = [
		   'public_token' => $public_token,
		];
		try {
			$jsonParsed = $this->call_curl("item/public_token/exchange", $this->clientCredentials($params));
			return $jsonParsed->access_token;

		} catch (\Throwable $th) {
			throw $th;
		}

	}

	private function createStripeProcessorToken($accessToken, $account_id){

		$params = [
		   'access_token' => $accessToken,
		   'account_id' => $account_id
		];
		try {
			return $this->call_curl("processor/stripe/bank_account_token/create", $this->clientCredentials($params));

		} catch (\Throwable $th) {
			throw $th;
		}
	}

	private function createProcessorToken($accessToken, $account_id, $processor){

		$params = [
		   'access_token' => $accessToken,
		   'account_id' => $account_id,
           'processor' => $processor
		];
		try {
			return $this->call_curl("processor/token/create", $this->clientCredentials($params));

		} catch (\Throwable $th) {
			throw $th;
		}
	}

    /**
     * connect Plaid to Stripe. Get a stripe bank token from public token
     *
     * @param string $public_token
     * @param string $account_id
     *
     * @return string
     */
	public function connectPlaidToStripe($public_token, $account_id){

		try {
			$accessToken = $this->createAccessToken($public_token);

			$btok_parsed = $this->createStripeProcessorToken($accessToken->access_token, $account_id);

			return $btok_parsed->stripe_bank_account_token;

		} catch (\Throwable $th) {
			throw $th;
		}
	}

    /**
     * connect Plaid to a Partner. Get a processor token from public token
     *
     * @param string $public_token
     * @param string $account_id
     * @param string $processor
     *
     * @return string
     */
	public function connectPlaid($public_token, $account_id, $processor){

		try {
			$accessToken = $this->createAccessToken($public_token);

			$res = $this->createProcessorToken($accessToken, $account_id, $processor);

			return $res->processor_token;

		} catch (\Throwable $th) {
			throw $th;
		}
	}

}
