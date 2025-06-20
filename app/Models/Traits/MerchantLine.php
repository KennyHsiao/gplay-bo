<?php

namespace App\Models\Traits;

use App\Models\Line\LineBotImageMap;
use App\Models\Line\LineBotMenu;
use App\Models\Player\Member;
use App\Models\Merchant\Hyperlink;
use App\Models\System\PolyParam;

trait MerchantLine
{

    public function imagemaps() {
        return $this->hasMany(LineBotImageMap::class, 'merchant_code');
    }

    public function lineMenu() {
        return $this->hasMany(LineBotMenu::class, 'merchant_code')->where('line_menu_id', '>', '');
    }

    public function lineUser() {
        return $this->hasMany(Member::class, 'merchant_code');
    }

    public function hyperlinks() {
        return $this->hasMany(Hyperlink::class, 'merchant_code');
    }

    public function param()
    {
        return $this->morphMany(PolyParam::class, 'param');
    }

}
