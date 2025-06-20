<?php

namespace App\Models\Line;

use App\Models\Merchant;
use App\Models\UuidModel;

class LineBotPush extends UuidModel
{
    protected $fillable = [
        'merchant_code',
        'type',
        'image',
        'title',
        'base_url',
        'image_width',
        'image_height',
        'message',
        'send_at',
        'target',
        'count'
    ];

    public function admin() {
        return $this->belongsTo(Merchant::class, 'merchant_code', 'code');
    }

    public function actions() {
        return $this->hasMany(LineBotPushAction::class, 'line_bot_push_id', 'id');
    }

    public function targets() {
        return $this->belongsToMany(LinePushGroup::class, 'line_bot_push_targets');
    }
}
