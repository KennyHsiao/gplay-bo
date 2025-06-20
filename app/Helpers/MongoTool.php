<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use MongoDB\Client;

class MongoTool
{
    /**
     * 建立代理 MongonDb index
     *
     * @param $mc_code 代理代碼
     * @param null $db_name 指定db name
     * @return bool
     */
    public static function CreateIndexs($ag_code, $db_name = null)
    {

        try {
            $db =  DB::table('sys_databases')->where([
                'agent_code' => $ag_code,
                'slug' => 'db'
            ])->first();

            $mongoIndex = json_decode($db->mongo_index, true);
            $repDb =  json_decode($db->rep_db, true);

            $client = new Client(
                "mongodb://{$repDb['ip']}/?replicaSet={$repDb['rs']}"
            );

            if ($db_name == null){
                $db_name = strtolower($ag_code).'_rep';
            }

            foreach ($mongoIndex as $v) {

                $col_name = key($v);

                // 建立collation不分大小寫
                if(isset($v[$col_name]['collation'])){

                    try {
                        $client->{$db_name}->createCollection($col_name, ['collation' => $v[$col_name]['collation']]);
                    }catch (\Throwable $th) {
                        Log::info($db_name." ".$col_name." createCollection error".$th->getMessage());
                    }

                }

                // 重組index格式
                $index = self::format($v[$col_name]['index_list']);

                try {
                    $client->{$db_name}->{$col_name}->createIndexes($index);
                }catch (\Throwable $th) {
                    Log::info($db_name." ".$col_name." createIndexes error".$th->getMessage());
                }

            }

            // agent_db
            $agnetIndex = json_decode($db->mongo_index, true);
            $agentSetting =  json_decode($db->rep_db, true);
            $agentClient = new Client(
                "mongodb://{$agentSetting['ip']}/?replicaSet={$agentSetting['rs']}"
            );

            // collection exists
            $listCols = $agentClient->agent_db->listCollections();
            $cols = [];
            foreach ($listCols as $v) {
                $cols[] = $v->getName();
            }

            foreach ($agnetIndex as $v) {

                $col_name = key($v);
                $agentCol = strtolower($ag_code)."_".$col_name;

                if (!in_array($agentCol, $cols)) {
                    // 建立collation不分大小寫
                    if(isset($v[$col_name]['collation'])){
                        try {
                            $agentClient->agent_db->createCollection($agentCol, ['collation' => $v[$col_name]['collation']]);
                        }catch (\Throwable $th) {
                            Log::info("agent_db ".$agentCol." createCollection error".$th->getMessage());
                        }


                    }

                    // 重組index格式
                    $index = self::format($v[$col_name]['index_list']);
                    try {
                        $agentClient->agent_db->{$agentCol}->createIndexes($index);
                    }catch (\Throwable $th){
                        Log::info("agent_db ".$agentCol." createIndexes error".$th->getMessage());
                    }

                }

            }

            return true;

        }catch (\Throwable $th){
            Log::info($th->getMessage());
            return false;
        }

    }

    /**
     * 指定建立單一collection
     *
     * @param $ag_code 代理代碼
     * @param $col_name 指定 collection name
     * @param null $db_name 指定db name
     * @return bool
     */
    public static function CreateIndexByAsign($ag_code, $col_name , $db_name = null)
    {

        try {
            $db =  DB::table('sys_databases')->where([
                'agent_code' => $ag_code,
                'slug' => 'db'
            ])->first();

            $mongoIndex = \Arr::collapse(json_decode($db->mongo_index, true));
            $repDb =  json_decode($db->rep_db, true);

            $client = new Client(
                "mongodb://{$repDb['ip']}/?replicaSet={$repDb['rs']}"
            );

            if ($db_name == null){
                $db_name = strtolower($ag_code).'_rep';
            }

            if(isset($mongoIndex[$col_name])){

                // 建立collation不分大小寫
                if(isset($mongoIndex[$col_name]['collation'])){
                    $client->{$db_name}->createCollection($col_name, ['collation' => $mongoIndex[$col_name]['collation']]);
                }

                $index = self::format($mongoIndex[$col_name]['index_list']);
                $client->{$db_name}->{$col_name}->createIndexes($index);

                return true;
            }
        }catch (\Throwable $th){
            Log::info($th->getMessage());
            return false;
        }

        return false;
    }

    private static function format($index_list) {
        $index =[];
        foreach ( $index_list as $i) {

            switch ($i) {
                case key_exists('unique', $i):
                    $arr = ['unique' => $i['unique']];
                    unset($i['unique']);
                    $arr += ['key' => $i];
                    array_push($index, $arr);
                    break;
                case key_exists('expireAfterSeconds', $i):
                    $arr = ['expireAfterSeconds' => $i['expireAfterSeconds']];
                    unset($i['expireAfterSeconds']);
                    $arr += ['key' => $i];
                    array_push($index, $arr);
                    break;
                default:
                    array_push($index, ['key' => $i]);
            }
        }

        return $index;
    }

    public static function CreateMerchantView($ag_code, $db_name = null)
    {

        try {

            $db =  DB::table('sys_databases')->where([
                'agent_code' => $ag_code,
                'slug' => 'db'
            ])->first();

            $repDb =  json_decode($db->rep_db, true);

            $client = new Client(
                "mongodb://{$repDb['ip']}/?replicaSet={$repDb['rs']}"
            );

            $mongoViews = json_decode($db->mongo_view, true);

            if ($db_name == null){
                $db_name = strtolower($ag_code).'_rep';
            }

            foreach ($mongoViews as $v) {
                $colName = key($v);

                try {
                    $client->{$db_name}->command([
                        'create' => $colName."_view",
                        'viewOn' => $colName,
                        'pipeline' => $v[$colName],
                    ]);

                }catch (\Throwable $th) {
                    Log::info($db_name." ".$colName."_view error".$th->getMessage());
                    continue;
                }

            }
            return true;

        } catch (\Throwable $th) {
            Log::info([$th->getCode(), $th->getMessage(), $th->getLine()]);
        }

        return false;
    }

}
