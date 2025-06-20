<?php

namespace App\Models\Player;

use App\Helpers\DateTransform;
use App\Models\Platform\Merchant;
use App\Models\TxBaseModel;
use Xn\Admin\Traits\DefaultDatetimeFormat;

class Member extends TxBaseModel
{
    use DefaultDatetimeFormat;

    protected $guarded = [];

    protected $keyType = 'string';

    protected $primaryKey = 'account';

    public $timestamps = false;

    public function merchant()
    {
        return $this->belongsTo(Merchant::class, 'merchant_code')
            ->select(['id', 'code', 'username', 'line_login_channel_id', 'switch_transfer', 'switch_departure', 'status']);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'account', 'account');
    }

    /**
     * 上線時間.
     *
     * @return string
     */
    public function getOnlineAtAttribute($value) {
        return DateTransform::TimestampToLocal($value, 'Y-m-d H:i:s');
    }

    /**
     * 更新時間.
     *
     * @return string
     */
    public function getUpdatedTimeAttribute($value) {
        return DateTransform::TimestampToLocal($value, 'Y-m-d H:i:s');
    }
}
