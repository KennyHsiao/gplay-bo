<?php
namespace App\Http\Controllers\Traits;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

trait LINENotify {
    /**
     * LINE-Notify 服務訊息通知
     *
     * @return void
     */
    public function LineNotifySetting(Request $request) {

        # 若使用者已連動則進行取消連動作業
        $clientId = env('NOTIFY_CLIENT_ID');
        $callbackUrl = route('line-notify.callback', ['user_id' => Auth::user()['user_id']]);

        return view('front.auth.line-notify', compact('clientId', 'callbackUrl'));
    }

    /**
     * 註冊服務訊息通知
     *
     * @param [type] $user_id
     * @return void
     */
    public function LineNotifyCallback(Request $request) {
        $user_id = request()->get('user_id');
        $code = request()->get('code');
        $callbackUrl = route('line-notify.callback', ['user_id' => $user_id]);
        ### LINE Access Token ###
        $this->getNotifyAccessToken($user_id, $code, $callbackUrl);
        return redirect()->route('line-notify.setting');
    }

    /**
     * 取消服務通知
     *
     * @param [type] $access_token
     * @return void
     */
    public function LineNotifyRevoke(Request $request) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://notify-api.line.me/api/revoke');
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . Auth::user()['notify_access_token']
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        $output = curl_exec($ch);
        curl_close($ch);
        $output = json_decode($output, true);
        DB::statement("update users set notify_access_token = null where user_id='".Auth::user()['user_id']."'");
        return redirect()->route('line-notify.setting');
    }

    /**
     * 取得LINE Notify Access Token
     *
     * @param [type] $user_id
     * @param [type] $code
     * @param [type] $redirect_uri
     * @return void
     */
    private function getNotifyAccessToken($user_id, $code, $redirect_uri) {

        $apiUrl = "https://notify-bot.line.me/oauth/token";

        $params = [
            'grant_type' => 'authorization_code',
            'code' => $code,
            'redirect_uri' => $redirect_uri,
            'client_id' => env('NOTIFY_CLIENT_ID'),
            'client_secret' => env('NOTIFY_CLIENT_SECRET')
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
        DB::statement("update users set notify_access_token='{$token}' where user_id='{$user_id}'");
    }
}
