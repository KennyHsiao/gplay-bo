<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Redis;

class JsonCache
{
    public static function rememberForever($key, $callback)
    {
        $redis = Redis::connection('cache');
        $value = $redis->get($key);

        if ($value !== null) {
            return json_decode($value, true);
        }

        $value = $callback();
        $redis->set($key, json_encode($value));

        return $value;
    }
}
