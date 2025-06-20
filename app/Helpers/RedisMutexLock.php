<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Redis;

class RedisMutexLock {

    /**
     * 获取lock最大 10s
     *
     * @var integer
     */
    public static $acquireTimeout = 10;

    protected static $lockPrefix = '';

    /**
     * 获取分布式锁（加锁）
     * @param lockKey 锁key
     * @param requestId 客户端请求标识
     * @param expireTime 超期时间,毫秒，默认10s
     * @param isNegative 是否是悲观锁，默认是
     * @return 是否获取成功
     */
    public static function lock($lockKey, $requestId, $expireTime = 10000, $isNegative = true)
    {
        $lockKey = static::$lockPrefix . $lockKey;
        if ($isNegative) {//悲观锁
            /**
             * 悲观锁 循环阻塞式锁取，阻塞时间为2s
             */
            $endtime = microtime(true) * 1000 + static::$acquireTimeout * 1000;
            while (microtime(true) * 1000 < $endtime) { //每隔一段时间尝试获取一次锁
                $acquired = Redis::set($lockKey, $requestId, 'PX', $expireTime, 'NX');
                if ($acquired) { //获取锁成功，返回true
                    return true;
                }
                usleep(100);
            }
            //获取锁超时，返回false
            return false;

        } else {//乐观锁
            /**
             * 乐观锁只尝试一次，成功返回true,失败返回false
             */
            $ret = Redis::set($lockKey, $requestId, 'PX', $expireTime, 'NX');
            if ($ret) {
                return true;
            }
            return false;
        }
    }

    /**
     * 解锁
     * @param $lockKey 锁key
     * @param $requestId 客户端请求唯一标识
     */
    public static function unlock($lockKey, $requestId)
    {
        $lockKey = static::$lockPrefix . $lockKey;
        $luaScript = <<<EOF
if redis.call("get",KEYS[1]) == ARGV[1]
then
    return redis.call("del",KEYS[1])
else
    return 0
end
EOF;
        $res = Redis::eval($luaScript, 1, $lockKey, $requestId);
        if ($res) {
            return true;
        }
        return false;
    }
}

?>
