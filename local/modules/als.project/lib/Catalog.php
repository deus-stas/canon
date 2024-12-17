<?php

namespace ALS\Project;

use ALS\Helper\CacheManager;
use ALS\Helper\El;
use ALS\Helper\Hel;
use ALS\Helper\Help;
use ALS\Helper\Sect;
use ALS\Helper\Typograph;

class Catalog
{
    public const IBLOCK_CODE = 'catalog';

    private const PAGE_MAX_LIMIT = 5000;

    private static $baseFilter = [
        'ACTIVE'      => 'Y',
        'ACTIVE_DATE' => 'Y',
    ];

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
            //'IBLOCK_SECTION_ID:int>sectionCode',
            'ACTIVE_FROM>date',
            'DEPTH_LEVEL>depth',
            'UF_DETAIL_PICTURE:Image>previewImage',
            'UF_TEXT>detailText',
            'UF_MEDIALIBRARY_COLLECTION>medialibrary',
            'DETAIL_PICTURE:Image>detailImage',
            'PROPERTY_LANGUAGE_LINK>language_link',
            'TAGS>tags',
            'UF_REGIONS:array>regions',
            'UF_NAME_BTN>name_btn',
            'UF_URL_BTN>url_btn',
            'UF_VIEW:string>disable_tabs',
            'UF_TWO_BANNER:string>two_banner',
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

                    // Вывод без табов
                    if($items[$k]['disable_tabs']){
                        $items[$k]['disable_tabs'] = true;
                    }else{
                        $items[$k]['disable_tabs'] = false;
                    }

