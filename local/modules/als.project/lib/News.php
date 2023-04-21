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

class News {
	public const IBLOCK_CODE = 'news';

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

        $order = [
			'sort'=>'desc',
			'active_from'=>'desc',
		];

		$params = [
			'IBLOCK_CODE'   => self::IBLOCK_CODE,
			'FILTER'		=> array_merge(self::$baseFilter, $filter, $advFilter),
			'SELECT'		=> $select,
			'ORDER'		    => $order,
			'GET_ENUM_CODE' => 'Y',
			'__SKIP_CACHE'  => $isAdmin,
		];

		$items = CacheManager::getIblockItemsFromCache($params, static function (&$items) use ($isAdmin) {
			$iblockId = Help::getIblockIdByCode(self::IBLOCK_CODE);

            foreach ($items as $k=>$item) {
				// Типографим данные
				Typograph::processItem($items[$k], ['name','detailText']);//

				if (!$items[$k]['detailImage']) {
					$items[$k]['detailImage'] = $item['image'];
				}

				// Дата новости
				$items[$k]['date'] = Help::formatDateHuman($item['date'], 'DD MMMM YYYY');

				// SEO-теги публикации
				$seo = El::getSeo($iblockId, $item);
				$seo['title'] = $seo['title'] ?: $item['name'];

				$items[$k]['seo'] = $seo;

				// Ссылка на другой язык
				if($item['language_link']){
					$items[$k]['language_link'] = Advanced::langLink(CacheManager::getIblockItemsFromCache([
						'FILTER'		=> ['ID' => $item['language_link']],
						'SELECT'		=> ['DETAIL_PAGE_URL>language_link'],
						'__SKIP_CACHE'  => $isAdmin,
						'GET_NEXT'  => 'Y',
					], null, 300)[0]['language_link']);
				}
			}
		}, 300);

		return $items[0];
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

		//Данные корневого раздела
		$parent = self::getSection(strtoupper(LANGUAGE_CODE))[0];

		return [
			'id' => $parent['id'],
			'code' => $parent['code'],
			'name' => $parent['name'],
			'description' => $parent['description'],
			'image' => $parent['image'],
			'items' => CacheManager::getIblockItemsFromCache($params, static function(&$items) {
				foreach ($items as $k => $item) {
					// Типографим данные
					Typograph::processItem($items[$k], ['name', 'previewText']);

					// Дата новости
					$items[$k]['date'] = Help::formatDateHuman($item['date'], 'DD MMMM YYYY');
				}
			}, 300)
		];
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


	public static function getSections(): ?array {
		$isAdmin  = User::isAdmin();
		$iblockId = Help::getIblockIdByCode(self::IBLOCK_CODE, true);

		$filter['SECTION_ID'] = self::getSectionIdByCode(strtoupper(LANGUAGE_CODE));

		$params = [
			'IBLOCK_ID' => $iblockId,
			'FILTER' => array_merge(self::$baseFilter, $filter),
			'SELECT' => [
				'ID:int>id',
				'CODE:string>code',
				'NAME:string>name',
				'DESCRIPTION:string>description',
				'PICTURE:Image>image',
			],
			'__SKIP_CACHE' => $isAdmin,
		];

		//Данные корневого раздела
		$parent = self::getSection(strtoupper(LANGUAGE_CODE))[0];

		return [
			'id' => $parent['id'],
			'code' => $parent['code'],
			'name' => $parent['name'],
			'description' => $parent['description'],
			'image' => $parent['image'],
			'items' => CacheManager::getIblockSectionsFromCache($params, static function (&$items) use ($iblockId) {
				foreach ($items as $k => $section) {
					// Добавим SEO-параметры
					$items[$k]['seo'] = Sect::getSeo($iblockId, $section);
				}

				Typograph::processItems($items, ['name', 'description']);
			})
		];
	}

	public static function getSection($code): ?array {
		$isAdmin  = User::isAdmin();
		$iblockId = Help::getIblockIdByCode(self::IBLOCK_CODE, true);

		$filter = [
			'ID' => self::getSectionIdByCode($code),
		];

		$params = [
			'IBLOCK_ID' => $iblockId,
			'FILTER' => array_merge(self::$baseFilter, $filter),
			'SELECT' => [
				'ID:int>id',
				'CODE:string>code',
				'NAME:string>name',
				'DESCRIPTION:string>description',
				'PICTURE:Image>image',
			],
			'__SKIP_CACHE' => $isAdmin,
		];

		return CacheManager::getIblockSectionsFromCache($params, static function (&$items) {
			Typograph::processItems($items, ['name', 'description']);
		});
	}

}
