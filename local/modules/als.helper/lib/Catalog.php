<?php

namespace ALS\Helper;


use Bitrix\Main\LoaderException;

class Catalog {
    /**
     * @param array $params - Параметры выборки
     * @return array - Массив единицы измерений
     */
    public static function getMeasureList($params) {
        try {
            \Bitrix\Main\Loader::includeModule('catalog');

            // Определение полей выборки
            $typeConverter = new TypeConvert($params['SELECT'] ?: []);
            $select = $typeConverter->getSelect();

            $resultRows = \CCatalogMeasure::getList(
                $params['ORDER'] ?: [],
                $params['FILTER'] ?: [],
                $params['GROUP_BY'] ?: false,
                $params['NAV_START'] ?: false,
                $select
            );

            $result = [];
            while ($row = $resultRows->Fetch()) {
                $result[] = $row;
            }

            // Приведем массив к нужным типам данных
            if ($typeConverter->getTypes()) {
                $result = $typeConverter->convertDataTypes($result);
            }

            return $result;
        } catch (LoaderException $e) {
        }
    }

}
