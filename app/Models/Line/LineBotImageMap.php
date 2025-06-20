<?php

namespace App\Models\Line;

use App\Models\UuidModel;

class LineBotImageMap extends UuidModel
{
    //
    protected $fillable = [
        'merchant_code',
        'image',
        'title',
        'base_url',
        'image_width',
        'image_height',
        'keywords'
    ];

    public function actions() {
        return $this->hasMany(LineBotImageMapAction::class, 'line_bot_image_map_id', 'id');
    }
}
