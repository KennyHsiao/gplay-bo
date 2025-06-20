<?php

namespace App\Http\Controllers;

use App\Helpers\GCache;
use App\Helpers\RedisMutexLock;
use App\Http\Controllers\Traits\LINEAuth;
use App\Http\Controllers\Traits\LINENotify;
use App\Models\Player\Member;
use App\Services\TransactionService;
use chillerlan\QRCode\QRCode;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class GPayController extends Controller
{
    use LINEAuth, LINENotify;

    /**
     * LINE LIFF 認證 / 加載進度畫面
     *
     * @param Request $request
     */
    public function home(Request $request) {
        $merchantCode = strtolower($request->admin->code);
        $userLineID =  $request->input('userId');
        $displayName = $request->input('displayName');
        $statusMessage = $request->input('statusMessage');
        $pictureUrl = $request->input('pictureUrl', env('APP_URL')."/static/avatar.jpg");
        $liffID = $request->input('liff_id');

        $user = Member::where(['merchant_code' => $request->admin->code, 'user_line_id' => $userLineID])->first();


        if (empty($userLineID)) {
            return view('front.gpay.home', [
                'id' => $merchantCode,
            ]);
        }

        if ($user && !empty($user->auth_code)) {
            $qrCode = (new QRCode)->render(base64_encode(json_encode([
                'account' => $user->account,
                'time' => microtime()
            ])));
            return view('front.gpay.home', [
                'id' => $merchantCode,
                'account' => $user->account,
                'display_name' => $displayName,
                'status_message' => $statusMessage,
                'picture_url' => $pictureUrl,
                'balance' => $user->balance,
                'qr_code' => $qrCode
            ]);
        }

        return view('front.gpay.reg', [
            'id' => $merchantCode,
            'user_line_id' => $userLineID,
            'display_name' => $displayName,
            'status_message' => $statusMessage,
            'picture_url' => $pictureUrl,
            'liff_id' => $liffID
        ]);
    }

    /**
     * 寫入註冊資料
     *
     * @param Request $request
     */
    public function postReg(Request $request) {
        $validator = Validator::make(request()->all(), [
            'user_line_id' => 'required',
            'display_name' => 'required',
            'auth_code' => 'required|min:4|max:10'
        ]);

        if ($validator->fails()) {
            return back()->withInput()->withErrors($validator->errors());
        }

        $account = $this->getUniqueUserAccount($request->admin->code);

        Member::updateOrCreate([
            'merchant_code' => $request->admin->code,
            'user_line_id' => $request->input('user_line_id')
        ],[
            'account' => $account,
            'display_name' => $request->input('display_name'),
            'status_message' => $request->input('status_message', ''),
            'picture_url' => $request->input('picture_url', env('APP_URL')."/static/avatar.jpg"),
            'auth_code' => bcrypt($request->input('auth_code'))
        ]);

        $liffID = $request->input('liff_id');

        return redirect()->route('gpay.home', [
            'id' => strtolower($request->admin->code),
            'liff_id' => $liffID,
            'account' => $account,
            'display_name' => $request->input('display_name'),
            'status_message' => $request->input('status_message', ''),
            'picture_url' => $request->input('picture_url', env('APP_URL')."/static/avatar.jpg"),
        ]);
    }

    /**
     * 支付畫面
     *
     * @param Request $request
     */
    public function pay(Request $request) {
        $validator = Validator::make(request()->all(), [
            'liff_id' => 'required',
            'account' => 'required',
            'from_account' => 'required',
        ]);

        if ($validator->fails()) {
            return view('front.gpay.error', config('code.error.9001'));
        }
        // 檢查商戶狀態 // 是否允許交易
        $check = $this->merchantSwitchCheck($request->admin, $request->input('account'));
        if ($check === true) {
            $fromUser = $this->memberCheck($request->input('from_account'));
            $destUser = $this->memberCheck($request->input('account'));
            if (!($fromUser instanceof Member)) {
                return $fromUser;
            }

            if (!($destUser instanceof Member)) {
                return $destUser;
            }

            if ($fromUser->account == $destUser->account) {
                return view('front.gpay.error', config('code.error.1001'));
            }

            $txCode = uniqid("tx");
            $ret = Redis::set($txCode, $fromUser->account, 'PX', 60000, 'NX');
            if (!$ret) {
                return view('front.gpay.error', config('code.error.9002'));
            }

            return view('front.gpay.pay', [
                'id' => strtolower($request->admin->code),
                'liff_id' => $request->input('liff_id'),
                'code' => $txCode,
                'account' => $destUser->account,
                'name' => $destUser->display_name,
                'pic' => $destUser->picture_url,
                'from_account' => $fromUser->account,
            ]);
        }

        return $check;
    }


    public function postPay(Request $request) {
        $validator = Validator::make(request()->all(), [
            'liff_id' => 'required',
            'code' => 'required',
            'account' => 'required',
            'from_account' => 'required',
            'auth_code' => 'required',
            'amount' => 'required|numeric|min:1'
        ]);

        if ($validator->fails()) {
            // return back()->withInput()->withErrors($validator->errors());
            return view('front.gpay.error', config('code.error.9001'));
        }

        $txCode = Redis::get($request->input('code'));

        if (empty($txCode) || $txCode !== $request->input('from_account')) {
            return view('front.gpay.error', config('code.error.1301'));
        }

        $fromUser = $this->memberCheck($request->input('from_account'));
        if (!($fromUser instanceof Member)) {
            return $fromUser;
        }

        if (Hash::check($request->input('auth_code'), $fromUser->auth_code)) {
            try {
                $ret = TransactionService::memberTransaction(
                    $request->input('from_account'),
                    $request->input('account'),
                    floatval($request->input('amount')),
                );

                if ($ret['code'] === "0") {
                    return view('front.gpay.pay_success', compact('ret'));
                }
                return view('front.gpay.error', $ret);
            } catch (Exception $e) {
                Log::info($e->getMessage());
                return view('front.gpay.error', config('code.error.1301'));
            }
        }

        return view('front.gpay.error', config('code.error.1107'));

    }

    /**
     * 會員狀態檢查
     *
     * @param [type] $account
     */
    private function memberCheck($account, $dst = false) {
        $user = Member::where(['account' => $account])->first();
        if (empty($user)) {
            if ($dst === false) {
                return view('front.gpay.error', config('code.error.1104'));
            } else {
                return view('front.gpay.error', config('code.error.1204'));
            }
        }

        if ($user->status !== "1") {
            if ($dst === false) {
                return view('front.gpay.error', config('code.error.1105'));
            } else {
                return view('front.gpay.error', config('code.error.1205'));
            }
        }

        return $user;
    }

    /**
     * 檢查交易商戶
     *
     * @param [type] $merchant
     */
    private function merchantSwitchCheck($merchant, $dstAccount) {
        $dstMerchantCode = strtoupper(substr($dstAccount, 0, 3));
        $dstMerchant = GCache::merchantSwitch($dstMerchantCode);

        $status = $merchant->status;
        $transfer = $merchant->switch_transfer;
        $departure = $merchant->switch_departure;
        if ($status !== '1') {
            switch ($status) {
                case '0' :
                    return view('front.gpay.error', config('code.error.1101'));
                case '2' :
                    return view('front.gpay.error', config('code.error.1102'));
            }
        }
        // 禁止轉帳
        if ($transfer !== '1') {
            return view('front.gpay.error', config('code.error.1103'));
        }

        // 禁止跨商戶轉帳
        if ($departure !== '1') {
            if ($merchant->code !== $dstMerchantCode) {
                return view('front.gpay.error', config('code.error.1000'));
            }
        }

        $status = $dstMerchant->status;
        $transfer = $dstMerchant->switch_transfer;
        $departure = $dstMerchant->switch_departure;
        if ($status !== '1') {
            switch ($status) {
                case '0' :
                    return view('front.gpay.error', config('code.error.1201'));
                case '2' :
                    return view('front.gpay.error', config('code.error.1202'));
            }
        }
        // 禁止轉帳
        if ($transfer !== '1') {
            return view('front.gpay.error', config('code.error.1203'));
        }

        return true;
    }

    /**
     * 產生不重複玩家帳號
     *
     */
    public function getUniqueUserAccount($prefix) {
        $len = 7;
    gen:
        $gID = Str::random(10);
        RedisMutexLock::lock('gen_account', $gID);
        try {
            $code = strtoupper(Str::random($len));
            if (is_numeric($code[0])) {
                goto gen;
            }
            $m = DB::table('line_bot_users')->where([
                'account' => $code
                ])->first();
            if ($m) {
                goto gen;
            }
            return strtolower("{$prefix}{$code}");
        } finally {
            RedisMutexLock::unlock('gen_account', $gID);
        }
    }
}
