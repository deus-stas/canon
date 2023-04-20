<?php

namespace ALS\Helper;


use CUser;

class User {
    public static function getList($arParams) {

        // Определение фильтра
        if ($arParams['FILTER']) {
            $arFilter = $arParams['FILTER'];

        } else {
            $arFilter = [];

        }
        // ---------------------------------------------------------------------


        // Определение полей выборки
        if ($arParams['SELECT']) {
            $arSelect = $arParams['SELECT'];

        } else {
            $arSelect = [];

        }
        $arSelect['FIELDS'][] = 'ID'; // Выбрать ID обязательно
        // ---------------------------------------------------------------------


        // Выборка результата из базы
        $resUserList = CUser::GetList(
            ($by = 'id'), ($order = 'asc'),
            $arFilter,
            $arSelect
        );
        // ---------------------------------------------------------------------


        // Формирование результата
        $arResult = [];
        while ($arUser = $resUserList->Fetch()) {
            $arResult[$arUser['ID']] = $arUser;
        }

        // ---------------------------------------------------------------------


        return $arResult;
    }
}
