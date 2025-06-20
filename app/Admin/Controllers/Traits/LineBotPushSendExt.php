<?php

namespace App\Admin\Controllers\Traits;

use App\Models\Line\LineBotPush;
### flex message
use App\Bot\LINEBot\Flex\TemplateFlexBuilder;
use App\Bot\LINEBot\Flex\MessageContainerBuilder\Container\BubbleContainerTemplateBuilder;
use App\Bot\LINEBot\Flex\MessageContainerBuilder\Container\CarouselContainerTemplateBuilder;
use App\Bot\LINEBot\Flex\TemplateBlock\HeaderTemplateBlockBuilder;
use App\Bot\LINEBot\Flex\TemplateBlock\HeroTemplateBlockBuilder;
use App\Bot\LINEBot\Flex\TemplateBlock\BodyTemplateBlockBuilder;
use App\Bot\LINEBot\Flex\TemplateBlock\FooterTemplateBlockBuilder;
use App\Bot\LINEBot\Flex\TemplateComponents\BoxTemplateComponentBuilder;
use App\Bot\LINEBot\Flex\TemplateComponents\TextTemplateComponentBuilder;
use App\Bot\LINEBot\Flex\TemplateComponents\ButtonTemplateComponentBuilder;
use App\Bot\LINEBot\Flex\TemplateComponents\ImageTemplateComponentBuilder;
use App\Bot\LINEBot\Flex\TemplateActionBuilder\MessageTemplateActionBuilder;
use App\Bot\LINEBot\Flex\TemplateActionBuilder\PostbackTemplateActionBuilder;
use App\Bot\LINEBot\Flex\TemplateActionBuilder\UriTemplateActionBuilder;
use Illuminate\Support\Facades\DB;
### Imagemap
use LINE\LINEBot\ImagemapActionBuilder\AreaBuilder;
use LINE\LINEBot\ImagemapActionBuilder\ImagemapMessageActionBuilder;
use LINE\LINEBot\ImagemapActionBuilder\ImagemapUriActionBuilder;
use LINE\LINEBot\MessageBuilder\Imagemap\BaseSizeBuilder;
use LINE\LINEBot\MessageBuilder\ImagemapMessageBuilder;
###
use LINE\LINEBot\MessageBuilder\TextMessageBuilder;
use LINE\LINEBot\MessageBuilder\ImageMessageBuilder;
use Xn\Admin\Facades\Admin;

