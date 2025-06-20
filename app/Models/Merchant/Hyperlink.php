<?php

namespace App\Models\Merchant;

use App\Models\Platform\Company;
use App\Models\UuidModel;
use Illuminate\Support\Facades\Redis;

class Hyperlink extends UuidModel
{
    public $timestamps = false;

    public function merchant() {
        return $this->belongsTo(Company::class, 'merchant_code', 'code');
    }

    public function getScopesAttribute($value)
    {
        return explode(',', $value);
    }

    public function setScopesAttribute($value)
    {
        $this->attributes['scopes'] = implode(',', $value);
    }

    protected static function boot()
    {
        parent::boot();


        self::saving(function ($model) {
            $cacheKey = "payment_" . $model->code;
            Redis::del($cacheKey);
        });
        self::deleting(function ($model) {
            $cacheKey = "payment_" . $model->code;
            Redis::del($cacheKey);
        });
    }
}
