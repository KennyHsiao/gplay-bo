<?php

namespace App\Models\Line;

use App\Models\UuidModel;

class LineBotPushAction extends UuidModel
{
    protected $fillable = [
        'line_bot_push_id',
        'coords',
        'action_type',
        'action_attr',
        'action_uri',
        'action_image',
        'description',
        'seq'
    ];

    public $timestamps = false;
}
