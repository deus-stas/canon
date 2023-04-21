<?php

namespace ALS\Helper;

use Bitrix\Iblock\InheritedProperty\ElementValues;
use CIBlock;
use CIBlockElement;
use CIBlockProperty;
use CIBlockPropertyEnum;
use CModule;


class El {

    // =========================================================================
    // ================================= CRUD ==================================
    // =========================================================================

    /**
     * Функция для добавления элемента инфоблока
     * @param array $params Поля и свойства нового элемента
     * @return int|string ID нового элемента или код ошибки в строке
     */
    public static function add($params) {
        CModule::IncludeModule('iblock');

        $fields = $params;

        // Определение ID инфоблока
        $iblockId = null;

        if (!empty($fields['TYPE'])) {
            $iblockId = Help::getOpt('IBLOCK_' . $fields['TYPE'] . '_ID');

        } elseif (!empty($fields['IBLOCK_ID'])) {
            $iblockId = $fields['IBLOCK_ID'];

        } elseif (!empty($fields['IBLOCK_CODE'])) {
            $iblockId = Help::getIblockIdByCode($fields['IBLOCK_CODE']);

        }

        $fields['IBLOCK_ID'] = $iblockId;
        unset($fields['TYPE']);


        $el = new CIBlockElement;
        if ($ELEMENT_ID = $el->Add($fields)) {
            return $ELEMENT_ID;
        }

        return 'Error: ' . $el->LAST_ERROR;
    }


    /**
     * Функция возвращает массив с данными об элементах инфоблока
     * @param array $params Параметры выборки
     * @return array
     */
    public static function getList($params) {
        CModule::IncludeModule('iblock');


        // Определение ID инфоблока
        $iblockId = false;
        if (!empty($params['IBLOCK_ID'])) {
            $iblockId = (int)$params['IBLOCK_ID'];

        } elseif (!empty($params['TYPE'])) {
            $iblockId = Help::getOpt('IBLOCK_' . $params['TYPE'] . '_ID');

        } elseif (!empty($params['IBLOCK_CODE'])) {
            $iblockId = Help::getIblockIdByCode($params['IBLOCK_CODE']);
        }


        // Определение направления сортировки
        $order = ['SORT' => 'ASC'];
        if (!empty($params['ORDER'])) {
            $order = $params['ORDER'];
        }


        // Определение фильтра
        $filter = [];
        if (!empty($params['FILTER'])) {
            $filter = $params['FILTER'];
        }
        $filter['SHOW_HISTORY'] = 'Y';

        if ($iblockId) {
            $filter['IBLOCK_ID'] = $iblockId;
        }


        // Определение группиировки
        $group = $params['GROUP'] ?: false;


        // Определение постраничной навигации
        $nav = $params['NAV'] ?: false;


        // Определение полей выборки
        $typeConverter = new TypeConvert($params['SELECT'] ?: []);
        $select = array_merge($typeConverter->getSelect(), ['ID', 'IBLOCK_ID']);


        // Выборка результата из базы
        $rsElement = CIBlockElement::GetList(
            $order,
            $filter,
            $group,
            $nav,
            $select
        );

        $result = [];
        if ($params['GET_NEXT'] === 'Y') {
            while ($element = $rsElement->GetNext(true, false)) {
                if ($element['ID']) {
                    $key = $element['ID'];
                    $result[$key] = $element;
                } else {
                    $result[] = $element;
                }
            }

        } else {
            while ($element = $rsElement->Fetch()) {
                if ($element['ID']) {
                    $key = $element['ID'];

                    if ($result[$key]) {
                        $result[$key] = Arr::getMergeExt(
                            $result[$key],
                            $element
                        );

                    } else {
                        $result[$key] = $element;

                    }
                } else {
                    $result[] = $element;
                }
            }

        }


        // Обработка результата
        if ($params['SELECT_PROP_NAMES'] === 'Y' && $params['TYPE']) {
            $propData = [];
            $propertyList = self::getPropertyList([
                'TYPE' => $params['TYPE'],
            ]);

            // todo: скорее всего в связи с типами в SELECT может не работать
            foreach ($select as $selectCode) {
                $matches = [];

                if (preg_match('/^PROPERTY_(\w+)$/', $selectCode, $matches)) {
                    foreach ($propertyList as $property) {
                        if ($property['CODE'] === $matches[1]) {
                            $propData[$matches[1]] = $property['NAME'];
                        }
                    }
                }
            }

            foreach ($result as $key => $element) {
                $result[$key]['PROP_NAMES'] = $propData;
            }
        }

        foreach ($result as $key => $element) {
            if (!is_array($element)) {
                continue;
            }

            foreach ($element as $keyField => $valField) {
                $matches = [];

                if (preg_match('/^PROPERTY_(\w+)_VALUE$/', $keyField, $matches)) {
                    if (is_string($valField)) {
                        $result[$key]['PROP'][$matches[1]] = trim($valField);
                    } else {
                        $result[$key]['PROP'][$matches[1]] = $valField;
                    }
                }
            }
        }

        if ($params['GET_ENUM_CODE'] === 'Y') {
            // Если необходима выборка кодов свойств типа «список»
            $enumXmlID = [];

            foreach ($result as $key => $element) {
                if (!is_array($element)) {
                    continue;
                }

                foreach ($element as $keyField => $valField) {
                    $matches = [];

                    if (preg_match('/^PROPERTY_(\w+)_ENUM_ID$/', $keyField, $matches)) {
                        // Если поле относится к свойству «список»

                        if ($enumXmlID[$valField]) {
                            $xmlID = $enumXmlID[$valField];

                        } else {
                            $propEnum = CIBlockPropertyEnum::GetByID($valField);
                            $xmlID = $propEnum['XML_ID'];
                            $enumXmlID[$valField] = $xmlID;

                        }

                        $result[$key]['PROPERTY_' . $matches[1] . '_ENUM_ID_VALUE'] = $xmlID;
                    }
                }
            }
        }


        // Приведем массив к нужным типам данных
        if ($typeConverter->getTypes()) {
            $result = $typeConverter->convertDataTypes($result);
        }


        return $params['ASSOC'] === 'Y'
            ? $result
            : array_values($result);
    }


