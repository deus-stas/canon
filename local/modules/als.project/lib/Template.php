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

class Template {
	public const IBLOCK_CODE = 'template';

	private const PAGE_MAX_LIMIT = 1;

	private static $baseFilter = [
		'ACTIVE' => 'Y',
		'ACTIVE_DATE' => 'Y',
	];


	public static function getSettings(): array {
		$isAdmin = User::isAdmin();

		$filter = [
			'CODE' => strtoupper(LANGUAGE_CODE),
		];

		$params = [
			'IBLOCK_CODE' => self::IBLOCK_CODE,
			'ORDER' => [
				'ACTIVE_FROM' => 'DESC',
				'SORT' => 'ASC'
			],
			'FILTER' => array_merge(self::$baseFilter, $filter),
			'SELECT' => [
				//'ID:int>id',
				'CODE>language',
				'NAME>name',
				'PROPERTY_TEXT_REGULATIONS:string>textRegulations',
				'PROPERTY_REGULATIONS:File>regulations',
				'PROPERTY_TEXT_PRIVACY_POLICY:string>textPrivacyPolicy',
				'PROPERTY_PRIVACY_POLICY:File>privacyPolicy',
				'PROPERTY_TEXT_COPYRIGHT:string>copyright',
				'PROPERTY_TEXT_MENU:string>textMenu',
				'PROPERTY_TEXT_SEARCH:string>textSearch',
				'PROPERTY_TEXT_REGION:string>textRegion',
				'PROPERTY_TEXT_PHONE:string>textPhone',
				'DETAIL_TEXT:string>phone',
				'PREVIEW_TEXT:string>address',
				'PROPERTY_TEXT_SHARE:string>textShare',
				'PROPERTY_TEXT_404_HOME:string>text404Home',
				'PROPERTY_TEXT_404_DESCRIPTION:Html>text404Description',
				'PROPERTY_RECAPTCHA_KEY:string>recaptchaKey',
				'PROPERTY_LOGO:File>logo',
				'PROPERTY_LOGO_TEXT:File>logoText',
				'PROPERTY_LOGO_MOBILE:File>mobileLogo',
				'PROPERTY_COUNTRY_MESSAGE:html>countryMessage',
				'PROPERTY_WORKING_CONDITIONS_TITLE:string>workingConditionsTitle',
				'PROPERTY_WORKING_CONDITIONS_FILE:File>workingConditionsFile',
			],
			'__SKIP_CACHE' => $isAdmin,
		];

		return CacheManager::getIblockItemsFromCache($params, static function(&$items) {

			foreach ($items as $k => $item) {

				// Типографим данные
				Typograph::processItem($items[$k], ['name', 'textRegulations', 'textPrivacyPolicy', 'text404Description']);

				$items[$k]['language'] = strtolower($item['language']);

				$year = date('Y');
				$items[$k]['copyright'] = '© '.($year=='2021' ? $year : '2021–'.$year).' '.$item['copyright'];

			}
		}, 300);
	}



}
