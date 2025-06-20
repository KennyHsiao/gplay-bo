<?php

namespace App\Models\GameManage;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Xn\Admin\Traits\DefaultDatetimeFormat;

class GameType extends Model
{
    use HasFactory, DefaultDatetimeFormat;

    public $timestamps = false;

    protected $table = "gm_types";

    protected $casts = [
        'name' => 'json',
    ];

    public function scopeFilterGameType($query) {
        $lang = session('locale');
        return $query->select(DB::raw("name::json->>'{$lang}' AS type_name"), "code")
        ->orderBy("code")->get();
    }

    public function scopePluckKV($query) {
        $lang = session('locale');
        return $query->select(DB::raw("name::json->>'{$lang}' AS type_name"), "code")
        ->orderBy("code")->pluck('type_name', 'code');
    }
}
