<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Traits\GPlayService;
use Illuminate\Http\Request;
use App\Http\Controllers\Traits\HelloDemo;
use App\Models\Player\Member;
use App\Models\Merchant\Hyperlink;

class DemoController extends Controller
{
    public function pgsoft(Request $request) {
        $merchantCode = strtolower($request->admin->code);
        return view('front.game.pgsoft', ['id' => $merchantCode]);
    }

    public function gplay(Request $request) {
        $merchantCode = strtolower($request->admin->code);
        return view('front.game.gplay', ['id' => $merchantCode]);
    }
    /**
     * GF RC遊戲測試工具
     *
     * @return void
     */
    public function pgDemo (Request $request) {
        $merchantCode = strtoupper($request->input('m_code'));
        $uLineID = $request->input('u_id');
        $userModel = Member::where('user_line_id', $uLineID)->first();
        if (empty($userModel)) {
            $link = Hyperlink::where(['merchant_code' => $merchantCode, 'title' => '掃碼支付'])->first();
            return response()->json(["code"=>"1104", "url" => "https://liff.line.me/{$link->liff_id}/?liff_id={$link->liff_id}"]);
        }
        $gameCode = $_POST['game_code']??"pg_74";
        $walletCode = $_POST['wallet_code']??"gf_main_balance";
        $createAccount = $_POST['create']??true;

        $service = new HelloDemo($userModel->account, $gameCode, $walletCode);

        if ($createAccount) {
            $service->createAccount();
        }

        $balance = $service->getBalance()['data']['balance'];
        if ((10000 - $balance) > 0) {
            $charge = 10000 - $balance;
            $result = $service->deposit($charge);
        }

        $ret = $service->gameLaunch();
        $gameURL = "https://public.pg-redirect.net/web-lobby/games/?ot=".HelloDemo::$pgToken."&ops=".$ret['data']['operator_player_session'];

        return response()->json(['code'=>"0", 'url'=>$gameURL]);
    }

    /**
     * GPlay Service
     *
     * @param Request $request
     */
    public function gPlayDemo (Request $request) {
        $merchantCode = strtoupper($request->input('m_code'));
        $uLineID = $request->input('u_id');
        $userModel = Member::where('user_line_id', $uLineID)->first();
        if (empty($userModel)) {
            $link = Hyperlink::where(['merchant_code' => $merchantCode, 'title' => '掃碼支付'])->first();
            return response()->json(["code"=>"1104", "url" => "https://liff.line.me/{$link->liff_id}/?liff_id={$link->liff_id}"]);
        }
        $gameCode = $_POST['game_code']??"xgd_lobby";

        $service = new GPlayService($merchantCode, $userModel->account, $gameCode);

        $ret = $service->gameLaunch();

        if ($ret["code"] === "0") {
            return response()->json(['code'=>"0", 'url'=>$ret["result"]["url"]]);
        }
        return response()->json($ret);
    }
}

// https://public.pg-redirect.net/web-lobby/games/?ot=c78f358882a97a6a972acaae682692c5&ops=VlZAV0RGVUNzfSxVVwBudnFNGhRT

