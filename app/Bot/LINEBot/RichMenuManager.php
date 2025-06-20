<?php

namespace App\Bot\LINEBot;

use Illuminate\Support\Facades\Log;
use LINE\LINEBot;
use LINE\LINEBot\HTTPClient\CurlHTTPClient;
use LINE\LINEBot\RichMenuBuilder\RichMenuAreaBoundsBuilder;
use LINE\LINEBot\RichMenuBuilder\RichMenuAreaBuilder;
use LINE\LINEBot\RichMenuBuilder\RichMenuSizeBuilder;
use LINE\LINEBot\RichMenuBuilder;
use LINE\LINEBot\TemplateActionBuilder\MessageTemplateActionBuilder;
use LINE\LINEBot\TemplateActionBuilder\PostbackTemplateActionBuilder;
use LINE\LINEBot\TemplateActionBuilder\UriTemplateActionBuilder;


class RichMenuManager
{
	private $bot;

	public function __construct(string $line_channel_access_token, string $line_channel_secret) {
		$httpClient = new CurlHTTPClient($line_channel_access_token);
		$this->bot = new LINEBot($httpClient, ['channelSecret' => $line_channel_secret]);
	}

	/**
	 * 建立選單
	 *
	 * @param [type] $menu
	 * @return void
	 */
	function createNewRichmenu($menu = null) {
		foreach($menu->actions()->get() as $area) {
			// menu_type | menu_attr | menu_uri
			switch ($area['menu_type']) {
				case '商品列表': // message
					switch ($area['menu_attr']) {
						case '列表':
						$action = new MessageTemplateActionBuilder($area['menu_uri'], $area['menu_uri']);
						break;
						case '單一分類':
						$action = new MessageTemplateActionBuilder($area['menu_attr'], "類別|{$area['menu_uri']}");
						break;
						case '商品屬性':
						$action = new MessageTemplateActionBuilder($area['menu_attr'], $area['menu_uri']);
						break;
					}
					break;
				case '影像地圖':
					$action = new PostbackTemplateActionBuilder("","action=imagemap&data=".$area['menu_uri']);
					break;
				case '文章': // uri
					$uri = route('store.article', ['id' => $area['menu_uri']]);
					$action = new UriTemplateActionBuilder($area['menu_attr'], $uri);
					break;
				case '聯絡我們': // message
						switch ($area['menu_attr']) {
							case '聯絡我們':
								$action = new MessageTemplateActionBuilder($area['menu_attr'], $area['menu_uri']);
							break;
						}
					break;
				case '預約': // message
					switch ($area['menu_attr']) {
						case '所有活動':
						$action = new MessageTemplateActionBuilder($area['menu_uri'], $area['menu_uri']);
						break;
						case '單一活動':
						$action = new MessageTemplateActionBuilder($area['menu_uri'], "預約|{$area['menu_uri']}");
						break;
					}
					break;
				case '會員': // uri
						switch ($area['menu_attr']) {
							case '會員專區':
								$action = new MessageTemplateActionBuilder($area['menu_attr'], $area['menu_uri']);
							break;
						}
					break;
				case '常見問題':
					switch ($area['menu_attr']) {
						case '所有分類':
							$action = new MessageTemplateActionBuilder($area['menu_attr'], "QA|{$area['menu_attr']}");
							break;
						case '單一分類':
							$action = new MessageTemplateActionBuilder($area['menu_attr'], "QA|{$area['menu_attr']}");
							break;
					}
					break;
				case '連結': // uri
					$action = new UriTemplateActionBuilder($area['menu_attr'], $area['menu_uri']);
					break;
                case 'LIFF': // uri
                    $action = new UriTemplateActionBuilder($area['menu_attr'], $area['menu_uri']);
                    break;
				case '純文字': // uri
					// $action = new MessageTemplateActionBuilder(mb_substr($area['menu_uri'], 0, 20), $area['menu_uri']);
					$action = new PostbackTemplateActionBuilder("", "action=text&data=".$area['menu_uri']);
					break;
				case '關鍵字': // uri
					$action = new MessageTemplateActionBuilder(mb_substr($area['menu_uri'], 0, 20), $area['menu_uri']);
					break;
				case '菜單': // message
					// $action = new MessageTemplateActionBuilder($area['menu_attr'], "前往|{$area['menu_attr']}");
					$action = new PostbackTemplateActionBuilder("", "action=menu&data=".$area['menu_uri']);
					break;
			}

			$area = $this->coordToArea($area['coords']);
			$areaBuilders[] = new RichMenuAreaBuilder(
				new RichMenuAreaBoundsBuilder($area[0], $area[1], $area[2], $area[3]),
				$action
			);
		}
		$selected = $menu['init_expand'] === '1' ? true : false;
		$richMenuBuilder = new RichMenuBuilder(
			new RichMenuSizeBuilder(abs($menu['image_height']), abs($menu['image_width'])),
			$selected,
			$menu['menu_title'],
			$menu['menu_title'],
			$areaBuilders
		);
		# 清除選單
		// foreach( $this->getRichMenuList()['richmenus'] as $k => $menu) {
		// 	$this->deleteRichMenu($menu['richMenuId']);
		// }
		$result = $this->bot->createRichMenu($richMenuBuilder)->getJSONDecodedBody();
		if(isset($result['richMenuId'])) {
		  	return $result['richMenuId'];
		} else {
			Log::info($result);
		  	return $result;
		}
	}
	/**
	 * 取得已上傳選單
	 *
	 * @return void
	 */
	function getRichMenuList() {
		return $this->bot->getRichMenuList()->getJSONDecodedBody();
	}

