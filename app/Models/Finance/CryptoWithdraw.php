<?php

namespace App\Models\Finance;

use App\Models\TxBaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Xn\Admin\Helper\DateTransform;

class CryptoWithdraw extends TxBaseModel
{
    use HasFactory;

    protected $table = 'crypto_withdraws';

    public $timestamps = false;
    /**
     * 建立時間.
     *
     * @return string
     */
    public function getCreatedTimeAttribute($value) {
        return DateTransform::TimestampToLocal($value, 'Y-m-d H:i:s');
    }
}