    /**
     * Функция обновляет поля и свойства элемента
     * @param int $id ID изменяемой записи
     * @param array $params Массив полей [FIELDS] и свойств [PROPS/PROPERTY_VALUES]
     * @return bool|string
     */
    public static function update($id, $params) {
        CModule::IncludeModule('iblock');


        // Поля элемента
        $element = $params['FIELDS'] ?: $params;

        $removeFields = ['PROPERTY_VALUES', 'PROPS', 'TYPE', 'IBLOCK_CODE'];

        foreach ($removeFields as $field) {
            if (isset($element[$field])) {
                unset($element[$field]);
            }
        }


        // Свойства элемента
        $props = [];
        if ($params['PROPERTY_VALUES']) {
            $props = $params['PROPERTY_VALUES'];
            unset($params['PROPERTY_VALUES']);

        } elseif ($params['PROPS']) {
            $props = $params['PROPS'];
            unset($params['PROPS']);
        }


        // Обновление в БД
        $el = new CIBlockElement;
        $res = $el->Update($id, $element);

        if ($res === true && is_array($props)) {
            // Определение ID инфоблока
            $iblockId = is_numeric($params['IBLOCK_ID'])
                ? $params['IBLOCK_ID']
                : Help::getIblockIdByCode($params['IBLOCK_CODE']);

            if ($iblockId) {
                CIBlockElement::SetPropertyValuesEx($id, $iblockId, $props);
            }

            return true;
        }

        return $el->LAST_ERROR;
    }


    /**
     * Функция удаляет элемент инфоблока
     * @const object $DB Класс для работы с базой данной
     * @param int $id ID удаляемого элемента
     * @return boolean true, если удаление прошло успешно и false, если нет
     */
    public static function delete($id) {
        if ((int)$id) {
            CModule::IncludeModule('iblock');
            global $DB;

            $DB->StartTransaction();
            if (!CIBlockElement::Delete($id)) {
                $DB->Rollback();

            } else {
                $DB->Commit();

                return true;
            }
        }

        return false;
    }


    // =========================================================================
    // ======================== ДОПОЛНИТЕЛЬНЫЕ ФУНКЦИИ =========================
    // =========================================================================

    /**
     * Функция деактивирует элемент с указанным кодом
     * @param int $id ID элемента
     * @return array
     */
    public static function deactivate($id) {
        $result = self::update(
            $id,
            [
                'FIELDS' => [
                    'ACTIVE' => 'N',
                ],
            ]
        );

        return $result;
    }


