<?php

namespace App\Models\Merchant;

use App\Models\Platform\Company;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Redis;
use Xn\Admin\Traits\DefaultDatetimeFormat;

class Article extends Model
{
    use HasFactory, DefaultDatetimeFormat;

    public function merchant() {
        return $this->belongsTo(Company::class, 'merchant_code', 'code');
    }

    public function comments() {
        return $this->hasMany(Banner::class, 'merchant_code', 'code');
    }

    protected static function boot()
    {
        parent::boot();


        self::saving(function ($model) {
            $cacheKey = "policy_" . $model->code;
            Redis::del($cacheKey);
        });
        self::deleting(function ($model) {
            $cacheKey = "policy_" . $model->code;
            Redis::del($cacheKey);
        });
    }
}
