<?php

namespace ALS\Helper;

use CModule;
use CRubric;
use CSubscription;



class Subscribe {

    /**
     * Функция для подписки email на рассылку
     * документация https://dev.1c-bitrix.ru/api_help/subscribe/classes/csubscriptiongeneral/csubscriptionadd.php
     * @param string $email - адрес для подписки
     * @param array $rubricsCode - рубрики для подписки
     * @return int|false ID нового раздела или false
     */
    public static function add(string $email, array $rubricsCode): ?int {
        CModule::IncludeModule('subscribe');

        $rubricsId = [];

        foreach ($rubricsCode as $code) {
            $rubrics = CRubric::GetList([], ['CODE' => $code]);
            while ($rubric = $rubrics->GetNext()) {
                $rubricsId[] = $rubric['ID'];
            }
        }

        $params = [
            'ACTIVE'       => 'Y',
            'CONFIRMED'    => 'Y',
            'EMAIL'        => $email,
            'FORMAT'       => 'html',
            'RUB_ID'       => $rubricsId,
            'SEND_CONFIRM' => 'N',
        ];

        $items = self::getList([
            'FILTER' => [
                'EMAIL' => $email
            ]
        ]);

        $id = count($items) ? $items[0]['ID'] : false;

        if ($id) {
            $params['RUB_ID'] = array_merge(
                CSubscription::GetRubricArray($id),
                $rubricsId
            );
        }

        $subscr = new CSubscription;
        $result = $id ?
            $subscr->Update($id, $params) :
            $subscr->Add($params);

        return $result;
    }


    /**
     * Возвращает список подписок
     * @param array $params
     * @return array
     */
    public static function getList(array $params): array {
        $rows = CSubscription::GetList(
            $params['ORDER'] ?: ['ID' => 'ASC'],
            $params['FILTER']
        );

        $items = [];

        while ($el = $rows->Fetch()) {
            $items[] = $el;
        }

        return $items;
    }

}
