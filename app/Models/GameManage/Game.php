<?php

namespace App\Models\GameManage;

use App\Helper\GlobalParam;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Xn\Admin\Traits\DefaultDatetimeFormat;

class Game extends Model
{
    use HasFactory, DefaultDatetimeFormat;

    public $timestamps = false;

    protected $table = "gm_games";

    protected $casts = [
        'name' => 'json',
    ];

    public function gameVendor()
    {
        return $this->belongsTo(GameVendor::class, 'vendor_code', 'code');
    }

    public function gameType()
    {
        return $this->belongsTo(GameType::class, 'game_type', 'code');
    }

    public function scopeFilterGameName($query) {
        $lang = session('locale');
        return $query->select(DB::raw("CONCAT('(',game_code,')',name::json->>'{$lang}') AS game_name"), "game_code")
        ->orderBy("game_code")->get();
    }

    public function scopePluckKV($query) {
        $lang = session('locale');
        return $query->select(DB::raw("CONCAT('(',game_code,')',name::json->>'{$lang}') AS game_name"), "game_code")
        ->orderBy("game_code")->pluck('game_name', 'game_code');
    }

    protected static function boot()
    {
        parent::boot();

        self::saving(function () {
            // $lang = session('locale');
            // $cacheKey = "game_name_{$lang}";
            // Cache::forget($cacheKey);
            // Redis::del("games");
        });
    }
}
