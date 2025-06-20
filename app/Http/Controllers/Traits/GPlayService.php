<?php

namespace App\Http\Controllers\Traits;

use GuzzleHttp\Client;
use GuzzleHttp\TransferStats;

class GPlayService{

    static $lang = "zh";

    private $mCode = null;

    private $account = null;

    private $gameCode = null;

    function __construct($mCode, $account, $gameCode)
    {
        $this->mCode = $mCode;
        $this->account = $account;
        $this->gameCode = $gameCode;
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

        $response = $client->request('POST', env('GPLAY_HOST')."/m/game/launch", [
            'connect_timeout' => 10,
            'json' => [
                'm_code' => $this->mCode,
                'account' => $this->account,
                'launch_code' => $this->gameCode,
                'lang' => self::$lang
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
