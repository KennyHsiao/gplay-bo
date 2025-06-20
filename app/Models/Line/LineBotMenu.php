<?php

namespace App\Models\Line;

use App\Models\Platform\Company;
use App\Models\UuidModel;

class LineBotMenu extends UuidModel
{
    //
    protected $fillable = [
        'merchant_code',
        'menu_title',
        'menu_image',
        'image_width',
        'image_height'
    ];

    public function merchant() {
        return $this->belongsTo(Company::class, 'merchant_code', 'code');
    }

    public function actions() {
        return $this->hasMany(LineBotMenuAction::class, 'line_bot_menu_id', 'id');
    }
}