    /**
     * Метод возвращает набор полей и свойств для элемента инфоблока
     *
     * ВНИМАНИЕ!
     * Сохранение картинок и текстов для визивига пока не гарантируется, надо писать и проверять
     *
     * @param array $item - Элемент инфоблока
     * @param array $fieldCodes - Массив соответствий полей элемента к полям в битриксе
     * @param array $propCodes - Массив соответствий полей элемента к свойствам в битриксе
     * @return array - Массив с полями [FIELDS] и [PROPS]
     */
    public static function getBitrixData($item, $fieldCodes, $propCodes) {
        $fields = [];
        $props = [];

        foreach ($item as $field => $value) {
            $bxFieldCode = $fieldCodes[$field];

            // Корректируем значение
            $valueType = 'any';
            $valueCorrected = $value;

            // Преобразуем картинки в формат пригодный для битрикс
            if (is_array($valueCorrected) && $valueCorrected['src']) {
                $imagePath = $_SERVER['DOCUMENT_ROOT'] . $valueCorrected['src'];
                $valueCorrected = \CFile::MakeFileArray($imagePath);
                $valueType = 'image';
            }

            // Кастомная логика сохранения картинок блока
            if ($bxFieldCode === 'PREVIEW_PICTURE' || $bxFieldCode === 'DETAIL_PICTURE') {
                // Если картинка не задана или не задан src, удалим её
                // https://dev.1c-bitrix.ru/api_help/iblock/classes/ciblockelement/update.php
                if (!$value || !$value['src']) {
                    $valueCorrected = ['del' => 'Y'];
                }
            }


            // Разделяем значения на поля и свойства
            if ($bxFieldCode) {
                if ($field === 'active') {
                    $fields[$bxFieldCode] = $valueCorrected ? 'Y' : 'N';

                } else if ($field === 'previewText') {
                    $fields['PREVIEW_TEXT_TYPE'] = 'html';
                    $fields['PREVIEW_TEXT_TEXT'] = $valueCorrected;

                } else if ($field === 'detailText') {
                    $fields['DETAIL_TEXT_TYPE'] = 'html';
                    $fields['DETAIL_TEXT_TEXT'] = $valueCorrected;

                } else {
                    $fields[$bxFieldCode] = $valueCorrected;
                }

            } else {
                if (is_array($valueCorrected) && empty($valueCorrected)) {
                    // Пустой массив множественного свойства не сохранится
                    // //dev.1c-bitrix.ru/api_help/iblock/classes/ciblockelement/setpropertyvaluesex.php
                    $valueCorrected = false;
                }

                if (is_array($valueCorrected) && !$valueCorrected[0]['DESCRIPTION'] && $valueType !== 'image') {
                    // Если свойство множественное и не используется DESCRIPTION,
                    // то добавим его, иначе порядок значений в битриксе
                    // может не измениться
                    foreach ($valueCorrected as $valueField => $valueCorrectedItem) {
                        $valueCorrected[$valueField] = [
                            'VALUE' => $valueCorrectedItem,
                            'DESCRIPTION' => mt_rand(0, 9999999),
                        ];
                    }
                }

                $propName = isset($propCodes[$field]) ? $propCodes[$field] : strtoupper($field);
                $props[$propName] = $valueCorrected;
            }
        }

        return [
            'FIELDS' => $fields,
            'PROPS' => $props,
        ];
    }


