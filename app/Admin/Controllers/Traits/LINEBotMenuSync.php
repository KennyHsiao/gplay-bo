<?php

namespace App\Admin\Controllers\Traits;

use Illuminate\Http\Request;
use App\Bot\LINEBot\RichMenuManager;
use App\Models\Line\LineBotMenu;
use App\Models\Player\Member;
use App\Models\Platform\Merchant;
use Illuminate\Support\Facades\DB;
use Xn\Admin\Facades\Admin;

trait LINEBotMenuSync
{

    /**
     * 上傳LINE選單
     *
     * @param Request $request
     * @param [type] $id
     * @return void
     */
    public function postUploadToLINE(Request $request, $id) {
        $menu = LineBotMenu::find($id);
        $model = Merchant::where('code', $menu->merchant_code)->first();
        $imagePath = realpath('') . '/uploads/' . $menu['menu_image'];
        $menuMng = new RichMenuManager($model->line_m_channel_access_token, $model->line_m_channel_secret);
        $menuId = $menuMng->createNewRichmenu($menu);
        DB::transaction(function()use($menuMng, $menu, $menuId, $imagePath){
            if ($menuMng->uploadRichMenuImage($menuId, $imagePath)) {
                $menu->line_menu_id = $menuId;
                $menu->save();
            }
        });
    }
    /**
     * 移除LINE選單
     *
     * @param Request $request
     * @param [type] $id
     * @return void
     */
    public function postDeleteFromLINE(Request $request, $id) {
        $txdb = strtolower(session('agent_code')."_tx");
        $menu = LineBotMenu::find($id);
        $model = Merchant::where('code', $menu->merchant_code)->first();
        $menuMng = new RichMenuManager($model->line_m_channel_access_token, $model->line_m_channel_secret);
        $menuId = $menu->line_menu_id;
        // 移除已連結的User
        $lineUsers = DB::connection($txdb)->table('members')->select(DB::raw("distinct user_line_id"))->whereRaw(" merchant_code = '".$menu->merchant_code."'")->pluck('user_line_id')->toArray();
        DB::connection($txdb)->transaction(function()use($menuMng, $menu, $menuId, $lineUsers){
            foreach($lineUsers as $user) {
                if($menuMng->unlinkRichMenu($user['user_line_id'])) {
                    $user->update(['line_bot_menu_id' => '']);
                }
            }
        });
        //
        if ($menuMng->deleteRichMenu($menuId)) {
            $menu->line_menu_id = "";
            $menu->save();
        }
    }

    /**
     * 批量移除LINE選單
     *
     * @param Request $request
     * @param [type] $id
     * @return void
     */
    public function postBulkDeleteFromLINE(Request $request, $id) {
        $txdb = strtolower(session('agent_code')."_tx");
        $menu = LineBotMenu::find($id);
        $model = Merchant::where('code', $menu->merchant_code)->first();
        $menuMng = new RichMenuManager($model->line_m_channel_access_token, $model->line_m_channel_secret);
        $menuId = $menu->line_menu_id;
        // 移除已連結的User
        $lineUsers = DB::connection($txdb)->table('members')->select(DB::raw("distinct user_line_id"))->whereRaw(" merchant_code = '".$menu->merchant_code."'")->pluck('user_line_id')->toArray();
        DB::connection($txdb)->transaction(function()use($menuMng, $menu, $menuId, $lineUsers, $txdb){
            // 發送訊息
            foreach(collect($lineUsers)->chunk(100) as $chunk) {
                if($menuMng->bulkUnlinkRichMenu(array_values($chunk->toArray()), $menuId)) {
                    DB::connection($txdb)->update("update members set line_bot_menu_id = '' where merchant_code ='".$menu->merchant_code."' and user_line_id in('".implode("','",$chunk->toArray())."')");
                }
            }
        });
        //
        if ($menuMng->deleteRichMenu($menuId)) {
            $menu->line_menu_id = "";
            $menu->save();
        }
    }

