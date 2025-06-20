<?php

namespace App\Helpers;

use App\Models\Merchant\Game;
use Illuminate\Support\Facades\Config;

class GlobalParam {

   static $oneDay = 3600 * 24;

   static $oneMinute = 1 * 60;

   /**
    * 建立交易DB連線
    *
    * @param string $dbName
    * @param string $dbConfig
    * @return string
    */
    public static function CreateTxDbConfig($dbConfig, $dbName = 'postgres') : string {
        $db = json_decode($dbConfig, true);
        $connName = ($dbName == 'postgres') ? $db['ip'] : $dbName;
        Config::set("database.connections.{$connName}", [
            'driver' => 'pgsql',
            'url' => null,
            'host' => $db['ip'],
            'port' => $db['port'],
            'database' => $dbName,
            'username' => $db['user'],
            'password' => $db['password'],
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
            'schema' => 'public',
            'sslmode' => 'prefer',
        ]);
        return $connName;
    }

    /**
     * 建立MongoDB連線
     *
     * @param string $dbConfig
     * @param string $dbName
     * @return string
     */
    public static function CreateMongoDbConfig($dbConfig, $dbName) : string {
        return static::CreateRepDbConfig($dbConfig, $dbName);
    }

   /**
    * 建立報表DB連線
    *
    * @param string $dbName
    * @param string $dbConfig
    * @return string
    */
    public static function CreateRepDbConfig($dbConfig, $dbName) : string {
        $db = json_decode($dbConfig, true);
        $connName = $dbName;
        Config::set("database.connections.{$connName}", [
            'driver' => 'mongodb',
            'host' => explode(",", $db['ip']),
            'port' => env('MONGODB_PORT', 27017),
            'database' => $dbName,
            'username' => "",
            'password' => "",
            'options' => [
                'replicaSet' => $db['rs'],
                'readPreference' => 'secondary',
                'database' => env('MONGODB_AUTHENTICATION_DATABASE', 'admin'), // required with Mongo 3+
            ],
        ]);
        return $connName;
    }

    /**
     * 檢查IP是否在
     *
     * @param $src
     * @param array $list
     * @return bool
     */
    public static function IpContainChecker($src, array $list): bool {
        $src = is_array($src) ? current($src) : $src;
        $src = str_ireplace(' ', '', $src);
        $ips = explode(',', $src);
        foreach ($ips as $ip) {
            if (in_array($ip, $list)) {
                return true;
            }
            foreach($list as $slashIp) {
                if ( strpos( $slashIp, '/' ) !== false ) {
                    if (ip_in_range($ip, $slashIp)===true) {
                        return true;
                    }
                }
            }
        }
        return false;
    }

    public static function AccountMask($account) {

        $len = strlen($account);
        if ($len > 4) {
            return "****".substr($account, -4);
        }

        return "****".$account;
    }

    public static function GameName($merchantCode, $lang) {
        return Game::filterGameName($merchantCode, $lang)->pluck('game_name', 'game_code')->toArray();
    }
}

?>
