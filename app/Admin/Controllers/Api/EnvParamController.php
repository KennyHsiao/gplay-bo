<?php

namespace App\Admin\Controllers\Api;

// use App\Models\OperatorInternalMessage;

use App\Models\Platform\Company;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EnvParamController extends Controller
{
    /**
     * 切換營商
     *
     * @param Request $request
     * @return void
     */
    public function switchMerchant(Request $request) {
        $mCode = $request->input('merchant_code', '-');
        $merchant = Company::select('id','parent_id','code', 'name')->where('code', $mCode)->first();
        $agent_id = $merchant->parent_id??"";
        $agent = Company::select('code', 'name')->find($agent_id);
        session(['merchant' => $merchant]);
        session(['merchant_code' => $mCode]);
        session(['agent_code' => $agent->code??""]);
        //
        return response()->json([
            'status' => 'ok'
        ], 200);
    }

    /**
     * 切換時區
     *
     * @param Request $request
     * @return void
     */
    public function switchTimezone(Request $request) {
        $timezone = $request->input('timezone');
        session(['timezone' => $timezone]);
        //
        return response()->json([
            'status' => 'ok'
        ], 200);
    }

    /**
     * 切換語系
     *
     * @param Request $request
     * @return void
     */
    public function switchLang(Request $request) {
        $lang = $request->input('lang');
        if ($lang == "zh") {
            session(['lang' => $lang]);
            session(['locale' => $lang]);
            session(['full_lang' => "{$lang}_cn"]);
        } else {
            session(['lang' => $lang]);
            session(['locale' => $lang]);
            session(['full_lang' => $lang]);
        }

        return response()->json([
            'status' => 'ok'
        ], 200);
    }

    /**
     * 站內消息
     *
     * @param Request $request
     * @return void
     */
    public function internalMessage(Request $request) {
        // $messages = MerchantInternalMessage::where('read', 'N')->orderBy('timestamp', 'desc')->get();
        return response()->json([
            'message' => []
        ], 200);
    }

}
