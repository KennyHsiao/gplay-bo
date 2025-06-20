<?php

namespace App\Presenters;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;

class UtilsPresenter
{
    /**
    * Youtube Url transfer to Embed
    */
    public static function youtubeId($url) {
        $search     = '#(.*?)(?:href="https?://)?(?:www\.)?(?:youtu\.be/|youtube\.com(?:/embed/|/v/|/watch?.*?v=))([\w\-]{10,12}).*#x';
        $replace    = '$2';
        return preg_replace($search, $replace, $url);
     }

    public static function youtube2Embed($url) {
       $search     = '#(.*?)(?:href="https?://)?(?:www\.)?(?:youtu\.be/|youtube\.com(?:/embed/|/v/|/watch?.*?v=))([\w\-]{10,12}).*#x';
       $replace    = 'http://www.youtube.com/embed/$2';
       return preg_replace($search, $replace, $url) . "?rel=0&amp;showinfo=0";
    }

    public static function downloadPath($path, $prefix = "/uploads/") {
        if (!empty($path)) {
            return $prefix . $path;
        } else {
            return 'javascript:void(0);';
        }
    }

    public static function picturePath($path, $prefix = "/uploads/") {
        if (substr($path, 0, 4) === "http") return $path;
        //
        if (!empty($path)) {
            return $prefix . $path;
        } else {
            return '';
        }
    }

    public static function shortContent($val) {
        switch(App::getLocale()) {
            case 'en':
                return Str::limit(strip_tags($val), 100);
                break;
            default :
                return Str::limit(strip_tags($val), 60);
                break;
        }
    }

    public static function nl2p($value) {
        return "<p>". preg_replace("/[\n\r]/","</p><p>",$value)."</p>";
    }

    public static function badges($value) {
        if(empty($value)) {
            return [];
        }
        $values = explode(',', $value);
        $badges = array_map(function($item){
            $prop = ($item == '熱門' ? 'hot' : ($item == '推薦' ? 'push' : ''));
            return "<div class='pd_badge {$prop}'> {$item} </div>";
        }, $values);

        return $badges;
    }

    public static function stringMask($str, $onlyNum = false, $start = 0, $length = 4, $repStr = "*") {

        if (isset($_GET['show'])) return $str;

        if ($onlyNum) {
            $mask = preg_replace ( "/\p{N}/ui", $repStr, $str );
            $mask = mb_substr ( $mask, $start );
            $str = substr_replace ( $str, $mask, $start );
        } else {
            $mask = preg_replace ( "/\S/ui", $repStr, $str );
            $mask = mb_substr ( $mask, $start, $length );
            $str = static::mb_substr_replace( $str, $mask, $start, $length );
        }

        return $str;
    }

    public static function cvscomInfo($str) {
        $info = json_decode($str, true)['Result'];

        return "<img src='/images/icon_{$info['StoreType']}.jpg' width='50'><br>超商取貨: <b>{$info['StoreName']}</b> ({$info['StoreCode']})";
    }

    public static function mb_substr_replace($string, $replacement, $start, $length=NULL) {
        if (is_array($string)) {
            $num = count($string);
            // $replacement
            $replacement = is_array($replacement) ? array_slice($replacement, 0, $num) : array_pad(array($replacement), $num, $replacement);
            // $start
            if (is_array($start)) {
                $start = array_slice($start, 0, $num);
                foreach ($start as $key => $value)
                    $start[$key] = is_int($value) ? $value : 0;
            }
            else {
                $start = array_pad(array($start), $num, $start);
            }
            // $length
            if (!isset($length)) {
                $length = array_fill(0, $num, 0);
            }
            elseif (is_array($length)) {
                $length = array_slice($length, 0, $num);
                foreach ($length as $key => $value)
                    $length[$key] = isset($value) ? (is_int($value) ? $value : $num) : 0;
            }
            else {
                $length = array_pad(array($length), $num, $length);
            }
            // Recursive call
            return array_map(__FUNCTION__, $string, $replacement, $start, $length);
        }
        preg_match_all('/./us', (string)$string, $smatches);
        preg_match_all('/./us', (string)$replacement, $rmatches);
        if ($length === NULL) $length = mb_strlen($string);
        array_splice($smatches[0], $start, $length, $rmatches[0]);
        return join($smatches[0]);
    }

    // 星期轉換
    public static function numToWeekday($val) {
        switch (intval($val)) {
            case 0: return '日'; break;
            case 1: return '一'; break;
            case 2: return '二'; break;
            case 3: return '三'; break;
            case 4: return '四'; break;
            case 5: return '五'; break;
            case 6: return '六'; break;
        }
    }

    /**
     * 智付通-應付款資訊
     *
     * @param [type] $json
     * @return void
     */
    public static function transLog($json) {
        if(empty($json)) return "";
        $info = json_decode($json, true)['Result'];
        $Amt = $info['Amt'];
        $BankCode = $info['BankCode'];
        $CodeNo = $info['CodeNo'];
        $ExpireDate = $info['ExpireDate'] . " " . $info['ExpireTime'];
        return "<div>金融機構代碼: {$BankCode} </div> <div>繳費代碼: {$CodeNo} </div> <div>應付金額: {$Amt} </div> <div>支付期限: {$ExpireDate} </div>";
    }

    /**
     * 智付通-已付款資訊
     *
     * @param [type] $json
     * @return void
     */
    public static function notifyLog($json) {
        if(empty($json)) return "";
        $info = json_decode($json, true)['Result'];
        $Amt = $info['Amt'];
        $PayBankCode = $info['PayBankCode'];
        $PayerAccount5Code = $info['PayerAccount5Code'];
        $PayTime = $info['PayTime'];
        return "<div>付款人金融機構代碼: {$PayBankCode} </div> <div>帳號末五碼: {$PayerAccount5Code} </div> <div>付款金額: {$Amt} </div> <div>支付完成時間: {$PayTime} </div>";
    }

    /**
     * 價格標籤
     */
    public static function priceLabel($oldPrice, $price) {
        if ($oldPrice === $price) {
            return "<span class='price'>NT$" . number_format($price) . "</span>";
        }

        if($oldPrice > $price) {
            return "<span class='oldPrice' style='text-decoration:line-through'>NT$" . number_format($oldPrice) . "</span><span class='price'>NT$" . number_format($price) . "</span>";
        }

        return "<span class='price'>NT$" . number_format($price) . "</span>";
    }

    /**
     * 處理JSON換行
     *
     * @param [type] $json
     * @return void
     */
    public static function jsonStringify($json) {
        if (is_array($json)) $json = json_encode($json);
        $search = array('\\', "\n", "\r", "\f", "\t", "\b", "'") ;
        $replace = array('\\\\', "\\n", "\\r","\\f","\\t","\\b", "'");
        return str_replace($search, $replace, $json);
    }
}
