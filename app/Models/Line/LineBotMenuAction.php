<?php

namespace App\Models\Line;

use Illuminate\Database\Eloquent\Model;

class LineBotMenuAction extends Model
{
    protected $fillable = [
        'line_bot_menu_id',
        'coords',
        'menu_type',
        'menu_attr',
        'menu_uri'
    ];

    public $timestamps = false;
}
