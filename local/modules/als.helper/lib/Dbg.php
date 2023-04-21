<?php

namespace ALS\Helper;

use CEventLog;

class Dbg {
    const EVENT_TYPE_SECURITY = 'SECURITY';
    const EVENT_TYPE_ERROR = 'ERROR';
    const EVENT_TYPE_WARNING = 'WARNING';
    const EVENT_TYPE_INFO = 'INFO';
    const EVENT_TYPE_DEBUG = 'DEBUG';

    /**
     * Дебагер
     *
     * @param $arr - Массив
     * @param $die - die()
     * @param $all - Видно всем пользователям, иначе только администраторам
     */
    public static function show($arr, $die = false, $all = false)
    {
        global $USER;

        if($USER->IsAdmin() || ($all == true))
        {
            echo '<br clear="all" />';
            echo '<pre style="text-align: left;">';
            print_r($arr);
            echo '</pre>';
        }

        if($die) { die; }
    }

    /**
     * Функция добавляет новую запись в log-файл.
     *
     * @param string | array $str
     */
    public static function addLog($str): void
    {
        AddMessage2Log(print_r($str, true));
    }

    /**
     * @param $type Тип ошибки, одна из констант self::EVENT_TYPE_SECURITY ...
     * @param array $params Массив с параметрами:
     * <li>auditTypeId - собственный ID типа события
     * <li>moduleId - модуль, который записывает в лог
     * <li>id - идентификатор связанного объекта
     * <li>message - сообщение, которое будет отображаться в логе
     * <li>
     * <li>
     */
    public static function addEventLog($type, array $params) {
        CEventLog::Add([
            'SEVERITY'      => $type,
            'AUDIT_TYPE_ID' => $params['auditTypeId'] ?? '',
            'MODULE_ID'     => $params['moduleId'] ?? 'als.project',
            'ITEM_ID'       => $params['id'] ?? '',
            'DESCRIPTION'   => $params['message'] ?? '',
        ]);
    }
}
