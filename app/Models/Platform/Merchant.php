<?php

namespace App\Models\Platform;

use App\Models\Merchant\GameVendor;
use App\Models\Traits\LineMenu;
use App\Models\Traits\MerchantLine;
use App\Models\UuidModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;

/**
 * 商戶
 */
class Merchant extends UuidModel
{
    use HasFactory, MerchantLine, LineMenu;

    protected $table = 'sys_merchants';

    protected $casts = [
//        'tx_db' => 'json',
        'wallet_api' => 'json',
        'wallet_method' => 'json',
    ];

    public function vendors() {
        return $this->hasMany(GameVendor::class, 'merchant_code', 'code');
    }

    public function company() {
        return $this->belongsTo(Company::class, 'code', 'code');
    }

    protected static function boot()
    {
        parent::boot();

        self::creating(function ($model) {
            $model->id = (string) Str::uuid();
        });

        self::saving(function ($model) {
            $cacheKey = "merchant_" . $model->code;
            Redis::del($cacheKey);
            // Redis::del("{$model->line_login_channel_id}_switch");
            Cache::forget("{$model->code}_switch");
        });
        self::deleting(function ($model) {
            $cacheKey = "merchant_" . $model->code;
            Redis::del($cacheKey);
            // Redis::del("{$model->line_login_channel_id}_switch");
            Cache::forget("{$model->code}_switch");
        });
    }

}
