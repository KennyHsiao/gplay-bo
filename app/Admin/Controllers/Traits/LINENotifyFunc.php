<?php

namespace App\Admin\Controllers\Traits;

# MODELS

use App\Models\Platform\Merchant;
use Illuminate\Support\Facades\DB;
use Xn\Admin\Facades\Admin;

trait LINENotifyFunc
{
    /**
     * LINE-Notify 取消服務訊息通知
     *
     * @return void
     */
    public function lineNotifyCancel() {
        $userModel = config('admin.database.users_model');
        $username = request()->get('username');
        $admin = $userModel::where(['username'=>$username])->first();
        # 若使用者已連動則進行取消連動作業
        if (!empty($admin['line_notify_token'])) {
            $this->lineNotifyRevoke($username, $admin);
            session()->flash('status', __('解除連動'));
            return redirect(admin_url('auth/setting'));
        }

        return redirect(admin_url('auth/setting'));
    }

    /**
     * 註冊服務訊息通知
     *
     * @param [type] $store_id
     * @param [type] $user_id
     * @return void
     */
    public function lineNotifyCallback() {
        $username = request()->get('username');
        $code = request()->get('code');
        $callbackUrl = route('my.line-notify.callback', ['username' => $username]);
        DB::statement("update admin_users set line_notify_auth_code='{$code}' where username='{$username}'");
        ### LINE Access Token ###
        $this->getNotifyAccessToken($username, $code, $callbackUrl);
        session()->flash('status', __('連動完成!'));
        return redirect(admin_url('auth/setting'));
    }

    /**
     * 取消服務通知
     *
     * @param [type] $access_token
     * @return void
     */
    public function lineNotifyRevoke($username, $admin) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://notify-api.line.me/api/revoke');
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $admin['line_notify_token']
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        $output = curl_exec($ch);
        curl_close($ch);
        $output = json_decode($output, true);
        /**
         * {"status":200,"message":"ok"}
         */
        if (in_array($output['status'],[200,401])) {
            DB::statement("update admin_users set line_notify_token = null where username='{$username}'");
        }
        return $output;
    }

    /**
     * 取得LINE Notify Access Token
     *
     * @param [type] $store_id
     * @param [type] $user_id
     * @param [type] $code
     * @param [type] $redirect_uri
     * @return void
     */
    private function getNotifyAccessToken($username, $code, $redirect_uri) {
        $model = Merchant::class;
        $admin = $model::where(['code'=> Admin::user()->merchant_code])->first();

        $apiUrl = "https://notify-bot.line.me/oauth/token";

        $params = [
            'grant_type' => 'authorization_code',
            'code' => $code,
            'redirect_uri' => $redirect_uri,
            'client_id' => $admin->notify_client_id??config('admin.notify.line.client_id'),
            'client_secret' => $admin->notify_client_secret??config('admin.notify.line.client_secret')
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        $output = curl_exec($ch);
        curl_close($ch);
        /**
         * {
         *      "status": 200,
         *      "message": "access_token is issued",
         *      "access_token": "7giNDfFWoAO1trYBA34YyfA6IZmazQoF4rmWSqrTtb3"
         *  }
         */
        $result = json_decode($output, true);
        $token = $result['access_token'];
        DB::statement("update admin_users set line_notify_token='{$token}' where username='{$username}'");
    }
}
