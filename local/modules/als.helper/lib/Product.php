<?php

namespace ALS\Helper;


use CCatalogProduct;
use CModule;
use CPrice;

class Product {

    // =========================================================================
    // === CRUD ================================================================
    // =========================================================================


    // =========================================================================
    // === ДОПОЛНИТЕЛЬНЫЕ ФУНКЦИИ ==============================================
    // =========================================================================

    /**
     * Функция обновит или добавит цену для товара
     * @param int $id ID элемента инфоблока
     * @param array $fields Массив с полями и свойствами товара
     * @return
     */
    public static function setPrice($id, $fields) {
        $result = null;

        if ((int)$id && is_array($fields)) {
            CModule::IncludeModule('catalog');

            $fields = [
                'PRODUCT_ID'       => $id,
                'CATALOG_GROUP_ID' => 1,
                'PRICE'            => $fields['PRICE'],
                'CURRENCY'         => $fields['CURRENCY'],
            ];

            $res = CPrice::GetList(
                [],
                [
                    'PRODUCT_ID'       => $id,
                    'CATALOG_GROUP_ID' => 1,
                ]
            );

            if ($arr = $res->Fetch()) {
                CPrice::Update($arr['ID'], $fields);

            } else {
                CPrice::Add($fields);
            }

        }

        return $result;

    }


    /**
     * Функция обновит параметры элемента каталога на основе данных из элемента
     * @param int $id ID элемента инфоблока
     * @param array $element Массив с полями и свойствами товара
     * @return bool
     */
    public static function updateFromArray($id, $element) {
        $result = null;

        if ((int)$id && is_array($element)) {
            CModule::IncludeModule('catalog');

            $quantity = 0;
            if (isset($element['QUANTITY']) && $element['QUANTITY']) {
                $quantity = $element['QUANTITY'];
            }

            $result = CCatalogProduct::add([
                'ID'       => $id,
                'QUANTITY' => $quantity,
            ]);
        }

        return $result;

    }

}
