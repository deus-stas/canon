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

class Landings {
	public const IBLOCK_CODE = 'landings';

	private const PAGE_MAX_LIMIT = 5000;


	private static $baseFilter = [
		'ACTIVE' => 'Y',
		'ACTIVE_DATE' => 'Y',
	];


    public static function prepareLink(string &$link): void
    {
        $link = strtolower($link);
        $link = str_replace(
            ['/landings/en', '/landings/ru'],
            ['/en/landings', '/landings'],
            $link
        );
    }
    private static function getMenu(string $code, $parentSection, $addAbout = false): array
    {
        $isAdmin = User::isAdmin();

        $params = [
            'IBLOCK_CODE'   => self::IBLOCK_CODE,
            'FILTER'        => [
                'SECTION_ID' => $parentSection,
                'ACTIVE'     => 'Y',
            ],
            'SELECT'        => [
                'NAME>name',
                'SECTION_PAGE_URL>link',
                'UF_REGIONS:array>regions',
                'PICTURE:Image>image',
            ],
            'GET_NEXT'      => 'Y',
            'GET_ENUM_CODE' => 'Y',
            '__SKIP_CACHE'  => $isAdmin,
        ];

        $curRegionId = RegionIP::getRegionIdByISO(REGION_ISO);

        $items = CacheManager::getIblockSectionsFromCache(
            $params,
            static function (&$items) use ($isAdmin, $curRegionId) {
                foreach ($items as $k => &$item) {
                    if ($item["regions"] && $curRegionId && !in_array($curRegionId, $item["regions"])) {
                        unset($items[$k]);
                        continue;
                    }
                    //  Typograph::processItem($item, ['name']);
                    self::prepareLink($item['link']);
                }
            }
        );

        if ($addAbout && $items) {
            $link = current($items)["link"];

            $linkArr = explode('/', $link);
            $linkArr = array_slice(
                $linkArr,
                0,
                -2
            ); // -2 т.к. последний элемент секций path пустой из-за закрывающего слеша

            $link = implode('/', $linkArr) . '/';
            if ($link) {
                $el = [
                    'link' => $link,
                    'name' => LANGUAGE_CODE === 'en' ? 'About' : 'О продукте',
                ];
                $items = [$el, ...$items];
            }
        }

        return array_values($items);
    }

