<?php

namespace ALS\Helper;

use ALSTypograf;
use CIBlock;
use CModule;
use COption;
use CPHPCache;
use CSite;

class Help {

    /**
     * Функция конвертирует
     * @param string $str
     * @return string
     */
    public static function convertStringToLink($str = '') {
        $patternProtocol = '/[a-z]{2,6}:\/\//';

        if (preg_match($patternProtocol, $str)) {
            $result = $str;

        } else {
            $result = 'http://' . $str . '/';
        }

        return $result;
    }


    /**
     * Функция возвращает нужную словоформу чистилительного по колчиеству
     * @param int $n Количество
     * @param array $vars Массив словоформ (1, 2, 5)
     * @return string Результат
     */
    public static function getEnding($n, $vars) {
        if (!(int) $n) { return false; }

        $n = (int)$n;

        $plural = $n % 10 === 1 && $n % 100 !== 11
            ? $vars[0]
            : ($n % 10 >= 2 && $n % 10 <= 4 && ($n % 100 < 10 || $n % 100 >= 20)
                ? $vars[1]
                : $vars[2]);

        return $plural;
    }


    /**
     * Метод возвращает символьный код инфоблока по его ID
     * @param $iblockId
     * @param bool $useCache
     * @return string|null
     */
    public static function getIblockCode($iblockId, $useCache = false) {
        CModule::IncludeModule('iblock');
        $result = null;

        $obCache = $useCache ? new CPHPCache : null;

        if ($obCache && $obCache->InitCache(3600, $iblockId, '/als.helper/help/getIblockCode/')) {
            $vars = $obCache->GetVars();
            $result = $vars['RESULT'];

        } else {
            $res = CIBlock::GetList([], ['ID' => $iblockId]);

            while ($item = $res->Fetch()) {
                if ($item['CODE']) {
                    $result = $item['CODE'];
                }
            }

            if ($obCache && $obCache->StartDataCache()) {
                $obCache->EndDataCache([
                    'RESULT' => $result,
                ]);
            }
        }

        return $result;
    }


    /**
     * Функция возвращает ID инофблока по его коду
     * @param string $code - Символьный код инфоблока
     * @param bool $useCache - Использовать кеш или нет
     * @return int - ID инфоблока
     */
    public static function getIblockIdByCode($code, $useCache = false) {
        CModule::IncludeModule('iblock');
        $result = null;

        $filter = [
            'CODE' => $code,
            'SITE_ID' => self::getSiteId()
        ];

        $obCache = $useCache ? new CPHPCache : null;

        if ($obCache && $obCache->InitCache(3600, serialize($filter), '/als.helper/help/getIblockIdByCode/')) {
            $vars = $obCache->GetVars();
            $result = $vars['RESULT'];

        } else {
            $res = CIBlock::GetList([], $filter);

            while ($item = $res->Fetch()) {
                if ($item['ID']) {
                    $result = (int)$item['ID'];
                }
            }
        }

        if ($obCache && $obCache->StartDataCache()) {
            $obCache->EndDataCache([
                'RESULT' => $result,
            ]);
        }

        return $result;
    }


    /**
     * Функция возвращает массив всех инфоблоков на сайте
     * @param array $order
     * @param array $filter
     * @param bool $includeCount
     * @return array
     */
    public static function getIblockList($order = [], $filter = [], $includeCount = false) {
        CModule::IncludeModule('iblock');

        $res = CIBlock::GetList($order, $filter, $includeCount);
        $items = [];

        while ($item = $res->Fetch()) {
            $items[] = $item;
        }

        return $items;
    }


    /**
     * Функция возвращает параметр модуля
     * @param string $option Код параметра
     * @return string
     */
    public static function getOpt($option) {
        return COption::GetOptionString('als.helper', $option);
    }


    /**
     * Функция возвращает SITE_ID
     *
     */
    public static function getSiteId() {
        if (defined('SITE_ID')) {
            $sites = CSite::GetList($by = 'sort', $order = 'asc', ['LID' => SITE_ID]);
            if ($site = $sites->Fetch()) {
                return $site['LID'];
            }
        }

        $result = null;

        $obCache = new CPHPCache;

        if ($obCache && $obCache->InitCache(3600, '', '/als.helper/help/getSiteId/')) {
            $vars = $obCache->GetVars();
            $result = $vars['RESULT'];

        } else {
            $sites = CSite::GetList($by = 'sort', $order = 'asc', ['DEFAULT' => 'Y']);
            if ($site = $sites->Fetch()) {
                $result = $site['LID'];
            }
        }

        if ($obCache && $obCache->StartDataCache()) {
            $obCache->EndDataCache([
                'RESULT' => $result,
            ]);
        }

        return $result;
    }


