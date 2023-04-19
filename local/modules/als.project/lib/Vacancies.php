<?php

namespace ALS\Project;
use ALS\Helper\CacheManager;
use ALS\Helper\Help;
use ALS\Helper\Typograph;
use ALS\Helper\Hel;

class Vacancies
{
    public const IBLOCK_CODE = 'vacancies';
    private static $baseFilter = [
        'ACTIVE' => 'Y',
    ];


    public static function getList(array $externalFilter): ?array
    {

        $isAdmin = User::isAdmin();
        $filter = [
            'INCLUDE_SUBSECTIONS' => 'Y',
            'SECTION_ID'          => self::getSection(strtoupper(LANGUAGE_CODE))['id'],
        ];

        $params = [
            'IBLOCK_CODE'  => self::IBLOCK_CODE,
            'FILTER'       => array_merge(self::$baseFilter, $filter),
            'SELECT'       => [
                'ID:int>id',
                'NAME>name',
                'PREVIEW_TEXT:string>previewText',

                'PROPERTY_COUNTRY>country',
                'PROPERTY_CITY>city',
                'PROPERTY_CITY_ENUM_ID:string>cityEn',
                'PROPERTY_LINK>externalLink',
            ],
            'ORDER'        => [
                'ACTIVE_FROM' => 'DESC'
            ],
            'NAV' => false,
            'GET_ENUM_CODE' => 'Y',
            '__SKIP_CACHE' => $isAdmin,
        ];

        return CacheManager::getIblockItemsFromCache(
            $params,
            static function (&$items) {
                foreach ($items as $k => &$item) {
                    if(LANGUAGE_CODE === 'en'){
                        $item['city']=$item['cityEn'];
                    }
                    unset($item['cityEn']);
                    // Типографим данные
                    Typograph::processItem($item, ['name', 'previewText']);

                    if($item["country"]){
                        $hParams=[
                            'filter'=>[
                                'UF_XML_ID'=>$item["country"],
                            ],
                            'select'=>[
                                'UF_NAME',
                                'UF_DESCRIPTION',
                            ]
                        ];
                        $hItems=Hel::getList(RegionIP::HLBLOCK_REGIONS,$hParams);
                        $hItems=current($hItems);
                        if ($hItems && $hItems["UF_NAME"]) {
                            $item["country"] = LANGUAGE_CODE === 'en' ? $hItems["UF_DESCRIPTION"] : $hItems["UF_NAME"];
                        }
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