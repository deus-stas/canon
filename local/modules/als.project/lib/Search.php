<?php


namespace ALS\Project;

use ALS\Helper\Arr;
use ALS\Helper\El;
use ALS\Helper\TypeConvert;
use ALS\Helper\TypographLight;
use \Bitrix\Main\Loader;
use \CSearch;

class Search
{
    private $lang;
    private $typo;
    private $query;
    private $model;
    private $limit;
    private $ibList;
    private $section;
    private $dataStore;
    private $typeConverter;


    /**
     * SearchClass constructor.
     * @throws LoaderException
     */
    public function __construct()
    {
        $this->typo = new TypographLight;

        $this->typeConverter = new TypeConvert(
            array_merge(
                [
                    // поля из битрикса
                    'ITEM_ID:int>id',
                    'TITLE>title',
                    'BODY_FORMATED>searchTextFormat',
                    'RANK:float>rank',
                    'URL:string>url',

                    // поля, задаваемые текущим классом
                    'IS_SECTION:bool>isSection',
                    'DATE_CHANGE:string>dateChange',
                    //'SECTION_ID:int>blockPageSectionId',
                    //'SECTION_NAME:string>blockPageSectionName',
                    'model',
                    'section',
                    'section_code'
                ],
                []
            )
        );
    }


    /**
     * @throws Exception
     */
    public function find(): array
    {
        $this->getIblockList();

        return $this->getResult();
    }


    /**
     * @return array
     * @throws Exception
     */
    private function getResult(): array
    {
        $result = [
            'full' => [],
            'cnt' =>0,
        ];
        $searchData = $this->getSearchData();


        // Получаем данные организаций
        // todo: скорее всего не нужно кешировать данные
        $this->modifyOrgBySearchData($searchData);


        foreach ($searchData as $item) {
            self::prepareUrl($item["url"]);
            if(!self::validateElem($item["url"],$this->lang)) {
                continue;
            }
            $result['full'][]=[
                'name'  => $item["title"],
                'descr' => $item["searchTextFormat"],
                'link'  => $item["url"],
                //  'excluded'=>'',
            ];
        }

        $result['cnt'] = count($result['full']);

        return $result;
    }

    private static function prepareUrl(&$url): void{
        $url=strtolower($url);
        $re = '/([\?\&]sphrase_id=\d*)/m';
        $url= preg_replace($re, '', $url);

        $arrReplace=[
            '/products/en'=>'/en/products',
            '/products/ru'=>'/products',
            '/specialties/en'=>'/en/specialties',
            '/specialties/ru'=>'/specialties',
            '/news/en'=>'/en/news',
            '/news/ru'=>'/news',
            '/en/home/'=>'/en/',
            '/ru/home/'=>'/',
            '/ru/'=>'/',
        ];
        $url=str_replace(array_keys($arrReplace), array_values($arrReplace), $url);
    }

    private static $excludedUrl=[
        '/en/not-found/',
        '/not-found/',
    ];

    private static function validateElem($url, $lang): bool
    {
        if(in_array($url, self::$excludedUrl)){
            return false;
        }
        if ($lang === 'ru') {
            return strpos($url, '/en/') !== 0;
        }

        $langString = '/' . $lang . '/';
        return strpos($url, $langString) === 0;
    }

    /**
     * @return array
     * @throws Exception
     */
    private function getSearchData(): array
    {
        $content = [];
        $itemList = $this->getSearchResult();


        $this->getItemSection($itemList);

        foreach ($itemList as $item) {

            $ibData = $this->getItemIbData($item);

            $item['model'] = $ibData['code'];
            $item['section'] = $ibData['name'];

            $item['TITLE'] = $this->typo->getResult($item['TITLE']);
            $item['BODY_FORMATED'] = $this->typo->getResult($item['BODY_FORMATED']);

            $content[]=$item;
        }

        $this->clearContentFromDuplicates($content);


        return $this->typeConverter->convertDataTypes(array_values($content));
    }


    /**
     * @param $itemList
     *
     * @throws LoaderException
     */
    private function getItemSection(&$itemList): void
    {
        $itemIds = [];
        foreach ($itemList as $item) {
            $ibId = $item['PARAM2'];
            if (!empty($ibId)) {
                $itemId = $item['ITEM_ID'];

                if ($itemId[0] === 'S') {
                    $itemIds[$ibId]['sections'][] = str_replace('S', '', $itemId);
                } else {
                    $itemIds[$ibId]['items'][] = $itemId;
                }
            }
        }

        foreach ($itemIds as $ibId => $data) {
            if (!empty($data['items'])) {
                $params = [
                    'IBLOCK_ID' => $ibId,
                    'FILTER'    => ['ID' => $data['items'], 'CHECK_PERMISSIONS' => 'N'],
                    'SELECT'    => ['ID:int>id', 'IBLOCK_SECTION_ID:int>sectId'],
                ];
                $getList = El::getList($params);
                foreach ($getList as $id => $item) {
                    $itemKey = Arr::findInArr($itemList, 'ITEM_ID', $id, null);
                    $itemList[$itemKey]['SECTION_ID'] = $item['sectId'];
                }
            }

            if (!empty($data['sections'])) {
                $params = [
                    'IBLOCK_ID' => $ibId,
                    'FILTER'    => ['ID' => $data['sections'], 'CHECK_PERMISSIONS' => 'N'],
                    'SELECT'    => ['ID:int>id'],
                ];
                $getList = \ALS\Helper\Sect::getList($params);
                foreach ($getList as $id => $item) {
                    $itemKey = Arr::findInArr($itemList, 'ITEM_ID', 'S' . $id, null);
                    $itemList[$itemKey]['IS_SECTION'] = true;
                    $itemList[$itemKey]['SECTION_ID'] = $item['id'];
                }
            }
        }
    }