                    // Вывод второго баннера над галереей
                    if($items[$k]['two_banner']){
                        $items[$k]['two_banner'] = true;
                    }else{
                        $items[$k]['two_banner'] = false;
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

                    if ($item['medialibrary']) {
                        $items[$k]['medialibrary'] = \ALS\Helper\Medialibrary::getMedialibraryItems(
                            $item['medialibrary']
                        );
                    }

                    // Дата
                    $items[$k]['date'] = Help::formatDateHuman($item['date'], 'DD MMMM YYYY');

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


    private static function getMenu(string $code, $parentSection, $addAbout = false): array
    {
        $isAdmin = User::isAdmin();

        $params = [
            'IBLOCK_CODE'   => self::IBLOCK_CODE,
            'FILTER'        => [
                'SECTION_ID' => $parentSection,
                'ACTIVE'     => 'Y',
                'UF_DONT_PRODUCT' => false,
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


    public static function prepareLink(string &$link): void
    {
        $link = strtolower($link);
        $link = str_replace(
            ['/products/en', '/products/ru'],
            ['/en/products', '/products'],
            $link
        );
    }

    public static function getList(array $filter = [], array $params = []): array
    {
        $isAdmin = User::isAdmin();

        if (!$filter['SECTION_ID']) {
            $filter['SECTION_ID'] = self::getSectionIdByCode(strtoupper(LANGUAGE_CODE));
        }

        $filter['=PROPERTY_REGIONS'] = REGION_ISO;
        $filter['UF_DONT_PRODUCT'] = false;
        $nav = [];

        $limit = (int)$params['navigation']['limit'];
        $page = (int)$params['navigation']['page'] + 1;

        if ($limit < 1 || $limit > self::PAGE_MAX_LIMIT) {
            $limit = self::PAGE_MAX_LIMIT;
        }

        if ($page > 1) {
            $nav = [
                'nPageSize'       => $limit,
                'iNumPage'        => $page,
                'checkOutOfRange' => true,
            ];
        } else {
            $nav = [
                'nTopCount' => $limit,
            ];
        }

        $params = [
            'IBLOCK_CODE'  => self::IBLOCK_CODE,
            'ORDER'        => [
                'ACTIVE_FROM' => 'DESC',
                'SORT'        => 'ASC',
            ],
            'FILTER'       => array_merge($filter, self::$baseFilter),
            'SELECT'       => [
                'ID:int>id',
                'CODE>code',
                'NAME>name',
                'PREVIEW_TEXT:string>previewText',
                //'IBLOCK_SECTION_ID:int>sectionCode',
                'ACTIVE_FROM>date',
                'PREVIEW_PICTURE:Image>previewImage',
            ],
            'NAV'          => $nav,
            '__SKIP_CACHE' => $isAdmin,
        ];

        //Данные корневого раздела
        $parent = self::getSection(strtoupper(LANGUAGE_CODE))[0];

        return [
            'id'          => $parent['id'],
            'code'        => $parent['code'],
            'name'        => $parent['name'],
            'description' => $parent['description'],
            'image'       => $parent['image'],
            'items'       => CacheManager::getIblockItemsFromCache(
                $params,
                static function (&$items) {
                    $sections = [];

                    foreach (self::getSections() as $section) {
                        $sections[$section['id']] = $section;
                    }

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
                },
                300
            ),
        ];
    }

    public static function getSectionIdByCode($code, $parentSectionId = false): int
    {
        $params = [
            'IBLOCK_CODE' => self::IBLOCK_CODE,
            'FILTER'      => ['CODE' => $code],
            'SELECT'      => ['ID:int>id'],
        ];
        if ($parentSectionId) {
            $params['FILTER']['SECTION_ID'] = $parentSectionId;
        }

        $items = CacheManager::getIblockSectionsFromCache($params);

        return $items[0]['id'] ?: 0;
    }

    public static function getSections(array $paramsFunc = []): ?array
    {
        $isAdmin = User::isAdmin();
        $iblockId = Help::getIblockIdByCode(self::IBLOCK_CODE, true);

        $filter['SECTION_ID'] = self::getSectionIdByCode(strtoupper(LANGUAGE_CODE));

        $params = [
            'IBLOCK_ID'    => $iblockId,
            'ORDER'        => [
                'SORT' => 'ASC',
                'NAME' => 'ASC',
            ],
            'FILTER'       => array_merge($filter, self::$baseFilter),
            'SELECT'       => [
                'ID:int>id',
                'CODE:string>code',
                'NAME:string>name',
                'DESCRIPTION:string>description',
                'PICTURE:Image>image',
                'SECTION_PAGE_URL>link',
                'UF_REGIONS:array>regions',
            ],
            'GET_NEXT'     => 'Y',
            '__SKIP_CACHE' => $isAdmin,
        ];
        if ($paramsFunc['SECTION_ID']) {
            $params['FILTER']["SECTION_ID"] = $paramsFunc['SECTION_ID'];
            // todo remove
            //    see($params['FILTER']);
            unset($params['FILTER']["=UF_REGIONS"]);
            //  see($params["=UF_REGIONS"]);

        }
        //Данные корневого раздела
        $parent = self::getSection(strtoupper(LANGUAGE_CODE))[0];

        $regionId = RegionIP::getRegionIdByISO($paramsFunc['region']);

        return [
            'id'          => $parent['id'],
            'code'        => $parent['code'],
            'name'        => $parent['name'],
            'description' => $parent['description'],
            'image'       => $parent['image'],
            'items'       => CacheManager::getIblockSectionsFromCache(
                $params,
                static function (&$items) use ($iblockId, $paramsFunc, $regionId) {
                    foreach ($items as $k => $section) {
                        // Добавим SEO-параметры
                        $items[$k]['seo'] = Sect::getSeo($iblockId, $section);
                        self::prepareLink($items[$k]['link']);

                        if (!$paramsFunc['SECTION_ID']) {
                            $extraParams = [
                                'SECTION_ID' => $section['id']
                            ];
                            $subsections = self::getSections($paramsFunc + $extraParams);

                            if ($regionId) {
                                foreach ($subsections['items'] as $kk => $v) {
                                    if ($v["regions"] && !in_array($regionId, $v["regions"])) {
                                        unset($subsections['items'][$kk]);
                                    }
                                }
                                $subsections['items'] = array_values($subsections['items']);
                            }
                            if ($subsections['items']) {
                                $items[$k]['children'] = $subsections['items'];
                            }
                        }
                    }

                    Typograph::processItems($items, ['name']);
                }
            ),
        ];
    }

    public static function getSection($code): ?array
    {
        $isAdmin = User::isAdmin();
        $iblockId = Help::getIblockIdByCode(self::IBLOCK_CODE, true);

        $filter = [
            'ID' => self::getSectionIdByCode($code),
        ];

        $params = [
            'IBLOCK_ID'    => $iblockId,
            'FILTER'       => array_merge(self::$baseFilter, $filter),
            'SELECT'       => [
                'ID:int>id',
                'CODE:string>code',
                'NAME:string>name',
                'DESCRIPTION:string>description',
                'PICTURE:Image>image',
            ],
            '__SKIP_CACHE' => $isAdmin,
        ];

        return CacheManager::getIblockSectionsFromCache(
            $params,
            static function (&$items) {
                Typograph::processItems($items, ['name', 'description']);
            }
        );
    }

}
