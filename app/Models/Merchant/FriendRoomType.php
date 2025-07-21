<?php

namespace App\Models\Merchant;

use App\Models\Platform\Company;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Redis;
use Xn\Admin\Traits\DefaultDatetimeFormat;

class FriendRoomType extends Model
{
    use HasFactory, DefaultDatetimeFormat;

    public $timestamps = false;

    protected $table = "mc_friend_room_types";

    protected $guarded = [];

    protected static function boot()
    {
        parent::boot();


        self::saving(function ($model) {
            Redis::del("mc_friend_room_types:mcCode:" . $model->merchant_code);
            Redis::del(sprintf("mcCode:%s:friendRoomType:map", $model->merchant_code));
        });
    }

    public function merchant()
    {
        return $this->belongsTo(Company::class, 'merchant_code', 'code');
    }

    public function subfriendroomtypes()
    {
        return $this->hasMany(FriendSubRoomType::class, 'friend_room_type_id', 'id')
            ->orderBy("sort_order", "desc")
            ->orderBy("arena_id", "asc");
    }
}
