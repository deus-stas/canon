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

class Socials {
	public const IBLOCK_CODE = 'socials';
	public const SHARE_IBLOCK_CODE = 'socials_share';

	private const PAGE_MAX_LIMIT = 5000;


	private static $baseFilter = [
		'ACTIVE' => 'Y',
		'ACTIVE_DATE' => 'Y',
	];


	public static function getList(string $type = ''): array {
		$isAdmin = User::isAdmin();

		$filter['SECTION_CODE'] = strtoupper(LANGUAGE_CODE);
		
		$params = [
			'IBLOCK_CODE' => $type == 'share' ? self::SHARE_IBLOCK_CODE : self::IBLOCK_CODE,
			'ORDER' => [
				'SORT' => 'ASC',
				'NAME' => 'ASC',
			],
			'FILTER' => array_merge(self::$baseFilter, $filter),
			'SELECT' => [
				'ID:int>id',
				'CODE>code',
				'NAME>name',
				'PREVIEW_TEXT:string>link',
				'PREVIEW_PICTURE:Image>icon',
				'PROPERTY_SVG_ICON:File>svgIcon',
			],
			'__SKIP_CACHE' => $isAdmin,
		];

		return CacheManager::getIblockItemsFromCache($params, static function(&$items) {

			foreach ($items as $k => $item) {

				// Типографим данные
				Typograph::processItem($items[$k], ['name']);
				
				//SVG иконки - инлайн код
				$items[$k]['svgIcon'] = file_get_contents($_SERVER['DOCUMENT_ROOT'].$item['svgIcon']['src']);

			}
		}, 300);
	}

	public static function getShareList(): array {
		return self::getList('share');
	}


}