    /**
     * 移除LINE預設選單
     *
     * @param Request $request
     * @param [type] $id
     * @return void
     */
    public function postDeleteDefaultMenuFromLINE(Request $request, $id) {
        $txdb = strtolower(session('agent_code')."_tx");
        $menu = LineBotMenu::find($id);
        $model = Merchant::where('code', $menu->merchant_code)->first();
        $menuMng = new RichMenuManager($model->line_m_channel_access_token, $model->line_m_channel_secret);
        $menuMng->cancelDefaultRichMenuId();
        $menuId = $menu->line_menu_id;
        $lineUsers = DB::connection($txdb)->table('members')->select(DB::raw("distinct user_line_id"))->whereRaw(" merchant_code = '".$menu->merchant_code."'")->pluck('user_line_id')->toArray();
        // 移除已連結的User
        DB::connection($txdb)->transaction(function()use($menuMng, $menu, $menuId, $lineUsers){
            foreach($lineUsers as $user) {
                $user->update(['line_bot_menu_id' => '']);
            }
        });
        //
        if ($menuMng->deleteRichMenu($menuId)) {
            $menu->line_menu_id = "";
            $menu->save();
        }
    }

    /**
     * 設置User的 “預設” LINE選單
     *
     * @param Request $request
     * @param [type] $id
     * @return void
     */
    public function postDefaultUserMenu(Request $request, $id) {
        $txdb = strtolower(session('agent_code')."_tx");
        $menu = LineBotMenu::find($id);
        $model = Merchant::where('code', $menu->merchant_code)->first();
        $menuMng = new RichMenuManager($model->line_m_channel_access_token, $model->line_m_channel_secret);
        $menuId = $menu->line_menu_id;
        $menuMng->setDefaultRichMenuId($menuId);
        $lineUsers = DB::connection($txdb)->table('members')->select(DB::raw("distinct user_line_id"))->whereRaw(" merchant_code = '".$menu->merchant_code."'")->pluck('user_line_id')->toArray();
        DB::connection($txdb)->transaction(function()use($menuMng, $menu, $menuId, $lineUsers){
            foreach($lineUsers as $user) {
                $user->update(['line_bot_menu_id' => $menuId]);
            }
        });
    }

    /**
     * 設置User的LINE選單
     *
     * @param Request $request
     * @param [type] $id
     * @return void
     */
    public function postLinkToUser(Request $request, $id) {
        $txdb = strtolower(session('agent_code')."_tx");
        $menu = LineBotMenu::find($id);
        $model = Merchant::where('code', $menu->merchant_code)->first();
        $menuMng = new RichMenuManager($model->line_m_channel_access_token, $model->line_m_channel_secret);
        $menuId = $menu->line_menu_id;
        $menuMng->setDefaultRichMenuId($menuId);
        $lineUsers = DB::connection($txdb)->table('members')->select(DB::raw("distinct user_line_id"))->whereRaw(" merchant_code = '".$menu->merchant_code."'")->pluck('user_line_id')->toArray();
        DB::connection($txdb)->transaction(function()use($menuMng, $menu, $menuId, $lineUsers){
            foreach($lineUsers as $user) {
                if($menuMng->linkToUser($user['user_line_id'], $menuId)) {
                    $user->update(['line_bot_menu_id' => $menuId]);
                }
            }
        });
    }

    /**
     * 批量設置User的LINE選單
     *
     * @param Request $request
     * @param [type] $id
     * @return void
     */
    public function postBulkLinkToUsers(Request $request, $id) {
        $txdb = strtolower(session('agent_code')."_tx");
        $menu = LineBotMenu::find($id);
        $model = Merchant::where('code', $menu->merchant_code)->first();
        $menuMng = new RichMenuManager($model->line_m_channel_access_token, $model->line_m_channel_secret);
        $menuId = $menu->line_menu_id;
        $menuMng->setDefaultRichMenuId($menuId);
        $lineUsers = DB::connection($txdb)->table('members')->select(DB::raw("distinct user_line_id"))->whereRaw(" merchant_code = '".$menu->merchant_code."'")->pluck('user_line_id')->toArray();
        DB::connection($txdb)->transaction(function()use($menuMng, $menu, $menuId, $lineUsers, $txdb){
            // 發送訊息
            foreach(collect($lineUsers)->chunk(100) as $chunk) {
                if($menuMng->bulkLinkToUser(array_values($chunk->toArray()), $menuId)) {
                    DB::connection($txdb)->update("update members set line_bot_menu_id = '{$menuId}' where merchant_code ='".$menu->merchant_code."' and user_line_id in('".implode("','",$chunk->toArray())."')");
                }
            }
        });
    }
}