	/**
	 * 檢查使用者當前選單
	 *
	 * @param [type] $userId
	 * @return void
	 */
	function getRichMenuId($userId) {
		$result = $this->bot->getRichMenuId($userId)->getJSONDecodedBody();
		if(isset($result['richMenuId'])) {
		  	return $result['richMenuId'];
		} else {
		  	return false;
		}
	}

	/**
	 * 移除使用者選單
	 *
	 * @param [type] $userId
	 * @return void
	 */
	function unlinkRichMenu($userId) {
		$result = $this->bot->unlinkRichMenu($userId)->getJSONDecodedBody();
		if(isset($result['message'])) {
		  	return $result['message'];
		} else {
		  	return 'success';
		}
	}

	/**
	 * 批量移除使用者選單
	 *
	 * @param [type] $userId
	 * @return void
	 */
	function bulkUnlinkRichMenu($userIds) {
		$result = $this->bot->bulkUnlinkRichMenu($userIds)->getJSONDecodedBody();
		if(isset($result['message'])) {
		  	return $result['message'];
		} else {
		  	return 'success';
		}
	}

	/**
	 * 移除已上傳選單
	 *
	 * @param [type] $richMenuId
	 * @return void
	 */
	function deleteRichMenu($richMenuId) {
		if(!$this->isRichmenuIdValid($richMenuId)) {
		  	return 'invalid richmenu id';
		}
		$result = $this->bot->deleteRichMenu($richMenuId)->getJSONDecodedBody();
		if(isset($result['message'])) {
		  	return $result['message'];
		} else {
		  	return true;
		}
	}

	/**
	 * 使用者預設選單（需重新進入LINEBot）
	 *
	 * @param [type] $richmenuId
	 * @return void
	 */
	function setDefaultRichMenuId($richMenuId) {
		if(!$this->isRichmenuIdValid($richMenuId)) {
		  	return 'invalid richmenu id';
		}
		$result = $this->bot->setDefaultRichMenuId($richMenuId)->getJSONDecodedBody();
		if(isset($result['message'])) {
		  	return $result['message'];
		} else {
		  	return 'success';
		}
	}

	/**
	 * 移除使用者預設選單（需重新進入LINEBot）
	 *
	 * @return void
	 */
	function cancelDefaultRichMenuId() {
		$result = $this->bot->cancelDefaultRichMenuId()->getJSONDecodedBody();
		if(isset($result['message'])) {
		  	return $result['message'];
		} else {
		  	return 'success';
		}
	}

	/**
	 * 使用者連結選單
	 *
	 * @param [type] $userId
	 * @param [type] $richmenuId
	 * @return void
	 */
	function linkToUser($userId, $richMenuId) {
		if(!$this->isRichmenuIdValid($richMenuId)) {
		  	return 'invalid richmenu id';
		}
		$result = $this->bot->linkRichMenu($userId, $richMenuId)->getJSONDecodedBody();
		if(isset($result['message'])) {
		  	return $result['message'];
		} else {
		  	return 'success';
		}
	}

	/**
	 * 批量設置使用者連結選單
	 *
	 * @param [type] $userId
	 * @param [type] $richmenuId
	 * @return void
	 */
	function bulkLinkToUser($userIds, $richMenuId) {
		if(!$this->isRichmenuIdValid($richMenuId)) {
		  	return 'invalid richmenu id';
		}
		$result = $this->bot->bulkLinkRichMenu($userIds, $richMenuId)->getJSONDecodedBody();
		if(isset($result['message'])) {
		  	return $result['message'];
		} else {
		  	return 'success';
		}
	}
	/**
	 * 上傳選單圖片
	 *
	 * @param [type] $richMenuId
	 * @param [type] $imagePath
	 * @param string $contentType
	 * @return void
	 */
	function uploadRichMenuImage($richMenuId, $imagePath, $contentType = 'image/png') {
		if(!$this->isRichmenuIdValid($richMenuId)) {
			return false;
		}
		$result = $this->bot->uploadRichMenuImage($richMenuId, $imagePath, $contentType)->getJSONDecodedBody();
		if(isset($result['message'])) {
			return $result['message'];
		} else {
			return true;
		}
	}


	/**
	 * 檢查選單是否有效
	 *
	 * @param [type] $string
	 * @return boolean
	 */
	private function isRichmenuIdValid($string) {
		if(preg_match('/^[a-zA-Z0-9-]+$/', $string)) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * 座標尺寸轉換
	 *
	 * @param [type] $coord
	 * @return void
	 */
	private function coordToArea($coord) {
		$coord = explode(',', $coord);
		$x1 = round($coord[0]);
		$y1 = round($coord[1]);
		$x2 = round($coord[2]);
		$y2 = round($coord[3]);

		return [abs($x1), abs($y1), abs($x1 - $x2), abs($y1  - $y2)];
	}
}
