<?php

namespace App\Helpers;

use DateTime;
use DateTimeZone;

class DateTransform {

    /**
     * 日期時區轉換
     *
     * @param [type] $datetime
     * @return string
     */
    public static function DatetimeToLocal($datetime, $format = 'Y-m-d H:i:s') {
        $date = new \DateTime($datetime, new \DateTimeZone('UTC'));
        $date->setTimezone(new \DateTimeZone(session('timezone')));

        return $date->format($format); // 2011-11-10 15:17:23 -0500
    }


    /**
     * 時間戳時區轉換
     *
     * @param string $timestamp
     * @return string
     */
    public static function TimestampToLocal($timestamp, $format = 'Y-m-d H:i:s', $timezone = null) {
        $timestamp = substr($timestamp, 0, 10);
        $datestr = date("Y-m-d H:i:s", intval($timestamp));
        $date = new \DateTime($datestr, new \DateTimeZone('UTC'));
        $date->setTimezone(new \DateTimeZone($timezone??session('timezone')??'asia/taipei'));

        return $date->format($format); // 2011-11-10 15:17:23 -0500
    }


    /**
     * 日期轉換UTC timestamp
     *
     * @param [type] $date
     * @param [type] $timezone
     * @return int
     */
    public static function DateToUTC($date, $timezone) {
        $dt = new \DateTime($date, new \DateTimeZone($timezone));
        $dt->setTimezone(new \DateTimeZone("UTC"));
        return intval($dt->getTimestamp());
    }

    /**
     * 取得當前日期
     *
     * @param string $timezone
     * @param string $format
     * @return string
     */
    public static function CurrDatetime(string $timezone, $format = 'Y-m-d H:i:s') {
        $timestamp = time();
        $dt = new DateTime("now", new DateTimeZone($timezone)); //first argument "must" be a string
        $dt->setTimestamp($timestamp); //adjust the object to correct timestamp
        return $dt->format($format);
    }

    /**
     * 可閱讀日期
     *
     * @param [type] $date
     * @return void
     */
    public static function TimeToHuman($date) {
        $chinese = date('Y年m月d日 H:i', strtotime($date));
		$month = strtoupper(date('F', strtotime($date)));
        $day = date('d', strtotime($date));
        return ['c'=>$chinese, 'm'=>$month, 'd'=>$day];
    }
}

?>
