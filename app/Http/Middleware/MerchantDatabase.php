<?php

namespace App\Http\Middleware;

use App\Helpers\GlobalParam;
use App\Helpers\JsonCache;
use App\Models\Platform\Merchant;
use App\Models\System\Database;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class MerchantDatabase
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (session()->has('merchant_code')) {

            $cacheKey = "database_".session('merchant_code');
            $db = JsonCache::rememberForever($cacheKey, function () {
                $agentDb = Database::where([
                    'agent_code' => session('agent_code'),
                    'slug' => 'db'
                ])->select('agent_code', 'tx_db', 'rep_db')->first();

                return $agentDb ? $agentDb->toArray() : null;
            });
            GlobalParam::CreateTxDbConfig($db['tx_db'], strtolower(session('agent_code')."_tx"));
            GlobalParam::CreateRepDbConfig($db['rep_db'], strtolower(session('agent_code')."_rep"));
            // 代理/商戶 RTP
            // $m = Cache::rememberForever("agentdb", function () {
            //     return Database::where([
            //         'agent_code' => session('agent_code'),
            //         'slug' => 'agent'
            //     ])->select('setting')->first();
            // });
            // GlobalParam::CreateRepDbConfig($m->setting, "agentdb");
            // 告警
            // $m = Cache::rememberForever("alertdb", function () {
            //     return Database::where([
            //         'agent_code' => session('agent_code'),
            //         'slug' => 'alert'
            //     ])->select('setting')->first();
            // });
            // GlobalParam::CreateRepDbConfig($m->setting, "alertdb");
        }

        if ($request->has('m_code')) {
            $mCode = strtoupper($request->input('m_code'));
            $cacheKey = "database_". $mCode;
            $db = JsonCache::rememberForever($cacheKey, function () use($mCode) {
                $m = Merchant::where([
                    'code' => $mCode,
                ])->select('agent_code')->first();

                $agentDb = Database::where([
                    'agent_code' => $m->agent_code,
                    'slug' => 'db'
                ])->select('agent_code', 'tx_db', 'rep_db')->first();

                return $agentDb ? $agentDb->toArray() : null;
            });
            session(['merchant_code' => $mCode]);
            session(['agent_code' => $db['agent_code']]);
            GlobalParam::CreateTxDbConfig($db['tx_db'], strtolower($db['agent_code']."_tx"));
        }

        return $next($request);
    }
}