    /**
     * Функция принимает свойства элемента, выбирает аналогичный из базы
     * и сравнивает их
     * @param array $element Массив со свойствами и параметрами элемента
     * @return array
     */
    public static function getDiff($element) {
        if (!$element['TYPE']) {
            return null;
        }
        if (!(int)$element['_DIFF_ID']) {
            return null;
        }

        $diff = [];

        // Определим перечень сравниваемых полей и свойств
        $fieldsExclude = ['TYPE', '_DIFF_INFO', '_DIFF_ID'];
        $fields = [];

        foreach ($element as $field => $value) {
            if (!in_array($field, $fieldsExclude, false)) {
                $fields[] = $field;
            }
        }

        $props = [];
        if (is_array($element['PROPERTY_VALUES'])) {
            foreach ($element['PROPERTY_VALUES'] as $code => $value) {
                $props[] = $code;
            }
        }
        // ---------------------------------------------------------------------


        // Выборка элемента из базы
        $select = $fields;
        foreach ($props as $code) {
            $select[] = 'PROPERTY_' . $code;
        }

        $elementListInBase = self::getList([
            'TYPE'   => $element['TYPE'],
            'FILTER' => ['ID' => $element['_DIFF_ID']],
            'SELECT' => $select,
        ]);

        $elementInBase = end($elementListInBase);
        // ---------------------------------------------------------------------


        // Сравнение элементов
        $element1 = $element;
        foreach ($fieldsExclude as $field) {
            unset($element1[$field]);
        }

        if (is_array($element1['PROPERTY_VALUES'])) {
            foreach ($element1['PROPERTY_VALUES'] as $field => $value) {
                $element1['PROPERTY_' . $field . '_VALUE'] = $value;
            }
        }
        unset($element1['PROPERTY_VALUES']);


        $element2 = $elementInBase;
        if (is_array($element2)) {
            unset($element2['PROP']);
            foreach ($element2 as $field => $value) {
                if (preg_match('/PROPERTY_\w+_ENUM_ID/', $field)) {
                    unset($element2[$field]);
                }

                if (preg_match('/PROPERTY_\w+_VALUE/', $field)) {
                    unset($element2[$field]);
                }
            }

            $diff = array_merge(
                array_diff($element1, $element2),
                array_diff($element2, $element1)
            );
        }
        // ---------------------------------------------------------------------


        return $diff;
    }


    /**
     * Функция возвращает поле элемента инфоблока
     * @param int $id
     * @param string $code
     * @return bool
     */
    public static function getField($id, $code) {
        if ((int)$id) { return null; }
        if (!$code) { return null; }


        // Выборка элементов из базы
        $query = [
            'FILTER' => ['ID' => $id],
            'SELECT' => [$code],
        ];

        if ($code === 'DETAIL_PAGE_URL') {
            $query['GET_NEXT'] = 'Y';
        }

        $elements = self::getList($query);

        if ($elements) {
            foreach ($elements as $element) {
                return $element[$code];
            }
        }

        return false;
    }


    /**
     * Метод получает разделы к которым принадлежит эдемент.
     *
     * @param array $elementId    - Массив элементов
     * @param bool  $bElementOnly - Указывает на необходимость выборки привязок и из свойств типа "Привязка к разделу".
     *
     * @return array
     */
    public static function getGroups(array $elementId, bool $bElementOnly = false): array
    {
        CModule::IncludeModule('iblock');

        if (!empty($elementId)) {
            $groups = [];
            $res = CIBlockElement::GetElementGroups($elementId, $bElementOnly);
            while($item = $res->Fetch()) {
                $groups[] = (int) $item['ID'];
            }

            return $groups;
        }

        return [];
    }


    /**
     * Функция возвращает ID элемента
     * @param string $ib Тип инфоблока из опций модуля
     * @param string $code Код элемента
     * @param array $params Массив доп.параметров <br>
     *    <li> Если нужно создать ненайденный элемент, то передать "FORCE_CREATE" => "Y"
     *    <li> Если нужно создать с заранее заданными параметрами, то передать их можно в ключе "NEW_ELEMENT"
     * @return int ID раздела
     */
    public static function getIdByElementCode($ib, $code, array $params = []) {

        // Выборка элемента по символьному коду
        $elements = self::getList([
            'TYPE'   => $ib,
            'FILTER' => ['CODE' => $code],
        ]);


        // Проверка результата
        if (count($elements) > 1) {
            return null;
        }

        if (count($elements) === 1) {
            $element = end($elements);
            return $element['ID'];
        }

        if ($params['FORCE_CREATE'] === 'Y') {
            $elementNewFields = [
                'TYPE' => $ib,
                'NAME' => $code,
                'CODE' => $code,
            ];

            if (is_array($params['NEW_ELEMENT'])) {
                $elementNewFields = $params['NEW_ELEMENT'];
            }

            $elementNewId = self::add($elementNewFields);

            if ((int)$elementNewId) {
                return $elementNewId;
            }
        }

        return null;
    }


    /**
     * Функция возвращает ID элемента
     * @param string $code Внешний код инфоблока
     * @param string $xml_id XML_ID раздела
     * @return array
     */
    public static function getIdByXmlId($code, $xml_id) {
        $result = null;

        if ($code && $xml_id) {
            $elementQuery = [
                'IBLOCK_CODE' => $code,
                'FILTER'      => ['XML_ID' => $xml_id],
            ];

            $elementList = self::getList($elementQuery);
            $element = array_shift($elementList);

            if ($element && $element['ID']) {
                $result = $element['ID'];
            }
        }

        return $result;
    }


