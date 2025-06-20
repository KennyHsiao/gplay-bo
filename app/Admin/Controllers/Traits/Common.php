<?php

namespace App\Admin\Controllers\Traits;

use App\Models\System\AdminGlobalParam;
use Xn\Admin\Facades\Admin;
use Illuminate\Http\File;

trait Common
{
    static $radio_option = ['1' => '是', '0'=> '否'];

    static $switch_open = [
        'on'  => ['value' => 1, 'text' => '是', 'color' => 'success'],
        'off' => ['value' => 0, 'text' => '否', 'color' => 'warning'],
    ];

    static $switch_public = [
        'on'  => ['value' => 1, 'text' => '開啟', 'color' => 'primary'],
        'off' => ['value' => 0, 'text' => '關閉', 'color' => 'danger'],
    ];

    static $switch_hidden = [
        'on'  => ['value' => 1, 'text' => '顯示', 'color' => 'success'],
        'off' => ['value' => 0, 'text' => '隱藏', 'color' => 'warning'],
    ];

    static $gender = ['f'=>'女', 'm'=>'男'];

    static function formTransType(){

        if (Admin::user()->inRoles(['agent', 'merchant', 'cs'])) {
            return [
                'adjust' => __('調整'),
                'deposit' => __('儲值'),
                'withdraw' => __('提款'),
            ];
        }

        return [
            'adjust' => __('調整'),
            'bet' => __('下注'),
            'win' => __('派彩'),
            'deposit' => __('儲值'),
            'withdraw' => __('提款'),
            'rollout' => __('轉出-遊戲上分'),
            'rollin' => __('轉入-遊戲下分'),
        ];
    }

    static function switchWalletType() {
        return [
            'on'  => ['value' => 'seamless', 'text' => __('單一'), 'color' => 'success'],
            'off' => ['value' => 'transfer', 'text' => __('轉帳'), 'color' => 'warning'],
        ];
    }

    static function switchPublic() {
        return [
            '0' => __('關閉'),
            '1' => __('開啟')
        ];
    }

    static function supportLang() {
        return [
            'en' => '',
            'zh' => '',
            'tw' => ''
        ];
    }

    static function formGameState() {
        return [
            '0' => __('下架'),
            '1' => __('正常'),
            '2' => __('維護中'),
            '3' => __('敬請期待'),
        ];
    }

    static function formMerchantState() {
        return [
            '0' => __('下架'),
            '1' => __('正常'),
            '2' => __('維護中'),
        ];
    }

    static function formMerchantGameState() {
        return [
            '0' => __('下架'),
            '1' => __('正常')
        ];
    }

    static function formSwitchLocale($type = 'public') {
        $data = [
            'radio_option' => [
                '1' => __('是'),
                '0' => __('否')
            ],
            'open' => [
                'on'  => ['value' => 1, 'text' => __('是'), 'color' => 'success'],
                'off' => ['value' => 0, 'text' => __('否'), 'color' => 'warning'],
            ],
            'public' => [
                'on'  => ['value' => 1, 'text' => __('開'), 'color' => 'primary'],
                'off' => ['value' => 0, 'text' => __('閉'), 'color' => 'danger'],
            ],
            'html' => [
                '1' => "<span class='label label-large label-warning'>".__('是')."</span>",
                '0' => "<span class='label label-large label-default'>".__('否')."</span>",
            ],

            'status' => [
                'on'  => ['value' => '1', 'text' => __('正常'), 'color' => 'primary'],
                'off' => ['value' => '2', 'text' => __('凍結'), 'color' => 'danger'],
            ],
        ];

        return $data[$type];
    }

    /**
     * check form
     */
    private function checkForm() {
        // check if not fill form
        return isset(request()->all()['form']) ? true : false ;
        //
    }

    /**
     * 取得全域參數
     *
     * @param [type] $slug
     * @return void
     */
    private function getGlobalParamValue($slug) {
       return AdminGlobalParam::where('param_slug', $slug)->first()->param_default;
    }

    /**
     * save image
     */
    private function saveFile($file, $folder = "images", $fileName = null)
    {
        if ($file->isValid()) {
            if (empty($fileName)) {
                $fileName = time().'.'.$file->getClientOriginalExtension();
            }

            //
            $bad = array_merge(
                array_map('chr', range(0,31)),
                array("<", ">", ":", '"', "／","/", "\\", "|", "?", "*"));
            $fileName = str_replace($bad, "", $fileName);
            //
            if ('WIN' == substr(PHP_OS, 0, 3)) {
                $path = \Storage::disk('admin')->putFileAs($folder, new File($file), mb_convert_encoding($fileName, "BIG5"));
                // 連續檔案上傳, 來不及寫入
                sleep(1);
                return iconv("BIG5", "UTF-8", $path);
            } else {
                $path = \Storage::disk('admin')->putFileAs($folder, new File($file), $fileName);
                // 連續檔案上傳, 來不及寫入
                sleep(1);
                return $path;
            }
        }
    }

    /**
     * 發送LINE Notify
     *
     * @return void
     */
    private function sendLineNotify($access_token, $message, $stickerId = 41, $stickerPackageId = 2) {

        if (is_array($message)) {
            $message = chr(13).chr(10) . implode(chr(13).chr(10), $message);
        }

        $message = $message . chr(13).chr(10) . 'line://ti/p/' . Admin::user()->line_id;

        $apiUrl = "https://notify-api.line.me/api/notify";

        $params = [
            'message' => $message,
            'stickerPackageId' => $stickerPackageId,
            'stickerId' => $stickerId
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $access_token
        ]);
        curl_setopt($ch, CURLOPT_URL, $apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        $output = curl_exec($ch);
        curl_close($ch);
    }
}
