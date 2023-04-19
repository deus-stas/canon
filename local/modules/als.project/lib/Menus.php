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

class Menus {

	private const PAGE_MAX_LIMIT = 5000;
	private const IBLOCK_CODE = 'structure';
	private const DEPTH_LEVEL = 3;

	private static $baseFilter = [
		'ACTIVE' => 'Y',
		'ACTIVE_DATE' => 'Y',
	];

	public static function getMenu(string $type = ''): array {


		//получаем разделы
		$sections = self::getSections($type);
		
		//получаем элементы
		$pages = self::getItems($type);
		
		//сортировка по полю "сортировка" всего списка
        $items = array_merge($sections, $pages);
        usort($items, fn($a, $b) => ($a['sort'] === $b['sort']) ? 0 : ($a['sort'] < $b['sort']) ? -1 : 1);
		return $items;
	}
	
	public static function getItems(string $type = ''): ?array {
		$isAdmin  = User::isAdmin();
		
		$filter = [
			'SECTION_ID' => self::getSectionIdByCode(self::IBLOCK_CODE, strtoupper(LANGUAGE_CODE)),
			'INCLUDE_SUBSECTIONS' => 'Y',
			'DEPTH_LEVEL' => self::DEPTH_LEVEL,
		];
		
		if($type == 'top'){
			$filter['=PROPERTY_SHOW_MENU_TOP_VALUE'] = 'Да';
		}
		
		if($type == 'bottom'){
			$filter['=PROPERTY_SHOW_MENU_BOTTOM_VALUE'] = 'Да';
		}
		
		$params = [
			'IBLOCK_CODE' => self::IBLOCK_CODE,
			'ORDER' => [
				//'SORT' => 'ASC',
				'NAME' => 'ASC',
			],
			'FILTER' => array_merge(self::$baseFilter, $filter),
			'SELECT' => [
				'ID:int>id',
				'NAME>name',
				'CODE:string>code',
				'DETAIL_PAGE_URL:string>link',
				'IBLOCK_SECTION_ID:int>parent_id',
				'PROPERTY_MENU_CAP:string>cap',
				'SORT:int>sort',
			],
			'GET_NEXT' => 'Y',
			'__SKIP_CACHE' => $isAdmin,
		];

		return CacheManager::getIblockItemsFromCache($params, static function(&$items) {
			foreach ($items as $k => $item) {
                $items[$k]['id'].='id';
				// Типографим данные
				Typograph::processItem($items[$k], ['name']);
				
				if($item['parent_id'] == self::getSectionIdByCode(self::IBLOCK_CODE, strtoupper(LANGUAGE_CODE))){
					$items[$k]['parent_id'] = null;
				}
				
				if($item['cap'] == 'Да'){
					$item['link'] = preg_replace('/'.$item['code'].'\/?/', '', $item['link']);
				}
				
				$items[$k]['link'] = Advanced::langLink($item['link']);
				
				unset($items[$k]['cap']);
			}
		}, 300);
	}
	
