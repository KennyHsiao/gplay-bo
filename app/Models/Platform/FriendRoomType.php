<?php

namespace App\Models\Platform;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Redis;
use Xn\Admin\Traits\DefaultDatetimeFormat;

class FriendRoomType extends Model
{
    use HasFactory, DefaultDatetimeFormat;

    public $timestamps = false;

    protected $table = "sys_friend_room_types";

    protected $guarded = [];

    protected static function boot()
    {
        parent::boot();


        self::saving(function ($model) {
            Redis::del("sys_friend_room_types");
            Redis::del("friendRoomType:map");
        });
    }
}
