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

class Pages {
	public const IBLOCK_CODE = 'structure';

	private const PAGE_MAX_LIMIT = 5000;


	private static $baseFilter = [
		'ACTIVE' => 'Y',
		'ACTIVE_DATE' => 'Y',
	];

	public static function getItem(array $filter): ?array {
		if (!is_array($filter)) { return null; }

		$advFilter = [
			'SECTION_ID' => self::getSectionIdByCode(strtoupper(LANGUAGE_CODE)),
			'INCLUDE_SUBSECTIONS' => 'Y',
			'=PROPERTY_REGIONS' => REGION_ISO,
		];

		$isAdmin = User::isAdmin();

		$select = [
			'ID:int>id',
			'CODE>code',
			'NAME>name',
			//'IBLOCK_SECTION_ID:int>sectionCode',
			'ACTIVE_FROM>date',
			'PREVIEW_PICTURE:Image>previewImage',
			'DETAIL_TEXT>detailText',
			'DETAIL_PICTURE:Image>detailImage',
			'PROPERTY_LANGUAGE_LINK>language_link',
		];

		$params = [
			'IBLOCK_CODE'   => self::IBLOCK_CODE,
			'FILTER'		=> array_merge(self::$baseFilter, $filter, $advFilter),
			'SELECT'		=> $select,
			'GET_ENUM_CODE' => 'Y',
			'__SKIP_CACHE'  => $isAdmin,
		];

		$page = CacheManager::getIblockItemsFromCache($params, static function (&$items) {
			$iblockId = Help::getIblockIdByCode(self::IBLOCK_CODE);

			/*$sections = [];

			foreach (self::getSections() as $section) {
				$sections[$section['id']] = $section;
			}*/

			foreach ($items as $k=>$item) {
				// Типографим данные
				Typograph::processItem($items[$k], ['name']);

				if (!$items[$k]['detailImage']) {
					$items[$k]['detailImage'] = $item['image'];
				}

				// Дата
				$items[$k]['date'] = Help::formatDateHuman($item['date'], 'DD MMMM YYYY');

				// Раздел
				/*$section = $sections[$item['sectionCode']];
				$items[$k]['sectionCode'] = $section['code'];*/

				// SEO-теги публикации
				$seo = El::getSeo($iblockId, $item);
				$seo['title'] = $seo['title'] ?: $item['name'];

				$items[$k]['seo'] = $seo;

				// Ссылка на другой язык
				if($item['language_link']){
					$items[$k]['language_link'] = Advanced::langLink(CacheManager::getIblockItemsFromCache([
						'FILTER'		=> ['ID' => $items[$k]['language_link']],
						'SELECT'		=> ['DETAIL_PAGE_URL>language_link'],
						'__SKIP_CACHE'  => $isAdmin,
						'GET_NEXT'  => 'Y',
					], null, 300)[0]['language_link']);
				}

			}
		}, 300)[0];

		//Страница не найдена - возможно это страница раздела
		if(empty($page)){
			$page = self::getSectionByCode($filter['CODE'])[0];
		}

		return $page;
	}

	public static function getList(array $filter = [], array $params = []): array {
		$isAdmin = User::isAdmin();

		if(!$filter['SECTION_ID']){
			$filter['SECTION_ID'] = self::getSectionIdByCode(strtoupper(LANGUAGE_CODE));
		};

		$nav = [];

		$limit = (int) $params['navigation']['limit'];
		$page = (int) $params['navigation']['page'] + 1;

		if ($limit < 1 || $limit > self::PAGE_MAX_LIMIT) {
			$limit = self::PAGE_MAX_LIMIT;
		}

		if ($page > 1) {
			$nav = [
				'nPageSize' => $limit,
				'iNumPage'  => $page,
				'checkOutOfRange' => true,
			];
		} else {
			$nav = [
				'nTopCount' => $limit,
			];
		}

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
				//'IBLOCK_SECTION_ID:int>sectionCode',
				'ACTIVE_FROM>date',
				'PREVIEW_PICTURE:Image>previewImage',
			],
			'NAV' => $nav,
			'__SKIP_CACHE' => $isAdmin,
		];

		return CacheManager::getIblockItemsFromCache($params, static function(&$items) {

			/*$sections = [];

			foreach (self::getSections() as $section) {
				$sections[$section['id']] = $section;
			}*/

			foreach ($items as $k => $item) {

				// Типографим данные
				Typograph::processItem($items[$k], ['name', 'previewText']);

				// Дата
				$items[$k]['date'] = Help::formatDateHuman($item['date'], 'DD MMMM YYYY');

				// Раздел
				/*$section = $sections[$item['sectionCode']];
				$items[$k]['section'] = $section;
				unset($items[$k]['sectionCode']);*/


			}
		}, 300);
	}

	public static function getSectionIdByCode($code): int {
		$params = [
			'IBLOCK_CODE' => self::IBLOCK_CODE,
			'FILTER' => ['CODE' => $code],
			'SELECT' => ['ID:int>id'],
		];

		$items = CacheManager::getIblockSectionsFromCache($params);

		return $items[0]['id'] ?: 0;
	}


	public static function getSectionByCode(string $section = null): ?array {
		$isAdmin  = User::isAdmin();
		$iblockId = Help::getIblockIdByCode(self::IBLOCK_CODE, true);

		$section = $section ?: strtoupper(LANGUAGE_CODE);

		$filter['ID'] = self::getSectionIdByCode($section);

		$params = [
			'IBLOCK_ID' => $iblockId,
			'FILTER' => array_merge(self::$baseFilter, $filter),
			'SELECT' => [
				'ID:int>id',
				'CODE:string>code',
				'NAME:string>name',
				'DESCRIPTION:string>detailText',
				'PICTURE:Image>detailImage',
				'SECTION_PAGE_URL:string>language_link',
			],
			'GET_NEXT' => 'Y',
			'__SKIP_CACHE' => $isAdmin,
		];

		return CacheManager::getIblockSectionsFromCache($params, static function (&$items) use ($iblockId) {
			foreach ($items as $k => $section) {
				Typograph::processItem($items[$k], ['name', 'detailText']);

				// Добавим SEO-параметры
				$items[$k]['seo'] = Sect::getSeo($iblockId, $section);

				$items[$k]['previewImage'] = '';
				$items[$k]['language_link'] = Advanced::langLink($section['language_link']);

			}

		});
	}


}
