<?php

use ALS\Project;
use Bitrix\Main\Localization\Loc;

// Подключение Bitrix
require_once $_SERVER['DOCUMENT_ROOT'] . '/local/tools/include-bitrix.php';

// Входные параметры
ob_start();
$input = file_get_contents('php://input');
$requestData = $input ? json_decode($input, true) : $_REQUEST;

define('LANGUAGE_CODE', $requestData['lang'] ?: 'ru');
define('REGION_ISO', $requestData['region'] ?: Project\RegionIP::getRegion());

Loc::setCurrentLang(LANGUAGE_CODE);

// Определение результата
$result = null;

switch ($requestData['action']) {
    // Данные для шаблона
    case 'template.getSettings':
        $result = Project\Template::getSettings();
        break;

    // Всякие вспомогательные элементы
    case 'advanced.getMainpageBlocks':
        $params = [];

        if ($requestData['limit']) {
            $params['limit'] = $requestData['limit'];
        }

        $result = Project\Advanced::getMainpageBlocks($params);
        break;

    // Регионы
    case 'tools.getRegion':
        $result = [
            'IP' => $_SERVER['REMOTE_ADDR'],
            'REGION' => REGION_ISO,
        ];
        break;

    case 'tools.getRegions':
        $result = Project\RegionIP::getRegions();
        break;

    // Меню
    case 'menus.getTopMenu':
        $result = Project\Menus::getTopMenu();
        break;

    case 'menus.getBottomMenu':
        $result = Project\Menus::getBottomMenu();
        break;

    // Слайдер
    case 'slider.getSlider':
        $params = [];

        $params['navigation'] = [
            'limit' => $requestData['limit'] ?: 0,
        ];

        $result = Project\Sliders::getSlider($params);
        break;


    // Новости
    case 'news.getList':
        $params = $filter = [];

        if ($requestData['section']) {
            if (!is_numeric($requestData['section'])) {
                $requestData['section'] = Project\News::getSectionIdByCode($requestData['section']);
            }

            $filter['SECTION_ID'] = (int)$requestData['section'];
        }

        $params['navigation'] = [
            'limit' => $requestData['limit'] ?: 0,
            'page' => $requestData['page'] ?: 0,
        ];

        $result = Project\News::getList($filter, $params);
        break;

    case 'news.getItem':
        if (!empty($requestData['code'])) {
            $result = Project\News::getItem([
                'CODE' => $requestData['code'] ?: false,
            ]);
        }
        break;

    case 'news.getSections':
        $result = Project\News::getSections();
        break;

    // Публикации
    case 'publications.getList':
        $result = Project\Publications::getLIst();
        break;

    // Страницы
    case 'pages.getList':
        $params = $filter = [];

        if ($requestData['section']) {
            if (!is_numeric($requestData['section'])) {
                $requestData['section'] = Project\Pages::getSectionIdByCode($requestData['section']);
            }

            $filter['SECTION_ID'] = (int)$requestData['section'];
        }

        $params['navigation'] = [
            'limit' => $requestData['limit'] ?: 0,
            'page' => $requestData['page'] ?: 0,
        ];

        $result = Project\Pages::getList($filter, $params);
        break;

    case 'pages.getItem':
        if (!empty($requestData['code'])) {
            $code=explode('/', $requestData['code']);
            $code=end($code);

            $result = Project\Pages::getItem([
                'CODE' => $code ?: false,
            ]);
        }
        break;

    // Каталог
    case 'catalog.getList':
        $params = $filter = [];

        if ($requestData['section']) {
            if (!is_numeric($requestData['section'])) {
                $requestData['section'] = Project\Catalog::getSectionIdByCode($requestData['section']);
            }

            $filter['SECTION_ID'] = (int)$requestData['section'];
        }

        $params['navigation'] = [
            'limit' => $requestData['limit'] ?: 0,
            'page' => $requestData['page'] ?: 0,
        ];

        $result = Project\Catalog::getList($filter, $params);
        break;

    case 'catalog.getItem':
        if (!empty($requestData['code'])) {
            $result = Project\Catalog::getItem($requestData['code']);
        }
        break;

    case 'catalog.getSections':
        $params['region'] = REGION_ISO;
        $result = Project\Catalog::getSections($params);
        break;

    // События
    case 'events.getList':
        $params['region'] = REGION_ISO;
        if ($requestData['old']) {
            $params['old']=true;
        }
        $result = Project\Events::getList($params);
        break;

    // Вебинары
    case 'webinars.getList':
        $params['region'] = REGION_ISO;
        $result = Project\Webinars::getList($params);
        break;

    // Вакансии
    case 'vacancies.getList':
        $params['region'] = REGION_ISO;
        $result = Project\Vacancies::getList($params);
        break;

    // Соц.Сети
    case 'socials.getList':
        $result = Project\Socials::getList();
        break;

    case 'socials.getShareList':
        $result = Project\Socials::getShareList();
        break;

    // Формы
    case 'forms.getForm':
        if ($requestData['code']) {
            $result = Project\Forms::getForm($requestData['code']);
        }
        break;

    case 'forms.saveForm':
        $result = Project\Forms::saveForm($requestData);
        break;

    case 'forms.getFeedback':
        $result = Project\Forms::getFeedback();
        break;

    // specialties
    case 'specialties.getItem':
        if (!empty($requestData['code'])) {
            $result = \ALS\Project\Specialties::getItem($requestData['code']);
        }
        break;

    // education
    case 'education.getItem':
        if (!empty($requestData['code'])) {
            $result = \ALS\Project\Education::getItem($requestData['code']);
        }
        break;

    // Поиск
    case 'search.getList':
        $query = !empty($requestData['q']) && is_string($requestData['q']) ? $requestData['q'] : null;
        $lang=LANGUAGE_CODE;
        if ($query !== null) {
            $result = (new \ALS\Project\Search())
                ->setLimit(250)
                ->setLang($lang)
                ->setQuery($query)
                ->find();
        }
        break;

    default:
        $result = [];
}


ob_get_clean();


// Возвращаем результат
header('Content-Type: application/json; charset=utf-8');
echo json_encode($result, JSON_UNESCAPED_UNICODE);
