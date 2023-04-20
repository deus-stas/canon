<?php

namespace ALS\Helper;


use CModule;

class Iblock {
    /**
     * Функция возвращает массив с инфоблоками
     * @param array $params Параметры выборки
     * @return array
     */
    public static function getList($params) {
        CModule::IncludeModule('iblock');

        // Определение полей выборки
        $typeConverter = new TypeConvert($params['SELECT'] ?: []);

        // Выборка результата из базы
        $elements = Help::getIblockList(
            $params['ORDER'] ?: [],
            $params['FILTER'] ?: []
        );

        $result = [];

        // Приведем массив к нужным типам данных
        if ($typeConverter->getTypes()) {
            $result = $typeConverter->convertDataTypes($elements);
        }

        return $result;
    }

}