	public static function getSections(string $type = ''): ?array {
		$isAdmin  = User::isAdmin();
		
		$filter = [
			'SECTION_ID' => self::getSectionIdByCode(self::IBLOCK_CODE, strtoupper(LANGUAGE_CODE)),
			//'INCLUDE_SUBSECTIONS' => 'Y',
			//'DEPTH_LEVEL' => self::DEPTH_LEVEL,
		];
		
		if($type == 'top'){
			$filter['=UF_SHOW_MENU_TOP'] = true;
		}
		
		if($type == 'bottom'){
			$filter['=UF_SHOW_MENU_BOTTOM'] = true;
		}
		
		$params = [
			'IBLOCK_ID' => Help::getIblockIdByCode(self::IBLOCK_CODE, true),
			'ORDER' => [
				//'SORT' => 'ASC',
				'NAME' => 'ASC',
			],
			'FILTER'	=> array_merge(self::$baseFilter, $filter),
			'SELECT' => [
				'ID:int>id',
				'NAME:string>name',
				'CODE:string>code',
				'SECTION_PAGE_URL:string>link',
				'IBLOCK_SECTION_ID:int>parent_id',
				'UF_IBLOCK_CODE>iblockCode',
				'SORT:int>sort',
			],
			'GET_NEXT' => 'Y',
			'__SKIP_CACHE' => $isAdmin,
		];

		$sections = CacheManager::getIblockSectionsFromCache($params, static function (&$sections) {
			foreach ($sections as $k => $section) {
				Typograph::processItem($sections[$k], ['name']);
                $sections[$k]['name']=str_replace(' &','&nbsp;&', $section['name']);
				$sections[$k]['link'] = Advanced::langLink($section['link']);
			}
		});


		foreach ($sections as $k => $section) {
			//если у раздела указан инфоблок - берем его разделы как подпункты
			if($section['iblockCode']){
				
				if($iblockId = Help::getIblockIdByCode($section['iblockCode'])){
				
					//если у инфоблока есть пользовательское поле Регионы - фильтруем по ним
					$properties = \Bitrix\Main\UserFieldTable::getList(array(
						'filter' => array('ENTITY_ID' => 'IBLOCK_'.$iblockId.'_SECTION')
					))->fetchAll();
					
					$propertyName = null;
					
					foreach($properties as $property){
						if($property['XML_ID'] == 'REGIONS'){
							$propertyName = $property['FIELD_NAME'];
							break;
						}
					}
					
					$filter = [];
					
					if ($propertyName) {
						$filter['='.$propertyName] = RegionIP::getRegionIdByISO(REGION_ISO);
					}
					
					$subSections = self::getSubSections($section['iblockCode'], $section['id'], $filter);

					$sections = array_merge($sections, $subSections);
				}
				
			}
			if($section['parent_id'] == self::getSectionIdByCode(self::IBLOCK_CODE, strtoupper(LANGUAGE_CODE))){
				$sections[$k]['parent_id'] = null;
			}
			unset($sections[$k]['iblockCode']);
		}
		
		return $sections;
	}
	
	public static function getSubSections($iblockCode, $parentId, array $filterParam = []): array {
		$isAdmin  = User::isAdmin();
		$filter = array_merge(
            $filterParam,
			[
				'SECTION_ID' => self::getSectionIdByCode($iblockCode, strtoupper(LANGUAGE_CODE)),
				'CNT_ACTIVE' => 'Y',
			],
			self::$baseFilter,
		);
		if($filterParam['SECTION_ID']){
            $filter['SECTION_ID']=$filterParam['SECTION_ID'];
        }
		
		$params = [
			'IBLOCK_ID' => Help::getIblockIdByCode($iblockCode),
			'ORDER' => [
				//'SORT' => 'ASC',
				'NAME' => 'ASC',
			],
			'FILTER'	=> $filter,
			'SELECT' => [
				'ID:int>id',
				'NAME:string>name',
				'CODE:string>code',
				'SECTION_PAGE_URL:string>link',
				'SORT:int>sort',
			],
			'GET_NEXT' => 'Y',
			'__SKIP_CACHE' => $isAdmin,
		];
		
		$sections = CacheManager::getIblockSectionsFromCache($params, static function (&$items) {
			Typograph::processItems($items, ['name']);
		});
		
		foreach($sections as $k => $section){

            if(!$filterParam['SECTION_ID']){
                $subsections=self::getSubSections($iblockCode,$section['id'],$filterParam+['SECTION_ID'=>$section['id']]);
            }

            if(!$subsections){
                $section['link'] = preg_replace('/'.$section['code'].'\/?/', '#'.$section['code'], $section['link']);
            }
			$sections[$k]['id'] = $parentId.'-'.$section['id'];

			$sections[$k]['link'] = Advanced::langLink($section['link']);

			$sections[$k]['parent_id'] = $parentId;
			//unset($sections[$k]['code']);
		}
		
		return $sections;
	}
	
	public static function getSectionIdByCode($iblockCode, $code): int {
		$params = [
			'IBLOCK_CODE' => $iblockCode,
			'FILTER' => ['CODE' => $code],
			'SELECT' => ['ID:int>id'],
		];

		$items = CacheManager::getIblockSectionsFromCache($params);

		return $items[0]['id'] ?: 0;
	}
	
	public static function getSectionCodeById($iblockCode, $id): string {
		$params = [
			'IBLOCK_CODE' => $iblockCode,
			'FILTER' => ['ID' => $id],
			'SELECT' => ['CODE:string>code'],
		];

		$items = CacheManager::getIblockSectionsFromCache($params);

		return $items[0]['code'] ?: '';
	}
	
	public static function getTopMenu(): array {
		return self::getMenu('top');
	}

	public static function getBottomMenu(): array {
		return self::getMenu('bottom');
	}

}
