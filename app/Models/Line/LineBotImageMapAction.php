<?php

namespace App\Models\Line;

use Illuminate\Database\Eloquent\Model;

class LineBotImageMapAction extends Model
{
    protected $fillable = [
        'line_bot_image_map_id',
        'coords',
        'action_type',
        'action_attr',
        'action_uri'
    ];

    public $timestamps = false;
}
