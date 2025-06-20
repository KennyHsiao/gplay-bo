<?php

namespace App\Models\Traits;


trait LineMenu
{
    /**
     * Line Bot Group belongs to Admin
     *
     */
    public function lineGroups() {
        return $this->hasMany(\App\Models\LineBotGroup::class, 'merchant_code');
    }
    /**
     * Undocumented function
     *
     */
    public function lineMenus() {
        return $this->hasMany(\App\Models\LineBotMenu::class, 'merchant_code')->where('line_menu_id', '>', '');
    }
    /**
     * Undocumented function
     *
     * @param [type] $query
     */
    public function mainLineMenu() {
        $menu = $this->lineMenus()->where(['main_menu' => '1'])->orderBy('updated_at', 'desc')->first();
        if(empty($menu)) {
            $menu = $this->lineMenus()->orderBy('updated_at', 'desc')->first();
        }
        return $menu;
    }
}
