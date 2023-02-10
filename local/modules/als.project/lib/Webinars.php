<?php

namespace ALS\Project;

use ALS\Helper\CacheManager;
use ALS\Helper\Help;
use ALS\Helper\Typograph;

class Webinars
{
    public const IBLOCK_CODE = 'webinars';
    private static $baseFilter = [
        'ACTIVE'      => 'Y',
    ];

    public static function getList(array $filter): ?array
    {

        return [
            'new'=>self::getWebinars($filter),
            'old'=>self::getWebinars($filter+['old'=>true]),
        ];
    }

    public static function getWebinars(array $externalFilter): ?array
    {
        $isAdmin = User::isAdmin();
        $filter = [
            'INCLUDE_SUBSECTIONS' => 'Y',
            'SECTION_ID'          => self::getSection(strtoupper(LANGUAGE_CODE))['id'],
        ];
        if($externalFilter['old']){
            $filter['<DATE_ACTIVE_FROM']= date('d.m.Y H:i:s');
        }else{
            $filter['>=DATE_ACTIVE_FROM']= date('d.m.Y H:i:s');
        }
        $params = [
            'IBLOCK_CODE'  => self::IBLOCK_CODE,
            'FILTER'       => array_merge(self::$baseFilter, $filter),
            'SELECT'       => [
                'ID:int>id',
                'NAME>name',
                'PREVIEW_TEXT:string>previewText',
                'IBLOCK_SECTION_ID:int>sectionId',
                'ACTIVE_FROM>date',

                'PROPERTY_LANGUAGE>language',
                'PROPERTY_THEME>theme',
                'PROPERTY_LINK>externalLink',
                'PROPERTY_YOUTUBE_CODE>videoCode',
            ],
            'ORDER'        => [
                'ACTIVE_FROM' => 'DESC'
            ],
            'NAV'          => false,
            '__SKIP_CACHE' => $isAdmin,
        ];

        return CacheManager::getIblockItemsFromCache(
            $params,
            static function (&$items) {
//                \Bitrix\Main\Diag\Debug::dumpToFile($items, 'items', 'LogItems');
                foreach ($items as $k => &$item) {
                    // Типографим данные
                    Typograph::processItem($item, ['name', 'previewText']);

                    // Дата
                    $dt=new \DateTime($item['date']);
                    if(LANGUAGE_CODE==='en'){
                        $item['date'] = FormatDate("d M Y H:i \G\M\T", MakeTimeStamp($item['date']));
                    }else{
                        $item['date'] = FormatDate("d M Y H:i", MakeTimeStamp($item['date']));
                    }
                    $sectIds=\ALS\Helper\El::getGroups([$item['id']]);
                    if(count($sectIds) > 1){
                        if(LANGUAGE_CODE==='en'){
                            $sect = self::getSection(207);
                        }else{
                            $sect = self::getSection(208);
                        }
                    }else{
                        $sect = self::getSection($sectIds[0]);
                    }
                    if($sect['img']){
                        $item['sectionImage'] = \CFile::GetPath($sect['img']);
                    }
                    foreach ($sectIds as $id){
                        $sect = self::getSection($id);
                        $item['sectionName'][] = $sect['name'];
                    }
                }
            },
            300
        );
    }

    public static function getSection($codeOrId): array
    {
        $params = [
            'IBLOCK_CODE' => self::IBLOCK_CODE,
            'FILTER'      => [],
            'SELECT'      => ['ID:int>id', 'NAME>name', 'PICTURE>img'],
        ];

        if (is_numeric($codeOrId)) {
            $params['FILTER']['ID'] = $codeOrId;
        } else {
            $params['FILTER']['CODE'] = $codeOrId;
        }

        $items = CacheManager::getIblockSectionsFromCache($params);
        return current($items);
    }


}
