<?php

namespace ALS\Helper;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Context;
use Bitrix\Main\Loader;
use Bitrix\Main\Mail\Event;

Loader::includeModule('als.helper');
Loader::includeModule('als.typograf');

/**
 * Статический класс для работы с почтовыми событиями
 */
class Mail
{

    private static $settings = [];

    /**
     * Отправляет почтовое событие
     *
     * @param string $eventName Имя вызываемого почтового события
     * @param array $fields Поля, которые необходимы для этого почтового события
     * @return boolean            Результат выполнения вызова почтового события
     * @api
     * @static
     */
    public static function sendMail(string $eventName = 'ALS_GLOBAL_NOTIFICATION', array $fields = []): bool
    {
        $lid    = 's1';
        $result = Event::send([
            'EVENT_NAME' => $eventName,
            'LID'        => $lid,
            'C_FIELDS'   => $fields,
        ]);

        return $result->isSuccess();
    }

    /**
     * Отправляет почтовое событие
     *
     * @param array $fields Поля, которые необходимы для этого почтового события []
     * @return boolean            Результат выполнения вызова почтового события
     * @api
     * @static
     */
    public static function send(array $fields = []): bool
    {
        if (empty($fields['EMAIL_FROM'])) {
            $fields['EMAIL_FROM'] = static::getEmailFrom();
        }

        $result = Event::send([
            'EVENT_NAME' => 'ALS_GLOBAL_NOTIFICATION',
            'LID'        => 's1',
            'C_FIELDS'   => $fields,
        ]);

        return $result->isSuccess();
    }

    /**
     * Получить стандартную электронную почту, с которой будут отправляться письма в поле from
     *
     * @return string                                   Стандартный адрес электронной почте, используемый
     *                                                  как отправитель
     * @api
     * @static
     */
    public static function getEmailFrom(): string
    {
        return trim(explode(',', Option::get('main', 'email_from'))[0]);
    }

    /**
     * Заменяет текст в указанном шаблоне
     *
     * @param string $text Текст шаблона
     * @param array $field Поля, которые необходимо подставить
     * @return string        Результирующий текст
     * @api
     * @static
     */
    public static function replaceText(string $text, array $field): string
    {
        foreach ($field as $key => $value) {
            while (preg_match('/#' . $key . '#/i', $text)) {
                $text = str_replace('#' . $key . '#', $value, $text);
            }
        }
        return $text;
    }

    /**
     * Получает шаблон письма из специализированного каталога по имени
     *
     * @param string $path Имя шаблона письма
     * @return string          Текст шаблона письма
     * @api
     * @static
     * @global array $_SERVER
     */
    public static function getTemplate(string $path = ''): string
    {
        $result = '';

        if ($path === '') {
            return $result;
        }

        return file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/local/mail/templates/' . $path);
    }

    /**
     * Получает интерактивный шаблон письма из специализированного каталога по его имени
     * В шаблоне доступна переменная $arResult. А сам шаблон можно использовать как php-скрипт
     *
     * @param string $path Путь до интерактивного шаблона письма
     * @param array $arResult Массив $arResult для шаблона
     * @return string Шаблон с подставленными значениями
     * @global array $_SERVER
     * @api
     * @static
     */
    public static function getInteractiveTemplate(string $path = '', array $arResult = []): string
    {
        $result = '';

        if ($path === '') {
            return $result;
        }

        $fullPath = $_SERVER['DOCUMENT_ROOT'] . '/local/mail/templates/' . $path . '.php';

        if (!file_exists($fullPath)) {
            return $result;
        }

        $setting = static::getSetting();

        \ob_start();
        include $fullPath;

        return \ob_get_clean();
    }

    /**
     * Получить стандартные настройки и параметры сайта
     * @return array Массив стандартных настроек
     * @global array $_SERVER
     * @api
     * @static
     */
    public static function getSetting(): array
    {
        if (empty(static::$settings)) {
            static::$settings = [
                'emailFrom'  => static::getEmailFrom(),
                'siteName'   => static::getSiteName(),
                'serverName' => $_SERVER['SERVER_NAME'],
            ];
        }
        return static::$settings;
    }

    /**
     * Получает имя сайта
     *
     * @return string Название сайта
     * @api
     * @static
     */
    public static function getSiteName(): string
    {
        return CacheManager::cached([
            'ttl'  => 60 * 60 * 24 * 7,
            'hash' => '',
            'dir'  => '/site/',
            'func' => function () {
                $rsSites = \CSite::GetList(
                    $sort = 'sort',
                    $order = 'desc',
                    [
                        'ID' => Context::getCurrent()->getSite(),
                    ]
                );

                $arSite = $rsSites->Fetch();
                if ($arSite) {
                    return $arSite['NAME'];
                }

                return 'Безымянный сайт';
            },
        ]);
    }

    /**
     * Вызывает принудительную отправку писем
     */
    public static function forceSending(): void
    {
        \CEvent::ExecuteEvents();
    }

}
