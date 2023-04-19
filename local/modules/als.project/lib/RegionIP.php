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

// Подключение SxGeo и определение страны (ISO-код) по ip
require_once $_SERVER['DOCUMENT_ROOT'] . '/local/tools/SxGeo/SxGeo.php';

class RegionIP {
	
	public const HLBLOCK_REGIONS = 'Regiony';

	public static function getRegion(){
		$SxGeo = new \SxGeo($_SERVER['DOCUMENT_ROOT'] . '/local/tools/SxGeo/SxGeo.dat');
		return $SxGeo->getCountry($_SERVER['REMOTE_ADDR']) ?: 'RU';
	}
	
	public static function getRegions(): array {
		
		$current = self::getRegion();
		
		$fieldName = LANGUAGE_CODE === 'en' ? 'UF_DESCRIPTION' : 'UF_NAME';
		
		$params = [
            'select' => [
                'ID:int>id',
                $fieldName.'>name',
                'UF_XML_ID:string>code',
            ]
        ];
        $ret = array_map(function ($el) use ($current) {
			
			$el['CURRENT'] = $el['code'] == $current;
			
			return $el;
        }, Hel::getList(self::HLBLOCK_REGIONS, $params));


        usort(
            $ret,
            static function ($a, $b) {
                if(in_array($a["name"], ['Россия','Russia'])){
                    return -1;
                }
                if ($a["name"] === $b["name"]) {
                    return 0;
                }
                return ($a["name"] < $b["name"]) ? -1 : 1;
            }
        );

	 return $ret;
	}
	
	public static function getRegionIdByISO(string $iso = 'RU'): int {
		$regions = self::getRegions();
		foreach($regions as $region){
			if($region['code'] == $iso){
				$regionId = $region['id'];
			}
		}
		return $regionId;
	}
}