    /**
     * Функция возвращает параметр элемента инфоблока
     * @param string|int|boolean $type Кодовое обозначение инфоблока
     * из опций модуля или ID инфоблока или false
     * @param int $id ID элемента
     * @param string $propCode Символьный код свойства
     * @return array
     */
    public static function getProp($type, $id, $propCode) {
        CModule::IncludeModule('iblock');


        // Выборка из базы
        if (is_numeric($type)) {
            $infoBlockId = $type;

        } elseif (is_string($type)) {
            $infoBlockId = Help::getOpt('IBLOCK_' . $type . '_ID')
                ?: Help::getIblockIdByCode($type);

        } else {
            $infoBlockId = CIBlockElement::GetIBlockByID($id);
        }

        $resProperty = CIBlockElement::GetProperty(
            $infoBlockId,
            $id,
            [],
            ['CODE' => $propCode]
        );
        // ---------------------------------------------------------------------


        // Формирование результата
        $result = null;
        $resultItems = [];
        $props = [];

        while ($property = $resProperty->Fetch()) {
            $resultItems[] = $property['VALUE'];
            $props[] = $property;
        }

        if ($resultItems[0] && $props[0]['MULTIPLE'] !== 'Y') {
            $result = $resultItems[0];

        } elseif ($resultItems && $resultItems[0] !== null) {
            $result = $resultItems;

        }

        TrimArr($result);

        // ---------------------------------------------------------------------


        return $result;
    }


    /**
     * Функция возвращает ID значения свойства списка
     * @param int|string $type ID инфоблока или его символьный код
     * @param string $propCode Код свойства
     * @param string $xmlParam XML_ID значение, ID которого нужно получить
     * @return int ID свойства
     */
    public static function getPropEnumID($type, $propCode, $xmlParam) {
        CModule::IncludeModule("iblock");

        if (is_numeric($type)) {
            $iblockId = $type;
        } else {
            $iblockId = Help::getIblockIdByCode($type);
        }

        $resProp = CIBlockPropertyEnum::GetList(
            [],
            [
                'IBLOCK_ID' => $iblockId,
                'CODE'      => $propCode,
                'XML_ID'    => $xmlParam,
            ]
        );
        $prop = $resProp->Fetch();

        return $prop['ID'];
    }


    /**
     * @param $params
     * @return array - Массив данных о свойстве списке инфоблока
     */
    public static function getPropEnumDict($params) {
        $iblockId = Help::getIblockIdByCode($params['IBLOCK_CODE']);
        $result = [];

        $resultRows = CIBlockPropertyEnum::GetList(
            $params['ORDER'] ?: [],
            [
                'IBLOCK_ID' => $iblockId,
                'CODE'      => $params['PROPERTY_CODE'],
            ]
        );

        /**
         * Массив элементов выборки с такими полями:
         * [ID] => 7
         * [PROPERTY_ID] => 27
         * [VALUE] => Название значения свойства
         * [DEF] => N
         * [SORT] => 10
         * [XML_ID] => block-house
         * [TMP_ID] =>
         * [EXTERNAL_ID] => block-house
         * [PROPERTY_NAME] => Название свойства
         * [PROPERTY_CODE] => FORM
         * [PROPERTY_SORT] => 200
         */
        $elements = [];
        while ($row = $resultRows->Fetch()) {
            $elements[] = $row;
        }

        // Приведем массив к нужным типам данных
        $typeConverter = new TypeConvert($params['SELECT'] ?: []);
        if ($typeConverter->getTypes()) {
            $result = $typeConverter->convertDataTypes($elements);
        }

        return $result;
    }


    /**
     * Функция возвращает ID значения свойства инфоблока
     * @param int|string $type Символьный код инфоблока или его ID
     * @param int $id ID элемента
     * @param string $code Символьный код свойства
     * @param mixed $value Значение свойства
     * @return array|bool
     */
    public static function getPropIdByValue($type, $id, $code, $value) {
        CModule::IncludeModule('iblock');
        $result = false;


        // Определим ID инфоблока
        if (is_numeric($type)) {
            $iblockId = $type;
        } else {
            $iblockId = Help::getIblockIdByCode($type);
        }


        // Определим ID значения свойства
        $resProp = CIBlockElement::GetProperty(
            $iblockId, $id, [], ['CODE' => $code]
        );

        while ($prop = $resProp->Fetch()) {
            if ($prop['VALUE'] === $value) {
                $result = $prop['PROPERTY_VALUE_ID'];
                break;
            }
        }


        return $result;
    }