trait LineBotPushSendExt
{
    /**
     * 發送推播訊息
     *
     * @return void
     */
    public function sendPush($message_id) {
        # build push message
        # 發送名單
        $adminUserId = Admin::user()->merchant_code;
        $push_list = [];
        $push = LineBotPush::with(['targets.subscribes', 'targets.filters'])->find($message_id);
        $sqlWhere = ["merchant_code = '{$adminUserId}'"];
        foreach($push->targets as $target) {
            foreach($target->filters as $filter) {
                $sql = "";
                switch ($filter['type']) {
                    //地區', '性別', '年齡', '出生月份', '消費範圍'
                    case '地區':
                        $county = "'".implode("','",explode(',', $filter['county']))."'";
                        $sql = "county in($county)";
                        break;
                    case '性別':
                        $sql = "gender ='{$filter['param1']}'";
                        break;
                    case '年齡':
                        $sql = "age >='{$filter['param1']}' and age<='{$filter['param2']}'";
                        break;
                    case '出生月份':
                        $month = "'".implode("','",explode(',', $filter['param1']))."'";
                        $sql = "month in($month)";
                        break;
                    case '消費範圍':
                        $sql = "amount >='{$filter['param1']}' and amount<='{$filter['param2']}'";
                        break;
                }
                $sqlWhere[] = $sql;
            }
        }
        if (count($sqlWhere)) {
            $push_list = DB::table('line_bot_users_view')->select(DB::raw("distinct user_line_id"))->whereRaw(implode(' and ', $sqlWhere))->pluck('user_line_id')->toArray();
        }

        foreach($push->targets as $target) {
            $push_list = array_merge($push_list, $target->subscribes->pluck('user_line_id')->toArray());
        }

        $admin = $push->admin()->first();

        switch ($push['type']) {
            case 'text': $message = $this->buildText($push); break;
            case 'image': $message = $this->buildImage($push); break;
            case 'imagemap': $message = $this->buildImagemap($push); break;
            case 'flex': $message = $this->buildFlex($push); break;
        }
        # linebot instance
        $httpClient = new \LINE\LINEBot\HTTPClient\CurlHTTPClient($admin['line_m_channel_access_token']);
        $bot = new \LINE\LINEBot($httpClient, ['channelSecret' => $admin['line_m_channel_secret']]);
        $statusCode = 0;
        $resultMessage = null;

        // 發送訊息
        foreach(collect($push_list)->chunk(100) as $chunk) {
            $result = $bot->multicast(array_values($chunk->toArray()), $message);
            $statusCode = $result->getHTTPStatus();
            $resultMessage = $result->getJSONDecodedBody();
        }

        //
        # User
        // if (in_array($push['target'],['all', 'user'])) {
        //     \DB::table('line_bot_users')->where('merchant_code', $push['merchant_code'])->orderBy('user_line_id')->chunk(100, function($users) use($bot, $message, &$statusCode, &$resultMessage)
        //     {
        //         $tos = $users->pluck('user_line_id')->toArray();
        //         $result = $bot->multicast($tos, $message);
        //         $statusCode = $result->getHTTPStatus();
        //         $resultMessage = $result->getJSONDecodedBody();
        //     });
        // }
        # Group
        // if (in_array($push['target'],['all', 'group'])) {
        //     \DB::table('line_bot_groups')->where('merchant_code', $push['merchant_code'])->orderBy('group_id')->chunk(100, function($groups) use($bot, $message, &$statusCode, &$resultMessage)
        //     {
        //         foreach ($groups as $group)
        //         {
        //             $result = $bot->pushMessage($group->group_id, $message);
        //             $statusCode = $result->getHTTPStatus();
        //             $resultMessage = $result->getJSONDecodedBody();
        //         }
        //     });
        // }
        #
        if ($statusCode === 200) {
            $push->sent_at = date('Y/m/d H:i:s');
            $push->count = count($push_list);
            $push->save();
            return response()->json(['message' => '成功送出'], $statusCode);
        } else {
            return response()->json($resultMessage, 200);
        }
    }

    /**
     * 建立文字訊息
     *
     * @param [type] $data
     * @return void
     */
    protected function buildText($data) {
        return new TextMessageBuilder($data['message']);
    }

    /**
     * 建立圖片訊息
     *
     * @param [type] $data
     * @return void
     */
    protected function buildImage($data) {
        $img = $this->imageUrl($data['image']);
        return new ImageMessageBuilder($img, $img);
    }

    /**
     * 建立影像地圖訊息
     *
     * @param [type] $data
     * @return void
     */
    protected function buildImagemap($data) {
        $actions = [];
        foreach($data['actions'] as $action) {
            $area = $this->coordToArea($action['coords']);
            switch($action['action_type']) {
                case '連結':
                    $_action = new ImagemapUriActionBuilder(
                        $action['action_uri'],
                        new AreaBuilder($area[0], $area[1], $area[2], $area[3])
                    );
                break;
                case '文章':
                    $uri = route('store.article', ['id' => $action['action_uri']]);
                    $_action = new ImagemapUriActionBuilder(
                        $uri,
                        new AreaBuilder($area[0], $area[1], $area[2], $area[3])
                    );
                break;
                case '預約': // message
                    switch ($action['action_attr']) {
                        case '所有活動':
                            $_action = new ImagemapMessageActionBuilder(
                                $action['action_uri'],
                                new AreaBuilder($area[0], $area[1], $area[2], $area[3])
                            );
                        break;
                        case '單一活動':
                            $_action = new ImagemapMessageActionBuilder(
                                "預約|{$action['action_uri']}",
                                new AreaBuilder($area[0], $area[1], $area[2], $area[3])
                            );
                        break;

                    }
                break;
                case '商品列表': // message
                    switch ($action['action_attr']) {
                        case '列表':
                            $_action = new ImagemapMessageActionBuilder(
                                $action['action_uri'],
                                new AreaBuilder($area[0], $area[1], $area[2], $area[3])
                            );
                        break;
                        case '單一分類':
                            $_action = new ImagemapMessageActionBuilder(
                                "類別|{$action['action_uri']}",
                                new AreaBuilder($area[0], $area[1], $area[2], $area[3])
                            );
                        break;
                        case '商品屬性':
                            $_action = new ImagemapMessageActionBuilder(
                                $action['action_uri'],
                                new AreaBuilder($area[0], $area[1], $area[2], $area[3])
                            );
                        break;
                    }
                break;
                default:
                    $_action = new ImagemapMessageActionBuilder(
                        $action['action_uri'],
                        new AreaBuilder($area[0], $area[1], $area[2], $area[3])
                    );
                break;
            }

            $actions[] = $_action;
        }
        $richMessageUrl = env('APP_URL')."/{$data->image}";
        return new ImagemapMessageBuilder(
            $richMessageUrl,
            $data->title,
            new BaseSizeBuilder($data['image_height'], 1040),
            $actions
        );
    }

