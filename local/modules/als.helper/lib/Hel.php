<?php

namespace ALS\Helper;

use Bitrix\Highloadblock\HighloadBlockTable;
use Bitrix\Main\Application;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Entity\AddResult;
use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\SystemException;
use CModule;
use Exception;

/**
 * Класс для работы с элементами highload-инфоблоков
 */
class Hel {
    // =========================================================================
    // ================================= CRUD ==================================
    // =========================================================================

    /**
     * Функция добавляет элемент в HL-блок
     * @param string $hlType Код HL-блока из опций модуля
     * @param array $fields Массив полей для добавления
     * @return AddResult|bool
     * @throws LoaderException
     * @throws SystemException
     */
    public static function add($hlType, $fields) {
        $entity = self::getEntity($hlType);

        $result = false;

        if ($entity) {
            try {
                $result = $entity::add($fields);
            } catch (Exception $e) {
                return null;
            }
        }

        return $result;
    }


    /**
     * Функция возвращает массив данных из highload-блока по входным параметрам
     * @param string $hlType - Код HL-блока из опций модуля
     * @param array $params - Массив параметров выборки элементов
     * @return array - Массив записей, ключи массива соответствуют ID записей
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     * @throws LoaderException
     */
    public static function getList($hlType, array $params = []) {
        CModule::IncludeModule('highloadblock');

        // Определение полей выборки
        $typeConverter = new TypeConvert($params['select'] ?: []);
        $select = $typeConverter->getSelect();

        $entity = self::getEntity($hlType);

        if (!$entity) {
            throw new SystemException('HighloadBlock does not exists');
        }

        $rsData = $entity::getList(array_merge($params, ['select' => $select]));
        $result = [];

        while ($arData = $rsData->Fetch()) {
            // Переводим дату в строку
            if ($arData['UF_DATE']) {
                $arData['UF_DATE'] = $arData['UF_DATE']->toString();
            }

            // Сохранение массива с привязкой к ID, если есть (как правило)
            if ($arData['ID']) {
                $result[$arData['ID']] = $arData;
            } else {
                $result[] = $arData;
            }
        }

        // Приведем массив к нужным типам данных
        if ($typeConverter->getTypes()) {
            $result = $typeConverter->convertDataTypes($result);
        }

        return $result;
    }


    /**
     * Функция обновляет параметры записи в базе
     * @param string $hlType Тип HL-блока
     * @param int $id ID записи
     * @param array $fields Массив обновляемых параметров
     * @return bool
     * @throws Exception
     */
    public static function update($hlType, $id, $fields) {
        $result = false;
        $entity = self::getEntity($hlType);

        if ($entity) {
            $result = $entity::update($id, $fields);
        }

        return $result;
    }


    /**
     * Функция удаляет запись с указанным ID
     * @param string $hlType Код HL-блока из опций модуля
     * @param int $id ID записи
     * @return boolean true - успешно и false в противном случае
     * @throws Exception
     */
    public static function delete($hlType, $id) {
        if (!(int)$id) { return null; }

        $entity = self::getEntity($hlType);
        $result = $entity::delete($id);

        // Проверка результата
        if ($result->isSuccess()) {
            return true;
        }

        $errList = '';
        foreach ($result->getError() as $error) {
            $errList .= $error->getMessage() . '; ';
        }
        echo $errList;

        return false;
    }


    /**
     * Функция удаляет все записи
     * @param string $hlType Код HL-блока из опций модуля
     * @throws Exception
     */
    public static function clear($hlType) {
        Application::getConnection()->truncateTable(
            self::getTableName($hlType)
        );
    }


    // =========================================================================
    // ======================== ДОПОЛНИТЕЛЬНЫЕ ФУНКЦИИ =========================
    // =========================================================================

    /**
     * @param $hlBlockCode
     * @return DataManager|bool|null
     * @throws SystemException
     * @throws LoaderException
     */
    public static function getEntity($hlBlockCode) {
        if (!$hlBlockCode) { return null; }

        Loader::includeModule('highloadblock');

        $iblockId = null;
        if (is_numeric($hlBlockCode)) {
            $iblockId = (int)$hlBlockCode;

        } else {
            $rsData = HighloadBlockTable::getList([
                'select' => ['ID', 'NAME'],
                'filter' => ['NAME' => $hlBlockCode],
            ]);

            if ($item = $rsData->Fetch()) {
                $iblockId = (int) $item['ID'];
            }
        }

        if ($iblockId) {
            $hlBlock = HighloadBlockTable::getById($iblockId)->fetch();
            $obEntity = HighloadBlockTable::compileEntity($hlBlock);
            return $obEntity->getDataClass();
        }

        return false;
    }


    /**
     * @param $hlBlockCode
     * @return DataManager|bool|null
     * @throws SystemException
     * @throws LoaderException
     */
    public static function getTableName($hlBlockCode) {
        if (!$hlBlockCode) { return null; }

        Loader::includeModule('highloadblock');

        $iblockId = null;
        if (is_numeric($hlBlockCode)) {
            $iblockId = (int)$hlBlockCode;

        } else {
            $rsData = HighloadBlockTable::getList([
                'select' => ['ID', 'NAME'],
                'filter' => ['NAME' => $hlBlockCode],
            ]);

            if ($item = $rsData->Fetch()) {
                $iblockId = (int) $item['ID'];
            }
        }

        if ($iblockId) {
            $hlBlock = HighloadBlockTable::getById($iblockId)->fetch();
            $obEntity = HighloadBlockTable::compileEntity($hlBlock);
            return $obEntity->getDBTableName();
        }

        return false;
    }

}
