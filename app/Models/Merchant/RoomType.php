<?php

namespace App\Models\Merchant;

use App\Models\Platform\Company;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Redis;
use Xn\Admin\Traits\DefaultDatetimeFormat;

class RoomType extends Model
{
    use HasFactory, DefaultDatetimeFormat;

    public $timestamps = false;

    protected $table = "mc_room_types";

    protected $casts = [
        'title' => 'json',
    ];

    protected static function boot()
    {
        parent::boot();


        self::saving(function ($model) {
            Redis::del("mc_room_types:mcCode:".$model->merchant_code);
            Redis::del(sprintf("mcCode:%s:roomType:map", $model->merchant_code));
        });
    }

    public function merchant()
    {
        return $this->belongsTo(Company::class, 'merchant_code', 'code');
    }
}
