<?php

namespace App\Models\Merchant;

use App\Models\GameManage\Game as GameManageGame;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Xn\Admin\Traits\DefaultDatetimeFormat;

class Game extends Model
{
    use HasFactory, DefaultDatetimeFormat;

    public $timestamps = false;

    protected $table = "mc_games";

    protected $casts = [
        'name' => 'json',
    ];

    public function info() {
        return $this->belongsTo(GameManageGame::class, 'game_code', 'game_code');
    }

    public function scopeFilterGameName($query, $merchantCode, $lang) {
        return $query->select(DB::raw("CONCAT('(',game_code,')',name::json->>'{$lang}') AS game_name"), "game_code")
        ->where('merchant_code', $merchantCode)
        ->orderBy("game_code")->get();
    }
}
