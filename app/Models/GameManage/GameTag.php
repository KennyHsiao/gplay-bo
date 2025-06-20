<?php

namespace App\Models\GameManage;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Xn\Admin\Traits\DefaultDatetimeFormat;

class GameTag extends Model
{
    use HasFactory, DefaultDatetimeFormat;

    public $timestamps = false;

    protected $table = "gm_tags";

    protected $casts = [
        'name' => 'json',
    ];

    public function scopeFilterGameTag($query, $lang) {
        return $query->select(DB::raw("name::json->>'{$lang}' AS tag_name"), "code")
        ->orderBy("code")->get();
    }

}
