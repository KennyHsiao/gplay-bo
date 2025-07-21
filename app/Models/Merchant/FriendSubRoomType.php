<?php

namespace App\Models\Merchant;

use App\Models\Platform\Company;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Redis;
use Xn\Admin\Traits\DefaultDatetimeFormat;

class FriendSubRoomType extends Model
{
    use HasFactory, DefaultDatetimeFormat;

    public $timestamps = false;

    protected $table = "mc_friend_sub_room_types";

    protected $guarded = [];

    protected static function boot()
    {
        parent::boot();


        // self::saving(function ($model) {
        //     $cacheKey = "mc_room_types";
        //     Redis::del($cacheKey);
        //
        //     $cursor = "0";
        //     do {
        //         [$cursor, $keys] = Redis::scan($cursor, ['match' => "mcCode:*:roomType:map", 'count' => 100]);
        //         info($keys);
        //         if (!empty($keys)) {
        //             Redis::del(...$keys);
        //         }
        //     } while ($cursor != 0);
        // });
    }

    public function friendRoomType()
    {
        return $this->belongsTo(FriendRoomType::class, 'friend_room_type_id', 'id');
    }
}
