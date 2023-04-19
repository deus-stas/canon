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

class Sliders {
	public const IBLOCK_CODE = 'main_slider';

	private const PAGE_MAX_LIMIT = 5000;


	private static $baseFilter = [
		'ACTIVE' => 'Y',
		'ACTIVE_DATE' => 'Y',
	];


	public static function getSlider(array $params = []): array {
		$isAdmin = User::isAdmin();

		$filter = [
			"SECTION_CODE" => strtoupper(LANGUAGE_CODE),
		];
		
		$limit = (int) $params['navigation']['limit'];

		if ($limit < 1 || $limit > self::PAGE_MAX_LIMIT) {
			$limit = self::PAGE_MAX_LIMIT;
		}

		$nav = [
			'nTopCount' => $limit,
		];

		$params = [
			'IBLOCK_CODE' => self::IBLOCK_CODE,
			'ORDER' => [
				'ACTIVE_FROM' => 'DESC',
				'SORT' => 'ASC'
			],
			'FILTER' => array_merge(self::$baseFilter, $filter),
			'SELECT' => [
				'ID:int>id',
				'CODE>code',
				'NAME>name',
				'PREVIEW_TEXT:string>previewText',
				'PREVIEW_PICTURE:Image>previewImage',
				'PROPERTY_LINK>link',
				'DETAIL_TEXT:string>caption',
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


}
