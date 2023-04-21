<?php

namespace ALS\Helper;


use Bitrix\Main\ArgumentTypeException;
use Bitrix\Main\Mail\Event;

class NotificationEmail
{
    /**
     * Отправляет e-mail уведомление средствами Битрикс
     * @param string $eventName - символьный код уведомления
     * @param array $data - данные для отправки
     * @param array|null $files - список идентификаторов файлов, либо абсолютных путей
     * @param bool|null $sendImmediate - отправить немедленно
     * @param string $siteId - ID сайта
     * @return bool
     * @throws ArgumentTypeException
     */
    public static function send(string $eventName, array $data, array $files = null, bool $sendImmediate = null, string $siteId = SITE_ID): bool
    {
        $dataSend = [
            'LID'        => $siteId,
            'EVENT_NAME' => $eventName,
            'C_FIELDS'   => $data,
            'FILE'       => $files
        ];

        $result = false;

        if ($sendImmediate) {
            $is = Event::sendImmediate($dataSend);
            if ($is !== Event::SEND_RESULT_ERROR) {
                $result = true;
            }
        } else {
            $is = Event::send($dataSend);
            if ($is->isSuccess()) {
                $result = true;
            }
        }

        return $result;
    }


    /**
     * @param array<string,mixed> $items
     * @param bool $isConvertCamelCaseToSnakeCase
     * @return array
     */
    public static function convertKeyForTemplate(array $items, $isConvertCamelCaseToSnakeCase = true): array
    {
        foreach ($items as $key => $value) {
            if (is_string($key)) {
                $convertKey = $isConvertCamelCaseToSnakeCase === true ? self::convertCamelCaseToSnakeCase($key) : $key;

                $convertKey = '#FIELD_' . mb_strtoupper($convertKey) . '#';

                $items[$convertKey] = $value;

                unset($items[$key]);
            }
        }

        return $items;
    }


    /**
     * Конвертирует верблюжью в змеиную
     *
     * @param string $item
     * @return string
     */
    private static function convertCamelCaseToSnakeCase(string $item): string
    {
        $pattern = '/([a-z])([A-Z])/';

        return preg_replace_callback($pattern, static function ($a) {
            return $a[1] . '_' . strtolower($a[2]);
        }, $item);
    }
}