    public static function getItem(string $path): ?array
    {
        $advFilter = [
            'SECTION_ID' => self::getSectionIdByCode(strtoupper(LANGUAGE_CODE)),
        ];
        if ($path) {
            $pathArr = explode('/', $path);
            $advFilter['CODE'] = end($pathArr);
            foreach ($pathArr as $k => $sectionCode) {
                if (count($pathArr) === $k + 1) {
                    continue;
                }
                $advFilter['SECTION_ID'] = self::getSectionIdByCode($sectionCode, $advFilter['SECTION_ID']);
            }
        }

        $isAdmin = User::isAdmin();

        $select = [
            'ID:int>id',
            'CODE>code',
            'IBLOCK_SECTION_ID>parentSection',
            'NAME>name',
            'PROPERTY_DATE_START>date',
            'DEPTH_LEVEL>depth',
            'PROPERTY_LANGUAGE_LINK>language_link',
            'TAGS>tags',
            'UF_MIN_PICTURE:Image>min_image',
            'UF_FULL_NAME:string>full_name',
            'UF_NAME_BLOCK_DAYS:string>name_block_days',
            'UF_THEME:string>theme',
            'UF_DESCRIPTION:text>full_description',
            'UF_VIDEO:text>iframe_video',
            'PICTURE:Image>image',
            'DESCRIPTION:text>description',
            'UF_BANNER_DESCRIPTION:text>banner_description',
            'UF_DATE_PLACE:text>date_place',
            'UF_DESCRIBE_BLOCK_ONE:text>desc_block1',
            'UF_DESCRIBE_BLOCK_TWO:text>desc_block2',
            'UF_DESCRIBE_BLOCK_THREE:text>desc_block3',
            'UF_DESCRIBE_BLOCK_FOUR:text>desc_block4',
            'UF_LANDING_CODE:string>landing_code',
        ];

        $nav = [
            'nPageSize' => 1
        ];

        $params = [
            'IBLOCK_CODE'   => self::IBLOCK_CODE,
            'FILTER'        => array_merge(self::$baseFilter, $advFilter),
            'SELECT'        => $select,
            'NAV'           => $nav,
            'GET_ENUM_CODE' => 'Y',
            '__SKIP_CACHE'  => $isAdmin,
        ];

        $curRegionId = RegionIP::getRegionIdByISO(REGION_ISO);


        // section only
        $items = CacheManager::getIblockSectionsFromCache(
            $params,
            static function (&$items) use ($isAdmin, $curRegionId) {
                $iblockId = Help::getIblockIdByCode(self::IBLOCK_CODE);

                foreach ($items as $k => $item) {
                    if ($item['regions'] && $curRegionId && !in_array($curRegionId, $item['regions'])) {
                        unset($items[$k]);
                        continue;
                    }

                    // Типографим данные
                    Typograph::processItem($items[$k], ['name']);

                    if (!$items[$k]['detailImage']) {
                        $items[$k]['detailImage'] = $item['image'];
                    }

                    $items[$k]['menu'] = self::getMenu($item['code'], $item['id'], $item['depth'] > 2);
                    if (!$items[$k]['menu']) {
                        $items[$k]['menu'] = self::getMenu($item['code'], $item['parentSection'], $item['depth'] > 3);
                    }

                    if(count($items[$k]['menu'])>=10){
                        $items[$k]['menu']=[];
                    }

                    if($item['depth'] == 5){
                        $section = CacheManager::getIblockSectionsFromCache(
                            [
                                'IBLOCK_CODE' => self::IBLOCK_CODE,
                                'FILTER'      => ['SECTION_ID' => $item['parentSection']],
                                'SELECT'      => ['NAME>name'],
                            ]
                        );
                        $section = $section ? current($section) : null;
                        if ($section['name']) {
                            $items[$k]['currentSectionName'] = $section['name'];
                        }
                    }
                    if ($item['parentSection']) {
                        $section = CacheManager::getIblockSectionsFromCache(
                            [
                                'IBLOCK_CODE' => self::IBLOCK_CODE,
                                'FILTER'      => ['ID' => $item['parentSection']],
                                'SELECT'      => ['NAME>name'],
                            ]
                        );
                        $section = $section ? current($section) : null;
                        if ($section['name']) {
                            $items[$k]['parentSectionName'] = $section['name'];
                        }
                    }

                    // SEO-теги публикации
                    $seo = Sect::getSeo($iblockId, $item);
                    $seo['title'] = $seo['title'] ?: $item['name'];

                    $items[$k]['seo'] = $seo;

                    // Ссылка на другой язык
                    $items[$k]['language_link'] = Advanced::langLink(
                        CacheManager::getIblockItemsFromCache(
                            [
                                'FILTER'       => ['ID' => $items[$k]['language_link']],
                                'SELECT'       => ['DETAIL_PAGE_URL>language_link'],
                                '__SKIP_CACHE' => $isAdmin,
                                'GET_NEXT'     => 'Y',
                            ],
                            null,
                            300
                        )[0]['language_link']
                    );
                }
            }
        );
        return $items[0];
    }
    public static function getDay(array $filter): ?array
    {
        if (!is_array($filter)) { return null; }

        $advFilter = [
            'INCLUDE_SUBSECTIONS' => 'Y',
            '=PROPERTY_REGIONS' => REGION_ISO,
        ];

        $isAdmin = User::isAdmin();

        $select = [
            'ID:int>id',
            'CODE>code',
            'NAME>name',
            //'IBLOCK_SECTION_ID:int>sectionCode',
            'PROPERTY_DATE_START>date',
            'PREVIEW_PICTURE:Image>previewImage',
            'DETAIL_TEXT>detailText',
            'DETAIL_PICTURE:Image>detailImage',
            'PROPERTY_PREVIEW_PICTURE:Image>previewImage',
            'PROPERTY_DETAIL_THEME:string>detail_theme_day',
            'PROPERTY_FULL_NAME:string>full_name',
	        'PROPERTY_BANNER_DESCRIPTION:text>banner_description',
	        'PROPERTY_SOURCES:text>sources',
            'PROPERTY_NAME_BLOCK_SPIKERS:string>name_block_spikers',
            'PROPERTY_LANGUAGE_LINK>language_link',
            'PROPERTY_SPIKERS:array>spikers',
            'PROPERTY_NAME_URL:string>name_url',
            'PROPERTY_EN_SPIKERS:array>en_spikers',
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
            if(!$filter['IBLOCK_CODE']){
                $filter['SECTION_ID'] = self::getSectionIdByCode(strtoupper(LANGUAGE_CODE));
            }
		}

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
			'IBLOCK_CODE' => ($filter['IBLOCK_CODE'])?:self::IBLOCK_CODE,
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
				'DETAIL_TEXT:string>detailText',
				//'IBLOCK_SECTION_ID:int>sectionCode',
                'PROPERTY_DATE_START>date',
				'PREVIEW_PICTURE:Image>icon',
				'DETAIL_PICTURE:Image>detailImage',
				'PROPERTY_PREVIEW_PICTURE:Image>previewImage',
				'PROPERTY_THEME:string>theme_day',
                'PROPERTY_FULL_NAME:string>full_name',
				'PROPERTY_DETAIL_THEME:string>detail_theme_day',
				'PROPERTY_POSITION:array>position',
				'PROPERTY_BANNER_DESCRIPTION:text>banner_description',
				'PROPERTY_SOURCES:text>sources',
				'PROPERTY_SPIKERS:array>spikers',
                'PROPERTY_NAME_URL:string>name_url',
				'PROPERTY_EN_SPIKERS:array>en_spikers',
			],
			'NAV' => $nav,
			'__SKIP_CACHE' => $isAdmin,
		];

		//Данные корневого раздела
		$parent = self::getSection(strtoupper(LANGUAGE_CODE))[0];

        // Получение спикеров
        $params_spikers = [
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
//                'PROPERTY_DATE_START>date',
                'PREVIEW_PICTURE:Image>previewImage',
                'PROPERTY_SPIKERS:array>spikers',
                'PROPERTY_EN_SPIKERS:array>en_spikers',
            ],
            'NAV' => $nav,
            '__SKIP_CACHE' => $isAdmin,
        ];
        $spikers = CacheManager::getIblockItemsFromCache($params_spikers, static function(&$items) {
            foreach ($items as $k => $item) {
                // Типографим данные
                Typograph::processItem($items[$k], ['name', 'previewText']);
            }
        }, 300);

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
				'UF_START_IMAGE:Image>start_image',
				'UF_START_TEXT:text>start_text',
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
