<?php

namespace App\Http\Controllers\Traits;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

trait LINEAuth
{
    /**
     * 登入
     *
     * @return void
     */
    public function login(Request $request) {
        $client_id = $request->admin->line_login_channel_id;
        if (Auth::check()) {
            return redirect()->route('gpay.home', ['id' => $client_id]);
        }
        $uuid = md5(uniqid(rand(), true));
        $auth_url = "https://access.line.me/oauth2/v2.1/authorize?response_type=code&client_id=".$client_id.
                    "&redirect_uri=".route('line.auth', ['id' => $client_id])."&state={$uuid}&scope=openid%20profile%20email";

        return view('front.auth.login_signup', compact('auth_url'));
    }

    /**
     * LINE授權
     *
     * @return void
     */
    public function LINEAuth(Request $request){
        $code = request()->input('code');
        $state = request()->input('state');
        $client_id = $request->admin->line_login_channel_id;
        $client_secret = $request->admin->line_login_channel_secret;
        $redirect_uri = route('line.auth', ['id' => $client_id]);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://api.line.me/oauth2/v2.1/token");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, "grant_type=authorization_code&code=$code&redirect_uri=$redirect_uri&client_id=$client_id&client_secret=$client_secret");
        curl_setopt($ch, CURLOPT_POST, 1);

        $headers = array();
        $headers[] = "Content-Type: application/x-www-form-urlencoded";
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($ch);
        $result = json_decode($result, true);
        # LINE Login
        if (!isset($result['id_token'])) {
            return redirect()->route('auth.login', ['id' => $client_id]);
        }
        $profile = static::LINEProfile($result['id_token']);

        $user = User::updateOrCreate(
            [
                'user_id' => $profile['user_id']
            ], [
                'name' => $profile['name'],
                'picture' => $profile['picture'],
                'email' => ($profile['email']??''),
                'access_token' => $result['access_token'],
            ]
        );

        static::LINEUserProfile($result['access_token']);
        if ($user) {
            Auth::login($user, true);
            if (Auth::check()) {
                return redirect()->route('gpay.home', ['id' => $client_id]);
            }
        }
        #
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }
        curl_close ($ch);
    }

    /**
     * LINE登出
     *
     * @return void
     */
    public function LINELogout(Request $request) {
        $client_id = $request->admin->line_login_channel_id;
        $client_secret = $request->admin->line_login_channel_secret;
        $access_token = Auth::user()['access_token'];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://api.line.me/oauth2/v2.1/revoke");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, "client_id=$client_id&client_secret=$client_secret&access_token=$access_token");
        curl_setopt($ch, CURLOPT_POST, 1);

        $headers = array();
        $headers[] = "Content-Type: application/x-www-form-urlencoded";
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($ch);
        # 登出
        if(empty($result)) {
            Auth::logout();
            return redirect()->route('gpay.home', ['id' => $client_id]);
        }

        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }
        curl_close ($ch);
    }

    /**
     * 解析idToken
     *
     * @param [type] $idToken
     * @return void
     */
    public static function LINEProfile($idToken) {

        $token = static::jwtDecode($idToken); // Parses from a string

        return [
            'url' => $token->iss,
            'user_id' => $token->sub,
            'name' => $token->name,
            'picture' => $token->picture,
            'email' => $token->email??""
        ];
    }

    /**
     * Undocumented function
     *
     * @param [type] $access_token
     * @return void
     */
    public static function LINEUserProfile($access_token) {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, "https://api.line.me/v2/profile");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");


        $headers = array();
        $headers[] = "Authorization: Bearer $access_token";
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($ch);
        // \Log::info($result);
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }
        curl_close ($ch);
    }

    /**
     * 解析 JWT
     */
    private static function jwtDecode($str) {
        list($header, $payload, $signature) = explode('.', $str);
        $payload = strtr($payload, '-_', '+/');
        return json_decode(base64_decode($payload));
    }
}
