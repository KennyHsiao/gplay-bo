<?php

namespace App\Helpers;

use App\Models\Platform\Merchant;
use Illuminate\Support\Facades\Cache;

class GCache {

    /**
     * 商戶開關
     *
     * @param [type] $code
     */
    public static function merchantSwitch($code) {
        $code = strtoupper($code);
        return Cache::rememberForever("{$code}_switch", function() use ($code){
            $model = Merchant::class;
            return $model::select('code', 'status', 'switch_transfer', 'switch_departure', 'line_login_channel_id', 'line_m_channel_id'
            )->where('code', $code)->first();
        });
    }
}
