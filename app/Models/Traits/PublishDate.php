<?php

namespace App\Models\Traits;

use App\Helpers\DateTransform;

trait PublishDate
{
     /**
     * StartTime.
     *
     * @return string
     */
    public function getStartTimeAttribute($value) {
        return DateTransform::TimestampToLocal($value, 'Y-m-d H:i:s');
    }

    /**
     * StartTime
     *
     * @param  string  $value
     * @return void
     */
    public function setStartTimeAttribute($value) {
        $this->attributes['start_time'] = DateTransform::DateToUTC($value, session('timezone')) * 1000;
    }

    /**
     * EndTime.
     *
     * @return string
     */
    public function getEndTimeAttribute($value) {
        return DateTransform::TimestampToLocal($value, 'Y-m-d H:i:s');
    }

    /**
     * EndTime
     *
     * @param  string  $value
     * @return void
     */
    public function setEndTimeAttribute($value) {
        $this->attributes['end_time'] = DateTransform::DateToUTC($value, session('timezone')) * 1000;
    }
}
