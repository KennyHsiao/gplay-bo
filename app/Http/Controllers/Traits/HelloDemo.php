<?php

namespace App\Http\Controllers\Traits;

use GuzzleHttp\Client;
use GuzzleHttp\TransferStats;

class HelloDemo{

    static $pgToken = "ca1ce7ed8e34cea858aa311f9f14449f";
    static $opToken = "c78f358882a97a6a972acaae682692c5";
    static $opSecret = "c07b0b4d7660314f711a68fc47c4ab38";

    private $account = null;

    private $gameCode = null;

    private $walletCode = null;

    function __construct($account, $gameCode , $walletCode)
    {
        $this->account = $account;
        $this->gameCode = $gameCode;
        $this->walletCode = $walletCode;
    }

    public function createAccount() {
        $client = new Client([
            'verify' => false,
            'curl' => [
                // CURLOPT_SSL_CIPHER_LIST => 'DEFAULT@SECLEVEL=1'
            ]
        ]);

        $response = $client->request('POST', 'https://test.gf-gaming.com/gf/Player/Create', [
            'connect_timeout' => 10,
            'json' => [
                'operator_token' => self::$opToken,
                'secret_key' => self::$opSecret,
                'player_name' => $this->account
            ],
            'on_stats' => function(TransferStats $stats) {
                if ($stats->hasResponse()) {
                } else {
                    \Log::info("error:");
                    \Log::info($stats->getHandlerErrorData());
                }
            }
        ]);

        return json_decode($response->getBody(), true);
    }

    /**
     * Game Launch
     *
     * @return string
     */
    public function gameLaunch() {
        $client = new Client([
            'verify' => false,
            'curl' => [
                // CURLOPT_SSL_CIPHER_LIST => 'DEFAULT@SECLEVEL=1'
            ]
        ]);

        $response = $client->request('POST', 'https://test.gf-gaming.com/gf/Launch', [
            'connect_timeout' => 10,
            'json' => [
                'operator_token' => self::$opToken,
                'secret_key' => self::$opSecret,
                'game_code' => $this->gameCode,
                'player_name' => $this->account
            ],
            'on_stats' => function(TransferStats $stats) {
                if ($stats->hasResponse()) {
                } else {
                    \Log::info("error:");
                    \Log::info($stats->getHandlerErrorData());
                }
            }
        ]);

        return json_decode($response->getBody(), true);
    }

    /**
     * Get Balance
     *
     * @return float
     */
    public function getBalance() {
        $client = new Client([
            'verify' => false,
            'curl' => [
                // CURLOPT_SSL_CIPHER_LIST => 'DEFAULT@SECLEVEL=1'
            ]
        ]);

        $response = $client->request('POST', 'https://test.gf-gaming.com/gf/GetPlayerBalance', [
            'connect_timeout' => 10,
            'json' => [
                'operator_token' => self::$opToken,
                'secret_key' => self::$opSecret,
                'game_code' => $this->gameCode,
                'player_name' => $this->account,
                'wallet_code' => $this->walletCode
            ],
            'on_stats' => function(TransferStats $stats) {
                if ($stats->hasResponse()) {
                } else {
                    \Log::info("error:");
                    \Log::info($stats->getHandlerErrorData());
                }
            }
        ]);

        return json_decode($response->getBody(), true);

    }

    /**
     * Deposit Balance
     *
     * @return float
     */
    public function deposit(float $charge) {
        $client = new Client([
            'verify' => false,
            'curl' => [
                // CURLOPT_SSL_CIPHER_LIST => 'DEFAULT@SECLEVEL=1'
            ]
        ]);

        $response = $client->request('POST', 'https://test.gf-gaming.com/gf/TransferIn', [
            'connect_timeout' => 10,
            'json' => [
                'operator_token' => self::$opToken,
                'secret_key' => self::$opSecret,
                'amount' => $charge,
                'traceId' => uniqid($this->account),
                'player_name' => $this->account,
                'wallet_code' => $this->walletCode
            ],
            'on_stats' => function(TransferStats $stats) {
                if ($stats->hasResponse()) {
                } else {
                    \Log::info("error:");
                    \Log::info($stats->getHandlerErrorData());
                }
            }
        ]);

        return json_decode($response->getBody(), true);
    }
}
