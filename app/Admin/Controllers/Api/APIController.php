<?php


namespace App\Admin\Controllers\Api;


use App\Helpers\GlobalParam;
use App\Http\HttpResponse\RespData;
use App\Http\HttpResponse\RespState;
use App\Models\GameManage\GameVendor;
use App\Models\Player\Member;
use App\Models\Platform\Company;
use App\Models\System\Database;
use App\Services\TransactionService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Xn\Admin\Facades\Admin;

class APIController extends Controller
{

    /**
     * 代理IP
     *
     * @param Request $request
     * @return void
     */
    public function companyIP(Request $request) {
        $id = $request->get('q');
        return json_encode(
            [
                'ip' => Company::find($id)->ip_whitelist
            ]);
    }

    /**
     * 產生不重複公司別代碼
     *
     * @param Request $request
     */
    public function getUniqueCompanyCode(Request $request) {
        // $type = $request->get('q');
        $len = 3;
        gen:
        $code = strtoupper(Str::random($len));
        if (is_numeric($code[0])) {
            goto gen;
        }
        $m = DB::table('sys_companies')->where([
            'code' => $code
        ])->first();
        if ($m) {
            goto gen;
        }
        return json_encode(
            [
                'code' => $code
            ]
        );
    }

    /**
     * 搜寻玩家
     *
     * @param Request $request
     * @return void
     */
    public function player(Request $request) {
        $q = $request->get('q');
        return Member::where('account', 'like', "%$q%")->paginate(null, ['account as id', 'account as text']);
    }

    /**
     * 帳號認證
     *
     * @param Request $request
     */
    public function confirmAuth(Request $request) {
        $authCode = $request->get('auth_code');

        // check auth method
        $userModel = config('admin.database.users_model');
        $user = (new $userModel)->select('auth_method', 'password', 'google2fa_secret')->where('username', Admin::user()->username)->first();
        $user->auth_method = 'password';
        switch ($user->auth_method) {
            case 'none':
            case 'password':
                if (Hash::check($authCode, $user->password)) {
                    return response()->json([
                        'code' => 200,
                        'data' => [
                            'text' => __('Auth success'),
                        ]
                    ]);
                }
                break;
            case 'otp':
                $google2fa = (new \PragmaRX\Google2FAQRCode\Google2FA());
                if ($google2fa->verifyKey($user->google2fa_secret, $authCode)) {
                    return response()->json([
                        'code' => 200,
                        'data' => [
                            'text' => __('Auth success'),
                        ]
                    ]);
                }
                break;
        }
        return response()->json([
            'code' => 403,
            'data' => [
                'text' => __('Auth fail'),
            ]
        ]);
    }

    /**
     * 密鑰認證
     *
     * @param Request $request
     */
    public function confirmAuthX(Request $request) {
        $authCode = $request->get('auth_code');
        // check auth method
        $userModel = config('admin.database.users_model');
        $user = (new $userModel)->select('secret_key')->where('username', Admin::user()->username)->first();
        if (Hash::check($authCode, $user->secret_key)) {
            return response()->json([
                'code' => 200,
                'data' => [
                    'text' => __('Auth success'),
                ]
            ]);
        }
        return response()->json([
            'code' => 403,
            'data' => [
                'text' => __('Auth fail'),
            ]
        ]);
    }

    private function checkApiPass($password) {
        return Hash::check($password, Admin::user()->secret_key);
    }

