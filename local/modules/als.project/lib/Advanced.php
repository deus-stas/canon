<?php


namespace ALS\Project;


use ALS\Helper\Arr;
use ALS\Helper\CacheManager;
use ALS\Helper\El;
use ALS\Helper\Hel;
use ALS\Helper\Help;
use ALS\Helper\Sect;
use ALS\Helper\Typograph;
use ALS\Property\EditorBlock\Block\BlockText;
use ALS\Property\EditorBlock\TypeConvert;

class Advanced {
	
	private static $baseFilter = [
		'ACTIVE' => 'Y',
		'ACTIVE_DATE' => 'Y',
	];


	public static function getMainpageBlocks(array $params = []): array {
		$isAdmin = User::isAdmin();
		
		$mainpage_blocks_limit = 9;

		$limit = (int) $params['limit'];

		if ($limit < 1 || $limit > $mainpage_blocks_limit) {
			$limit = $mainpage_blocks_limit;
		}

		$nav = [
			'nTopCount' => $limit,
		];
		
		$filter = [
			"SECTION_CODE" => strtoupper(LANGUAGE_CODE),
		];

		$params = [
			'IBLOCK_CODE' => 'mainpage_blocks',
			'ORDER' => [
				'ACTIVE_FROM' => 'DESC',
				'SORT' => 'ASC'
			],
			'FILTER' => array_merge(self::$baseFilter, $filter),
			'SELECT' => [
				'ID:int>id',
				'NAME>name',
				'PREVIEW_TEXT:string>description',
				'PREVIEW_PICTURE:Image>image',
				'DETAIL_TEXT>link',
			],
			'NAV' => $nav,
			'__SKIP_CACHE' => $isAdmin,
		];

		return CacheManager::getIblockItemsFromCache($params, static function(&$items) {

			foreach ($items as $k => $item) {

				// Типографим данные
				Typograph::processItem($items[$k], ['name', 'previewText']);

			}
		}, 300);
	}
	
	public static function langLink(string $link): string {
		return str_replace(strtoupper(LANGUAGE_CODE).'/', '', $link);
	}
	
}