    /**
     * Функция возвращает массив с данными о свойствах инфоблока
     * @param array $params Параметры выборки
     * @return array
     */
    public static function getPropertyList($params) {
        CModule::IncludeModule('iblock');
        $result = [];

        if (isset($params['IBLOCK_CODE'])) {
            $iblockId = Help::getIblockIdByCode($params['IBLOCK_CODE']);
        }
        elseif (isset($params['IBLOCK_ID'])) {
            $iblockId = (int) $params['IBLOCK_ID'];
        }
        else {
            $iblockId = Help::getOpt('IBLOCK_' . $params['TYPE'] . '_ID');
        }

        $filter = [];
        $filter['IBLOCK_ID'] = $iblockId;

        $rsProp = CIBlockProperty::GetList([], $filter);
        while ($prop = $rsProp->Fetch()) {
            $result[] = $prop;
        }

        return $result;
    }


    /**
     * Функция устанавливает свойства элемента
     * @param int|string $iblockType Тип инфоблока или ID
     * @param int $id ID элемента
     * @param array $props Массив пар "Название свойства"=>"Значение"
     */
    public static function setProp($iblockType, $id, $props) {
        CModule::IncludeModule('iblock');


        // Определение ID инфоблока
        $iblockId = is_numeric($iblockType) ? $iblockType : Help::getIblockIdByCode($iblockType);


        CIBlockElement::SetPropertyValuesEx(
            $id,
            $iblockId,
            $props
        );
    }


    /**
     * Функция меняет сортировку значений свойств множественного свойства
     * @param int|string $type $iblockType Тип инфоблока или ID
     * @param int $id ID элемента
     * @param string $propName Символьный код свойства
     * @param array $newSortId Новый порядок значений
     * @return bool
     */
    public static function sortPropFiles($type, $id, $propName, $newSortId) {
        CModule::IncludeModule('iblock');
        $result = false;


        // Определение ID инфоблока
        if (is_numeric($type)) {
            $iblockId = $type;
        } else {
            $iblockId = Help::getOpt('IBLOCK_' . $type . '_ID');
        }


        // Выборка параметров свойств
        $resProp = CIBlockElement::GetProperty(
            $iblockId, $id, [],
            ['CODE' => $propName]
        );

        $propList = [];
        while ($prop = $resProp->Fetch()) {
            $propList[] = $prop;
        }


        // Формирование массива длясхранения нового порядка
        $sortedProp = [];
        foreach ($newSortId as $sortValue) {
            foreach ($propList as $prop) {
                if ($prop['VALUE'] === $sortValue) {
                    $sortedProp[$prop['PROPERTY_VALUE_ID']]
                        = CIBlock::makeFilePropArray(['VALUE' => $sortValue]);
                }
            }
        }

        if (count($newSortId) === count($sortedProp)) {
            self::setProp($type, $id, [$propName => $sortedProp]);
            $result = true;
        }


        return $result;

    }


    /**
     * Метод возвращает массив с описанием мета-тегов title, keywords и description
     * @param int $iblockId - ID инфоблока
     * @param array $element - Массив элемента с ключем ['id']
     * @return array
     */
    public static function getSeo(int $iblockId, array $element): array {
        $props = new ElementValues($iblockId, $element['id']);
        $values = $props->getValues();

        return [
            'title'       => $values['ELEMENT_META_TITLE'] ?: $element['name'] ?: $element['NAME'],
            'keywords'    => htmlspecialchars($values['ELEMENT_META_KEYWORDS']),
            'description' => htmlspecialchars($values['ELEMENT_META_DESCRIPTION']),
        ];
    }


    /**
     * Функция обновляет значение
     * @param int $iblockId - ID инфоблока
     * @param int $id - ID элемента
     * @param string $code - Символьный код свойства
     * @param string $value - Новое значение
     */
    public static function updateProp($iblockId, $id, $code, $value) {
        CModule::IncludeModule('iblock');

        CIBlockElement::SetPropertyValuesEx(
            $id,
            $iblockId,
            [$code => $value]
        );
    }

}