    public function transferIn(Request $request)
    {
        $params = $request->all();

        $validator = Validator::make($params, [
            'merchant_code' => 'required',
            'account' => 'required',
            'amount' => 'required',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return (new RespData(RespState::InvalidRequestParam, __('參數錯誤')))->toJson();
        }

        if (!$this->checkApiPass($params['password'])) {
            return (new RespData(RespState::InvalidPassword, __('密碼錯誤')))->toJson();
        }

        $connName = $this->initDB();
        $member = DB::connection($connName)->table('members')
            ->where('merchant_code', $params['merchant_code'])
            ->where('account', $params['account'])->first();

        if (empty($member)) {
            return (new RespData(RespState::MemberNotExist, __('使用者不存在')))->toJson();
        }
        if ($member->status != 1) {
            return (new RespData(RespState::MemberStatusFail, __('使用者狀態異常')))->toJson();
        }

        return TransactionService::transferIn(
            $params['merchant_code'],
            $params['account'],
            $params['amount'],
            'bo'
        );
    }


    public function transferOut(Request $request)
    {
        $params = $request->all();

        $validator = Validator::make($params, [
            'merchant_code' => 'required',
            'account' => 'required',
            'amount' => 'required',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return (new RespData(RespState::InvalidRequestParam, __('參數錯誤')))->toJson();
        }

        if (!$this->checkApiPass($params['password'])) {
            return (new RespData(RespState::InvalidPassword, __('密碼錯誤')))->toJson();
        }

        $connName = $this->initDB();
        $member = DB::connection($connName)->table('members')
            ->where('merchant_code', $params['merchant_code'])
            ->where('account', $params['account'])->first();

        if (empty($member)) {
            return (new RespData(RespState::MemberNotExist, __('使用者不存在')))->toJson();
        }
        if ($member->status != 1) {
            return (new RespData(RespState::MemberStatusFail, __('使用者狀態異常')))->toJson();
        }

        return TransactionService::transferOut(
            $params['merchant_code'],
            $params['account'],
            $params['amount'],
            $params['memo'] ?? 'bo'
        );

    }

    public function memberTransaction()
    {
        return TransactionService::memberTransaction(
            "37d7f67e-20e1-443a-bdb0-d555ad4363bb",
            "U3b4a309aaecaa9460c3aec605336de62",
            "37d7f67e-20e1-443a-bdb0-d555ad4363bb",
            "U1ab1d8c6e6771dea30ddb7303fa801b0",
            500,
             'ts'
        );
    }

    public function authCodeReset(Request $request)
    {
        $params = $request->all();
        $validator = Validator::make($params, [
            'merchant_code' => 'required',
            'account' => 'required',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return (new RespData(RespState::InvalidRequestParam, __('參數錯誤')))->toJson();
        }

        if (!$this->checkApiPass($params['password'])) {
            return (new RespData(RespState::InvalidPassword, __('密碼錯誤')))->toJson();
        }

        $connName = $this->initDB();
        // $member = DB::connection($connName)->table('members')
        //     ->where('merchant_code', $params['merchant_code'])
        //     ->where('account', $params['account'])->first();
        //
        // if (empty($member)) {
        //     return (new RespData(RespState::MemberNotExist, __('使用者不存在')))->toJson();
        // }

        // $member->auth_code = Hash::make($params['password']);
        // $member->auth_code = "";

        $res = DB::connection($connName)->table('members')
            ->where('merchant_code', $params['merchant_code'])
            ->where('account', $params['account'])
            ->update([
                'auth_code' => ''
            ]);

        if ($res) {
            return (new RespData(RespState::Success, __('Success')))->toJson();
        }

        return (new RespData(RespState::Fail, __('發生錯誤')))->toJson();

    }

    /**
     * 遊戲商配置
     *
     * @param Request $request
     */
    public function vendorSetting(Request $request) {
        $vendor = GameVendor::where('code', $request->input('code'))->first();
        return response()->json([
            'setting' => $vendor->params
        ]);
    }

    private function initDB()
    {
        $cacheKey = "database_".session('agent_code');
        $db = Cache::rememberForever($cacheKey, function () {
            return Database::where([
                'agent_code' => session('agent_code'),
                'slug' => 'db'
            ])->select('tx_db', 'rep_db')->first();
        });
        return GlobalParam::CreateTxDbConfig($db->tx_db, strtolower(session('agent_code')."_tx"));
    }
}
