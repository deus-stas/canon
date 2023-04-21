<?php

namespace ALS\Helper;


class Search {

    /**
     * Метод исправляет тексты в поле BODY_FORMATED результатов поиска
     * @param array $searchResults
     */
    public static function fixBodyFormattedText(&$searchResults) {
        if (!is_array($searchResults)) {
            return;
        }

        foreach ($searchResults as $k => $item) {
            $searchResults[$k]['BODY_FORMATED'] = str_replace(
                [
                    "\r",
                    ' ...',
                    '... ',
                    "\r...",
                    '&nbsp;...',
                    '&nbsp; ',
                    ' , ',
                    ' .',
                    ' ?',
                    '  ',
                ],
                [
                    ' ',
                    '...',
                    '...',
                    '...',
                    '...',
                    '&nbsp;',
                    ', ',
                    '.',
                    '?',
                    ' ',
                ],
                $item['BODY_FORMATED']
            );
        }
    }

}
