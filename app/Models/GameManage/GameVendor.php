<?php

namespace App\Models\GameManage;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Xn\Admin\Traits\DefaultDatetimeFormat;

class GameVendor extends Model
{
    use HasFactory, DefaultDatetimeFormat;

    public $timestamps = false;

    protected $table = "gm_vendors";

    protected $casts = [
        'params' => 'json',
        'lang' => 'json',
        'name' => 'json',
    ];

    protected $guarded = ['id'];

    public function gameType()
    {
        return $this->belongsTo(GameType::class, 'game_type', 'code');
    }

    public function currencies(){
        return $this->hasMany(GameVendorCurrency::class, 'vendor_code', 'code');
    }

    public function scopePluckKV($query) {
        $lang = session('locale');
        return $query->select(DB::raw("CONCAT('(',code,')',name::json->>'$lang') AS _name"), "code")
        ->orderBy("code")->pluck('_name', 'code');
    }

    protected static function boot()
    {
        parent::boot();

        self::saving(function ($model) {
            $cacheKey = "vendor_" . $model->code;
            Redis::del($cacheKey);
            // Redis::del("{$model->line_login_channel_id}_switch");
            Cache::forget("{$model->code}_switch");
        });
        self::deleting(function ($model) {
            $cacheKey = "vendor_" . $model->code;
            Redis::del($cacheKey);
            // Redis::del("{$model->line_login_channel_id}_switch");
            Cache::forget("{$model->code}_switch");
        });
    }
}
