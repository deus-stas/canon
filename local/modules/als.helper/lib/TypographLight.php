<?php

namespace ALS\Helper;

/*
 * Класс реализует минимальный набор функций и методов для
 * типографирования текстов.
 *
 * Планируется для типографирования названий, коротких текстовых фраз, которые
 * или неудобно заводить в админке или клиент не будет этим заниматься.
 *
 */

class TypographLight {
    // =========================================================================
    // === Параметры объекта ===================================================
    // =========================================================================

    // Обрабатываемый текст
    private $text;

    // Язык текста
    private $lang = 'ru';

    // Рег. выражения для установки неразрывных пробелов
    private $constToNbsp = [
        'ru' => [
            'name'          => 'Russian',
            'words_pattern' => [
                '/(\s|^|;|\()(и|в|во|не|на|из|за|под|над|от|до|об|с|у|что|а|как|то|по|он|все|это|о|его|к|еще|для|ко|со|бы|же)(\s)/iu',
                '/(\s|^)(ул.|д.|стр.|г.|п.|оф.)(\s)/iu',
                '/(\s|^)(of|and|or|the|on|in|to)(\s)/',
                '/(\s|^)(©|&copy;)(\s)/',
            ],
        ],
    ];

    // Рег. выражения для установки nobr
    private $constToNobr = [
        'ru' => [
            'name'          => 'Russian',
            'words_pattern' => [
//                '/((\+\d\s|\d\s)?(\(?\d{3,4}\)?[\- ]?)?[\d\-]{7,10}\d)/',
// https://rp.medical.canon/news/mri-vantage-elan тут баг у src
                '/(по\-[А-яЁ\w]+)/iu',
            ],
        ],
    ];

    // Установка неразрывных пробелов перед единицами измерений
    private $constToNbspSi = [
        'ru' => [
            'name'          => 'Russian',
            'words_pattern' => [
                '/(\d+)\s([A-zА-яЁё\w])/',
            ],
        ],
    ];

    // Установка тонких пробелов в числах
    private $constToThinsp = [
        'ru' => [
            'name'          => 'Russian',
            'words_pattern' => [
                '/(\d{1,3})\s(\d{3})/',
            ],
        ],
    ];

    // Обработка кавычек
    private $constQoutes = [
        'ru' => [
            ['&laquo;', '&raquo;'],
            ['&laquo;', '&raquo;'],
//            ['&bdquo;', '&ldquo;'],
        ],
    ];

    // =========================================================================
    // === КОНСТРУКТОР, ГЕТТЕРЫ и СЕТТЕРЫ ======================================
    // =========================================================================

    public function __construct() {
    }


    // =========================================================================
    // === CRUD ================================================================
    // =========================================================================

    /**
     * Метод возвращает типографированный текст
     * @param string $text
     * @param array $params
     * @return string Типографированный текст
     */
    public function getResult($text, $params = []) {
        if (!is_string($text) || !$text) {
            return $text;
        }

        $this->text = trim($text);


        // Меняем кавычки
        if ($params['quote'] === true) {
            $this->setQuote();
        }


        // Добавляем тонкие пробелы
        $this->setThinsp();


        // Добавляем неразрывные пробелы
        $this->setNbsp();


        // Запретим переносы строк, где нужно
        $this->setNobr();


        // Ставим тире
        $this->setDash();


        // Возвращаем результат
        return $this->text;
    }


    /**
     * Метод возвращает типографированную строку с номерами телефонов
     * @param string $text
     * @return string Типографированный текст
     */
    public function getPhones($text) {
        if (!is_string($text) || !$text) {
            return $text;
        }

        $phonePattern = '/(\+\d)([\(\)\d\s\-]+)(\d\d)/';

        return preg_replace($phonePattern, '<span class="nobr">$1$2$3</span>', $text);
    }


    // =========================================================================
    // === ДОПОЛНИТЕЛЬНЫЕ ФУНКЦИИ ==============================================
    // =========================================================================

    private function setDash() {
        $pattern = '/([A-zА-яЁё])\s+([—–-])/ui';

        $this->text = preg_replace($pattern, '$1&nbsp;&mdash;', $this->text);

    }


    /**
     * Метод добавляет неразырвные пробелы в текст
     */
    private function setNbsp() {
        // Предлоги и адреса
        $patternList = $this->constToNbsp[$this->lang]['words_pattern'];

        foreach ($patternList as $pattern) {
            // Две строки для кейса:
            // Как в московских школах -> Как&nbsp;в&nbsp;московских школах
            $this->text = preg_replace($pattern, '$1$2&nbsp;', $this->text);
            $this->text = preg_replace($pattern, '$1$2&nbsp;', $this->text);
        }


        // Единицы измерения
        $patternListSi = $this->constToNbspSi[$this->lang]['words_pattern'];

        foreach ($patternListSi as $pattern) {
            $this->text = preg_replace($pattern, '$1&nbsp;$2', $this->text);
        }
    }


    private function setNobr() {
        $patternList = $this->constToNobr[$this->lang]['words_pattern'];

        foreach ($patternList as $pattern) {
            $this->text = preg_replace($pattern, '<span class="nobr">$1</span>', $this->text);
        }
    }


    private function setThinsp() {
        $patternList = $this->constToThinsp[$this->lang]['words_pattern'];

        foreach ($patternList as $pattern) {
            $this->text = preg_replace($pattern, '$1&thinsp;$2', $this->text);
        }
    }

    private function setQuote() {
        $quotes = $this->constQoutes[$this->lang];
        $quoteByLevel = static function ($level, $type = 'open') use ($quotes) {
            return $quotes[$level > 0 ? 1 : 0][($type === 'close') ? 1 : 0];
        };

        $result = '';
        $prevSpace = true;
        $tagOpen = false;
        $quotLevel = 0;

        $text = str_replace(['&laquo;', '&raquo;', '&bdquo;', '&ldquo;', '&quot;', '«', '»', '“', '”', '„', '"'], '"', $this->text);
        while (mb_strlen($text) > 0) {
            $char = mb_substr($text, 0, 1);
            $text = mb_substr($text, 1);

            if ($tagOpen) {
                if ($char === '>') {
                    $tagOpen = false;
                }
            }
            elseif ($char === '<') {
                $tagOpen = true;
            }
            elseif ($char === '"') {
                $nextChar = mb_substr($text, 0, 1);
                $nextSpace = trim($nextChar) === '';

                if ($prevSpace && !$nextSpace) {
                    $char = $quoteByLevel($quotLevel++, 'open');
                }
                elseif ($quotLevel > 0) {
                    $char = $quoteByLevel(--$quotLevel, 'close');
                }
            }

            $prevSpace = (trim($char) === '');
            $result .= $char;
        }

        $this->text = $result;
    }

}
