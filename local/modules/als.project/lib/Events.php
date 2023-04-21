<?php

namespace ALS\Project;


use ALS\Helper\CacheManager;
use ALS\Helper\Help;
use ALS\Helper\Typograph;

class Events
{
    public const IBLOCK_CODE = 'events';
    public const SHOWS_SECTION_CODE = 'shows';

    private static $baseFilter = [
        'ACTIVE'      => 'Y',
    ];

    public static function getList(array $filter): ?array
    {
        return self::getShows($filter);
    }

    public static function getShows(array $externalFilter): ?array
    {
        $isAdmin = User::isAdmin();
        $filter = [
            'SECTION_ID'          => self::getSection(strtoupper(LANGUAGE_CODE))['id'],
            'INCLUDE_SUBSECTIONS' => 'Y',
        ];
        if($externalFilter['old']){
            $filter['<DATE_ACTIVE_TO']= date('d.m.Y H:i:s');
        }else{
            $filter['>=DATE_ACTIVE_TO']= date('d.m.Y H:i:s');
        }
        $params = [
            'IBLOCK_CODE'  => self::IBLOCK_CODE,
            'FILTER'       => array_merge(self::$baseFilter, $filter),
            'SELECT'       => [
                'ID:int>id',
                'NAME>name',
                'PREVIEW_TEXT:string>previewText',
                'IBLOCK_SECTION_ID:int>sectionId',
                'ACTIVE_FROM>dateFrom',
                'ACTIVE_TO>dateTo',

                'PROPERTY_LINK>externalLink',
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
                foreach ($items as $k => &$item) {
                    // Типографим данные
                    Typograph::processItem($item, ['name', 'previewText']);

                    // Дата
                    $dateFrom = Help::formatDateHuman($item['dateFrom'], 'DD M YYYY');
                    $dateTo = Help::formatDateHuman($item['dateTo'], 'DD M YYYY');
                    if($item['dateFrom'] && $item['dateTo']){
                        $dateFromArr=explode( '&nbsp;',$dateFrom);
                        $dateToArr=explode( '&nbsp;',$dateTo);
                        $dateCurrArr=[date('d'),'',date('Y')];
                        if($dateFromArr && $dateToArr){

                            if($dateFromArr[1]===$dateToArr[1]){
                                $item['date']='';
                                if($dateFromArr[0]===$dateToArr[0]){
                                    $item['date'].=$dateFromArr[0];
                                }else{
                                    $item['date'].=$dateFromArr[0].'–'.$dateToArr[0];
                                }
                                $item['date'].=' '.$dateFromArr[1];
                            }else{
                                $item['date']=$dateFromArr[0].' '.$dateFromArr[1].'–'.$dateToArr[0].' '.$dateToArr[1];
                            }
                            $item['date'].=' '.$dateFromArr[2];
                        }
                        $item['date']=str_replace(' ', '&nbsp;', $item['date']);
                    }else{
                        $item['date']=$dateFrom ?? $dateTo;
                    }

                    $sectIds=\ALS\Helper\El::getGroups([$item['id']]);
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
            'SELECT'      => ['ID:int>id', 'NAME>name'],
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