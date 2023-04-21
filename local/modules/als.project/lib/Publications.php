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

class Publications
{
    public const IBLOCK_CODE = 'publications';

    private const PAGE_MAX_LIMIT = 5000;
// http://localhost:3000/local/api/?action=publications.getList&region=RU&lang=en

    private static $baseFilter = [
        'ACTIVE'      => 'Y',
    ];

    public static function getList(): ?array
    {
        $isAdmin = User::isAdmin();
        $filter = [
            'SECTION_ID' => self::getSection(strtoupper(LANGUAGE_CODE))['id'],
            'INCLUDE_SUBSECTIONS'=>'Y',
        ];
        $nav = false;
        $params = [
            'IBLOCK_CODE'  => self::IBLOCK_CODE,
            'FILTER'       => array_merge(self::$baseFilter, $filter),
            'SELECT'       => [
                'ID:int>id',
                'NAME>name',
                'PREVIEW_TEXT:string>previewText',
                'IBLOCK_SECTION_ID:int>sectionId',
                'ACTIVE_FROM>date',
                'PREVIEW_PICTURE:Image>previewImage',
                'PROPERTY_VIDEO>video',
                'PROPERTY_LINK>link',
                'PROPERTY_FILE:File>file',
            ],
            'NAV'          => $nav,
            '__SKIP_CACHE' => $isAdmin,
        ];

        return CacheManager::getIblockItemsFromCache(
            $params,
            static function (&$items) {
                foreach ($items as $k => &$item) {
                    // Типографим данные
                    Typograph::processItem($item, ['name', 'previewText']);

                    // Дата

                    $item['timestamp'] = (new \DateTime($item['date']))->getTimestamp();
                    $item['date'] = Help::formatDateHuman($item['date'], 'M YYYY');

                    if($item['sectionId']){
                        $sect=self::getSection($item['sectionId']);
                        $item['sectionName']=$sect['name'];
                        unset($item['sectionId']);
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
            'SELECT'      => ['ID:int>id','NAME>name'],
        ];

        if(is_numeric($codeOrId)){
            $params['FILTER']['ID']=$codeOrId;
        }else{
            $params['FILTER']['CODE']=$codeOrId;
        }

        $items = CacheManager::getIblockSectionsFromCache($params);
        return current($items);
    }

}