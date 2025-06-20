<?php

namespace App\Http\Middleware;

use App\Admin\Controllers\AuthController;
use App\Helper\GlobalParam;
use Illuminate\Support\Facades\Cache;
use Closure;
use Illuminate\Http\Request;
use Xn\Admin\Facades\Admin;

class BoIPChecker
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
        $ip = $request->getClientIp();
        if (isset(Admin::user()->username)) {
            // if (!Admin::user()->inRoles(['administrator', 'supervisor', 'bot'])) {
                $comp = Admin::user()->belongToCompany()->first()??Admin::user()->companies()->first();
                $cacheKey = "company_{$comp->code}_ip";
                $compIP = Cache::remember($cacheKey, 600, function () use($comp) {
                    return explode(",", str_replace(" ", "", $comp->ip_whitelist));
                });
                if (!GlobalParam::IpContainChecker($ip, array_values($compIP))) {
                    $auth = new AuthController();
                    return $auth->getLogout($request, "IP Access denied: {$ip}");
                }
            // }
        }

        return $next($request);
    }
}
