<?php

namespace App\Models\Merchant;

use App\Models\GameManage\GameVendor as GameManageGameVendor;
use App\Models\Platform\Company;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Redis;
use Xn\Admin\Traits\DefaultDatetimeFormat;

class GameVendor extends Model
{
    use HasFactory, DefaultDatetimeFormat;

    public $timestamps = false;

    protected $table = "mc_vendors";

    protected $guarded = [];

    protected $casts = [

    ];

    public function vendor() {
        return $this->belongsTo(GameManageGameVendor::class, 'vendor_code', 'code');
    }

    public function merchant() {
        return $this->belongsTo(Company::class, 'merchant_code', 'code');
    }

    protected static function boot()
    {
        parent::boot();

        self::saving(function ($model) {
            $cacheKey = strtolower($model->vendor_code) . "_" . strtoupper($model->merchant_code);
            Redis::del($cacheKey);
        });
        self::deleting(function ($model) {
            $cacheKey = strtolower($model->vendor_code) . "_" . strtoupper($model->merchant_code);
            Redis::del($cacheKey);
        });
    }
}