    public static function getTypografFormat($data) {
        if (!CModule::IncludeModule('als.typograf')) { return null; }


        $cacheOn = (COption::GetOptionString('main', 'component_managed_cache_on') === 'Y') ? 1 : 0;
        $obCache = new CPHPCache;
        $lifeTime = 86400 * 30;
        $cacheId = $data;
        $cachePath = '/als.helper/CHelper/getTypografFormat/';


        if ($cacheOn && $obCache->InitCache($lifeTime, $cacheId, $cachePath)) {
            $vars = $obCache->GetVars();
            $format = $vars['FORMAT'];

        } else {
            $format = ALSTypograf::Format($data);
        }

        if ($obCache->StartDataCache()) {
            $obCache->EndDataCache([
                'FORMAT' => $format,
            ]);
        }


        if ($format) {
            return $format;
        }

        return $data;
    }


    /**
     * Функция отправляет HTTP POST запрос на адрес
     * @param string $url
     * @param array $data
     * @return array
     */
    public static function httpPost($url, $data) {
        $dataUrl = http_build_query($data);
        $dataLen = strlen($dataUrl);

        $result = [
            'content' => file_get_contents(
                $url,
                false,
                stream_context_create(
                    [
                        'http' => [
                            'method'  => 'POST',
                            'header'  => "Connection: close\r\nContent-Length: $dataLen\r\n",
                            'content' => $dataUrl,
                        ],
                    ]
                )
            ),
            'headers' => $http_response_header,
        ];

        return $result;
    }


    /**
     * Функция переводит дату из формата сайта в человеческий вид
     * @param string $date Дата в формате текущего сайта
     * @param string $format Формат в который необходимо её пробразовать
     * @return string Дата в отформатированном виде
     */
    public static function formatDateHuman($date, $format) {
        $result = FormatDateFromDB($date, $format);

        // месяц с большой буквы
        if (LANGUAGE_CODE === 'ru') {
            $result = mb_strtolower($result, 'UTF-8');
        } else {
            $result = mb_convert_case($result, MB_CASE_TITLE, "UTF-8");
        }

        $result = preg_replace('/^0/', '', $result);
        $result = str_replace(' ', '&nbsp;', $result);

        return $result;
    }


    /**
     * Функция превращает дату в формате сайта в человеческий вид
     * Возвращает время, если дата соответствует сегодняшней
     * Возвращает только время, дату, месяц, если дата текущего года
     * @param string $date Дата в формате сайта
     * @return string
     */
    public static function formatDateHumanSmart($date) {
        // Исходные параметры: парсинг даты и таймштампы
        $dateParsed = ParseDateTime($date);
        $timeStamp = MakeTimeStamp($date);

        $dateDayNum = floor($timeStamp / 86400);
        $nowDayNum = floor(time() / 86400);


        // Определение формата даты
        $format = 'DD MMMM YYYY в HH:MI';

        if ($dateDayNum === $nowDayNum) {
            $format = 'Сегодня в HH:MI';

        } elseif ($dateDayNum === $nowDayNum - 1) {
            $format = 'Вчера в HH:MI';

        } elseif ($dateParsed['YYYY'] === date('Y')) {
            $format = 'DD MMMM в HH:MI';
        }

        // Формирование результата
        return self::formatDateHuman($date, $format);
    }


    /**
     * Функция возвращает число в формате суммы
     * @param int $number Число для вывода суммы
     * @param int $decimal Число знаков после запятой, например, не более двух
     * @param string $groupSeparator Разделитель разрядов
     * @param string $fractionSeparator Разделитель дробных значений
     * @return string
     */
    public static function formatPrice($number, $decimal = 2, $groupSeparator = '&#8201;', $fractionSeparator = ',') {
        if (LANGUAGE_ID === 'en') {
            $fractionSeparator = '.';
            $groupSeparator = ',';
        }

        $roundNumber = round($number, $decimal);
        $numberParts = explode('.', $roundNumber);
        $decimalDefine = $numberParts[1] ? strlen($numberParts[1]) : 0;
        $separatorTmp = ($number >= 10000) ? '#' : false;

        $num = number_format($number, $decimalDefine, $fractionSeparator, $separatorTmp);
        $num = str_replace('#', $groupSeparator, $num);

        if (!$num) {
            $num = 0;
        }

        return $num;
    }


    /**
     * Функция минифицирует html-код
     * @param string $content html-код
     * @return string
     */
    public static function minifyHtml($content) {
        // http://stackoverflow.com/questions/6225351/how-to-minify-php-page-html-output
        $search = [
            '/\>[^\S ]+/s',  // strip whitespaces after tags, except space
            '/[^\S ]+\</s',  // strip whitespaces before tags, except space
            // '/(\s)+/s'       // shorten multiple whitespace sequences
        ];

        $replace = [
            '>',
            '<',
            // '\\1'
        ];

        return preg_replace($search, $replace, $content);
    }

}