    /**
     * @return array
     * @throws LoaderException
     */
    private function getSearchResult(): array
    {
        Loader::includeModule('search');

        // Определение фильтра
        $filter = [
            'QUERY'       => $this->query,
            'SITE_ID'     => SITE_ID,
            'CHECK_DATES' => 'Y',
        ];

        // modifyFilterParam
        $this->modifyFilterParam($filter);

        // Определение направления сортировки
        $order = [
            'CUSTOM_RANK' => 'DESC',
            'DATE_FROM'   => 'DESC',
        ];

        $search = new CSearch;
        $search->Search($filter, $order);

        /*
         * Костыль
         * Если нет результатов то пробуем найти с отключенной морфологией
         */
        if (!$search->selectedRowsCount()) {
            $search->Search($filter, $order, ['STEMMING' => false]);
        }

        $items = [];
        $search->NavStart($this->limit);

        while ($item = $search->Fetch()) {
            $items[] = $item;
        }

        return $items;
    }


    /**
     * Метод модифицируе параметры фильтра в зависимости от заданных условий].
     *
     * @param array $filter
     */
    private function modifyFilterParam(array &$filter): void
    {
        /*
         * Если указано модель то ограничивает поиск по инфоблоку
         */
        if (!empty($this->model)) {
            $ibId = Iblock::getIblockIdByCode($this->model);
            $filter['MODULE_ID'] = 'iblock';
            $filter['PARAM2'] = $ibId;

            /*
             * Если модель - Новости
             * И указан раздел
             * Ограничиваем поиск по разделу
             */
            if ($this->model === Message::IBLOCK_CODE['bank'] && !empty($this->section)) {
                $type = MessageFacade::TYPE_BANK;
                $messages = new Message(Message::IBLOCK_CODE[$type], Message::HL_CODE[$type . 'Tags']);
                $sectionList = $messages->getAllSectionList();

                foreach ($this->section as $item) {
                    $filter['PARAMS']['iblock_section'][] = Arr::findInArr($sectionList, 'code', $item, 'id');
                }
            }
        }
    }


    /**
     * Метод получаем данные по финансовым организациям
     *
     * @param array $searchData
     *
     * @throws LoaderException
     */
    private function modifyOrgBySearchData(array &$searchData): void
    {
        // Получаем все ID организаций из выдачи
        $orgIds = [];
        foreach ($searchData as $item) {
            if ($item['model'] === 'FINANCE_ORGANIZATION') {
                $orgIds[] = $item['id'];
            }
        }

        // Получаем данные по организациям
        $orgList = [];
        if (!empty($orgIds)) {
            $query = [
                'filter' => [
                    'id' => $orgIds
                ],
            ];
            $result = (new Listing())->setQuery($query)->get();
            foreach ($result as $item) {
                $orgId = $item['id'];
                $orgList[$orgId] = $item;
            }
        }

        // Модифицируем данные
        foreach ($searchData as $k => $item) {
            $itemId = $item['id'];
            if ($item['model'] === 'FINANCE_ORGANIZATION' && !empty($orgList[$itemId])) {
                $searchData[$k]['company'] = $orgList[$itemId];
            }
        }
    }


    /**
     * Метод получает данные инфоблока указанного элемента
     * @param array $item
     * @return array
     */
    private function getItemIbData(array $item = []): array
    {
        $ibId = $item['PARAM2'];
        $ibData = $this->ibList[$ibId];
        if (!empty($ibData)) {
            return $ibData;
        }

        return [];
    }


    /**
     * Метод удаляет дубликаты в результатах поиска
     * @param array $content - Ссылка на массив с результатами поиска
     */
    private function clearContentFromDuplicates(&$content)
    {
        // Сделаем хешированный одномерный массив карту для поиска дублей
        $hashMap = [];

        foreach ($content as $item) {
            $hashMap[] = md5(serialize([$item['ITEM_ID']]));
        }


        // Определяем ключи массива с дубликатами
        $duplicateKeys = [];

        foreach ($hashMap as $hash) {
            $duplicateKeysForHash = self::array_search_all($hash, $hashMap);

            if (count($duplicateKeysForHash) > 1) {
                array_shift($duplicateKeysForHash);

                foreach ($duplicateKeysForHash as $removableHash) {
                    $duplicateKeys[] = $removableHash;
                }
            }
        }

        $duplicateKeys = array_unique($duplicateKeys);


        // А теперь удаляем
        if (is_array($duplicateKeys) && count($duplicateKeys) > 0) {
            foreach ($duplicateKeys as $removableItemKey) {
                unset($content[$removableItemKey]);
            }
        }
    }


    /**
     * Метод получает список инфоблоков.
     */
    private function getIblockList(): void
    {
        $iblockList = El::getList(
            [
                'SELECT' => ['ID:int>id', 'CODE:string>code', 'NAME:string>name', 'IBLOCK_TYPE_ID:string>type'],
                'FILTER' => []
            ]
        );


        $this->ibList = $iblockList;
    }


    /**
     * http://php.net/manual/ru/function.array-search.php#88465
     * @param $needle
     * @param $haystack
     * @return mixed
     */
    private static function array_search_all($needle, $haystack)
    {
        $array = [];

        foreach ($haystack as $k => $v) {
            if ($haystack[$k] === $needle) {
                $array[] = $k;
            }
        }

        return $array;
    }


    public function setLang(string $lang): self
    {
        $this->lang = $lang;
        return $this;
    }


    public function setLimit(int $limit): self
    {
        $this->limit = $limit;
        return $this;
    }


    public function setQuery(string $query): self
    {
        $this->query = $query;
        return $this;
    }


    public function setModel(string $model): self
    {
        $this->model = $model;
        return $this;
    }

}