<?php

namespace App\Admin\Controllers\Traits;

use App\Helpers\OperatorLog;
use App\Models\Platform\Merchant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Xn\Admin\Facades\Admin;

trait LIFFSync
{
    /**
     * 取得LIFT-ID
     *
     * @return void
     */
    private function fetchLIFFId($url, $merchantCode, $name, $scanQR = false,
        $moduleMode = false, $size = 'full', $scopes = ['chat_message.write','openid','profile','email']) {
        $accessToken = $this->getLIFFAccessToken($merchantCode);
        $data = [
            "view" => [
                "type" => $size,
                "url" => $url,
                "moduleMode" => $moduleMode=='on'?true:false,
            ],
            "scope" => array_filter($scopes),
            "features" => [
                "ble" => false,
                "qrCode" => $scanQR=='on'?true:false,
            ],
            "botPrompt" => "aggressive",
            "description" => $name
        ];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://api.line.me/liff/v1/apps");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        // curl_setopt($ch, CURLOPT_POSTFIELDS, "{\n  \"view\":{\n \"type\":\"$type\",\n \"url\":\"$url\"\n  },
        // \n \"scope\": [\"profile\", \"chat_message.write\",\"openid\",\"email\" ],
        // \n \"features\": {\"ble\": false,\"qrCode\": true}, \"botPrompt\":\"aggressive\"}");
        curl_setopt($ch, CURLOPT_POST, 1);

        $headers = array();
        $headers[] = "Authorization: Bearer {$accessToken}";
        $headers[] = "Content-Type: application/json";
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            OperatorLog::insert(curl_error($ch), "", $merchantCode, __FUNCTION__);
        }
        OperatorLog::insert($result, "", $merchantCode, __FUNCTION__);
        curl_close ($ch);
        return json_decode($result, true);
    }

    /**
     * 更新LIFF-ID
     *
     * @return void
     */
    private function updateLIFFId($liffID, $url, $merchantCode, $name, $scanQR = false,
        $moduleMode = false, $size = 'full', $scopes = ['chat_message.write','openid','profile','email']) {
        $accessToken = $this->getLIFFAccessToken($merchantCode);
        $data = [
            "view" => [
                "type" => $size,
                "url" => $url,
                "moduleMode" => $moduleMode=='on'?true:false,
            ],
            "scope" => array_filter($scopes),
            "features" => [
                "ble" => false,
                "qrCode" => $scanQR=='on'?true:false,
            ],
            "botPrompt" => "aggressive",
            "description" => $name
        ];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://api.line.me/liff/v1/apps/{$liffID}");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        // curl_setopt($ch, CURLOPT_POSTFIELDS, "{\n  \"view\":{\n \"type\":\"$type\",\n \"url\":\"$url\"\n  },
        // \n \"scope\": [\"profile\", \"chat_message.write\",\"openid\",\"email\" ],
        // \n \"features\": {\"ble\": false,\"qrCode\": true}, \"botPrompt\":\"aggressive\"}");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');

        $headers = array();
        $headers[] = "Authorization: Bearer {$accessToken}";
        $headers[] = "Content-Type: application/json";
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            OperatorLog::insert(curl_error($ch), "", $merchantCode, __FUNCTION__);
        }
        OperatorLog::insert($result, "", $merchantCode, __FUNCTION__);
        curl_close ($ch);
        return json_decode($result, true);
    }
    /**
     * 取回所有LIFF
     *
     * @return void
     */
    private function getAllLIFF($merchantCode, $lineLoginChannelID, $lineLoginChannelSecret) {
        $accessToken = $this->getLIFFAccessToken($merchantCode, $lineLoginChannelID, $lineLoginChannelSecret);
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, "https://api.line.me/liff/v1/apps");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");


        $headers = array();
        $headers[] = "Authorization: Bearer {$accessToken}";
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            OperatorLog::insert(curl_error($ch), "", $merchantCode, __FUNCTION__);
        }
        OperatorLog::insert($result, "", $merchantCode, __FUNCTION__);
        curl_close ($ch);
        return json_decode($result, true);
    }
    /**
     * 移除 LIFF
     *
     * @param [type] $id
     * @return void
     */
    private function deleteLIFF($id, $merchantCode) {
        $accessToken = $this->getLIFFAccessToken($merchantCode);
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, "https://api.line.me/liff/v1/apps/{$id}");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");

        $headers = array();
        $headers[] = "Authorization: Bearer {$accessToken}";
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            OperatorLog::insert(curl_error($ch), "", $merchantCode, __FUNCTION__);
        }
        OperatorLog::insert($result, "", $merchantCode, __FUNCTION__);
        curl_close ($ch);
    }

    /**
     * 取得LIFFAccessToken
     * 有效30天
     *
     * @return string
     */
    private function getLIFFAccessToken($merchantCode) {

        if ($token = $this->verifyLIFFAccessToken($merchantCode)) {
            return $token;
        }

        $model = Merchant::class;
        $admin = $model::where('code', $merchantCode)->first();
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, "https://api.line.me/v2/oauth/accessToken");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $headers = array();
        $headers[] = "Content-Type: application/x-www-form-urlencoded";
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $form = [];
        $form['grant_type'] = 'client_credentials';
        $form['client_id'] = $admin->line_login_channel_id;
        $form['client_secret'] = $admin->line_login_channel_secret;
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($form));

        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            OperatorLog::insert(curl_error($ch), "", $admin->code, __FUNCTION__);
        }
        OperatorLog::insert($result, "", $admin->code, __FUNCTION__);
        curl_close ($ch);

        $data = json_decode($result, true);

        DB::table('sys_merchants')->where('code', $merchantCode)->update(['line_login_channel_access_token' => $data['access_token']??""]);

        return $data['access_token'] ?? false;
    }

    /**
     * 檢查LIFFAccessToken是否有效
     *
     * @return bool
     */
    private function verifyLIFFAccessToken($lineID) {
        $model = Merchant::class;
        $admin = $model::where('code', $lineID)->first();
        if (empty($admin->line_m_channel_access_token)) {
            return false;
        }
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, "https://api.line.me/v2/oauth/verify");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $headers = array();
        $headers[] = "Content-Type: application/x-www-form-urlencoded";
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $form = [];
        $form['access_token'] = $admin->line_m_channel_access_token;
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($form));

        $result = curl_exec($ch);

        if (curl_errno($ch)) {
            OperatorLog::insert(curl_error($ch), "", $admin->code, __FUNCTION__);
        }
        OperatorLog::insert($result, "", $admin->code, __FUNCTION__);
        curl_close ($ch);

        $data = json_decode($result, true);

        return isset($data['client_id']) ? false : $admin->line_m_channel_access_token;
    }
}
