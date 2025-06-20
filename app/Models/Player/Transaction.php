<?php

namespace App\Models\Player;

use App\Models\TxBaseModel;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Transaction extends TxBaseModel
{

    protected $table = "transactions";

    public $timestamps = false;

    protected $fillable = [
        'merchant_code',
        'account',
        'before_balance',
        'transfer_amount',
        'balance',
        'trans_type',
        'trace_id',
        'memo',
        'created_time'
    ];

    public function getCreatedTimeAttribute($value) {
        return Carbon::createFromTimestamp($value/1000)->tz("PRC")->format("Y-m-d H:i:s");
    }

    public function member()
    {
        return$this->belongsTo(Member::class, 'account', 'account');
    }

}