    /**
     * 建立卡片訊息
     *
     * @param [type] $data
     * @return void
     */
    protected function buildFlex($dialog) {
        $dialogs_builder = [];

        $body = [];
        $footer = [];
        # 按鈕
        if (count($dialog['actions']) > 0 ) {
            foreach($dialog['actions'] as $btn) {
                switch($btn['action_type']) {
                    case '文字':
                        $footer[] = new ButtonTemplateComponentBuilder(new MessageTemplateActionBuilder($btn['action_attr'], $btn['description']));
                    break;
                    case '連結':
                        $footer[] = new ButtonTemplateComponentBuilder(new UriTemplateActionBuilder($btn['action_uri'], $btn['action_attr']));
                    break;
                    case '圖片':
                        $img = $this->imageUrl($btn['action_image']);
                        $footer[] = new ButtonTemplateComponentBuilder(new PostbackTemplateActionBuilder("action=img&data=".$img, $btn['action_attr']));
                    break;
                }
            }
        }
        # 主體
        $bubble = [];
        $bubble['header'] = new HeaderTemplateBlockBuilder(new BoxTemplateComponentBuilder(
            [
                new TextTemplateComponentBuilder(str_limit($dialog['title'], 40))
            ], [
                // optional
            ]));
        if (!empty($dialog['image'])) {
            $img = $this->imageUrl($dialog['image']);
            $bubble['hero'] = new HeroTemplateBlockBuilder(new ImageTemplateComponentBuilder($img, [
                // 'aspectRatio' => '20:13',
                'aspectMode' => 'cover',
                'size' => 'full'
            ]));
        }
        if (!empty($dialog['message'])) {
            $bubble['body'] = new BodyTemplateBlockBuilder(new BoxTemplateComponentBuilder(
                [
                    new TextTemplateComponentBuilder($dialog['message'],[
                        'wrap' => true,
                        'gravity' => 'top',
                        'align' => 'start',
                        'maxLines' => 5
                    ])
                ], [
                    // optional
                ]));
        }
        if(!empty($footer)) {
            $bubble['footer'] = new FooterTemplateBlockBuilder(new BoxTemplateComponentBuilder(
                $footer
                ,[
                    // optional
                ]));
        }
        $dialogs_builder[] = new BubbleContainerTemplateBuilder(
            $bubble
        );
        $carouselTemplateBuilder = new CarouselContainerTemplateBuilder($dialogs_builder);
        return new TemplateFlexBuilder($dialog['title'], $carouselTemplateBuilder);
    }

	/**
	 * 座標尺寸轉換
	 *
	 * @param [type] $coord
	 * @return void
	 */
	protected function coordToArea($coord) {
		$coord = explode(',', $coord);
		$x1 = round($coord[0]);
		$y1 = round($coord[1]);
		$x2 = round($coord[2]);
		$y2 = round($coord[3]);

		return [abs($x1), abs($y1), abs($x1 - $x2), abs($y1  - $y2)];
    }

    /**
     * 圖片URL
     *
     * @param [type] $image
     * @return void
     */
    protected function imageUrl($image) {
        if (substr($image, 0, 4) === "http") return $image;
        return env('APP_URL') . '/uploads/' . $image;
    }
}
