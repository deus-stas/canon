<?php

namespace ALS\Project;

use ALS\Helper\CacheManager;
use ALS\Helper\El;
use ALS\Helper\Help;
use ALS\Helper\Typograph;

class Education
{
    public const IBLOCK_CODE = 'education';

    private static $baseFilter = [
        'ACTIVE'      => 'Y',
        'ACTIVE_DATE' => 'Y',
    ];

    public static function getItem($path): ?array
    {

        $advFilter = [
            'SECTION_ID'        => self::getSectionIdByCode(strtoupper(LANGUAGE_CODE)),
            '=PROPERTY_REGIONS' => REGION_ISO,
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
        // section only
        $items = CacheManager::getIblockSectionsFromCache(
            $params,
            static function (&$items) use ($isAdmin) {
                $iblockId = Help::getIblockIdByCode(self::IBLOCK_CODE);

                foreach ($items as $k => $item) {
                    // Типографим данные
                    Typograph::processItem($items[$k], ['name']);

                    if (!$items[$k]['detailImage']) {
                        $items[$k]['detailImage'] = $item['image'];
                    }

                    $items[$k]['menu'] = self::getMenu($item['code'], $item['id'], $item['depth'] > 1);
                    if (!$items[$k]['menu']) {
                        $items[$k]['menu'] = self::getMenu($item['code'], $item['parentSection'], $item['depth'] > 2);
                    }

                    // Дата
                    $items[$k]['date'] = Help::formatDateHuman($item['date'], 'DD MMMM YYYY');

                    // SEO-теги публикации
                    $seo = El::getSeo($iblockId, $item);
                    $seo['title'] = $seo['title'] ?: $item['name'];

                    $items[$k]['seo'] = $seo;

                    if($item['medialibrary']){
                        $items[$k]['medialibrary']=\ALS\Helper\Medialibrary::getMedialibraryItems($item['medialibrary']);
                    }

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
    private static function getMenu(string $code, $parentSection,$addAbout=false): array
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
                'PICTURE:Image>image',
            ],
            'GET_NEXT'      => 'Y',
            'GET_ENUM_CODE' => 'Y',
            '__SKIP_CACHE'  => $isAdmin,
        ];

        $items = CacheManager::getIblockSectionsFromCache(
            $params,
            static function (&$items) use ($isAdmin) {
                foreach ($items as &$item) {
                    Typograph::processItem($item, ['name']);
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
                    'name' => LANGUAGE_CODE === 'en' ? 'About' : 'Описание',
                ];
                $items = [$el, ...$items];
            }
        }

        return $items;
    }
    public static function prepareLink(string &$link): void{
        $link = strtolower($link);
        $link = str_replace(
            ['/'.self::IBLOCK_CODE.'/en', '/'.self::IBLOCK_CODE.'/ru'],
            ['/en/'.self::IBLOCK_CODE, '/'.self::IBLOCK_CODE],
            $link
        );
    }

}
