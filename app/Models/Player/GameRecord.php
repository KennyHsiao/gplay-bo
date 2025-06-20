<?php

namespace App\Models\Player;

use App\Models\TxBaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Xn\Admin\Helper\DateTransform;

class GameRecord extends TxBaseModel
{
    use HasFactory;

    protected $table = 'records';

    public $incrementing = false;

    public $timestamps = false;

    /**
     * 遊戲開始時間.
     *
     * @return string
     */
    public function getStartTimeAttribute($value) {
        return DateTransform::TimestampToLocal($value, 'Y-m-d H:i:s');
    }

    /**
     * 遊戲結束時間.
     *
     * @return string
     */
    public function getEndTimeAttribute($value) {
        return DateTransform::TimestampToLocal($value, 'Y-m-d H:i:s');
    }

    /**
     * 遊戲結算時間.
     *
     * @return string
     */
    public function getBillTimeAttribute($value) {
        return DateTransform::TimestampToLocal($value, 'Y-m-d H:i:s');
    }
}
