<?php


namespace ALS\Helper;
use Org\Heigl\Hyphenator as h;


class Hyphenator {
    public static function process(array &$items, array $fields): void {
        $hyphenator = h\Hyphenator::factory();
        $o = new h\Options();
        $o->setDefaultLocale('ru_RU');
        $o->setHyphen('&shy;');
        $o->setLeftMin(2);
        $o->setRightMin(2);
        $o->setWordMin(10);
        $o->setFilters('Simple');
        $o->setTokenizers(['Whitespace', 'Punctuation']);

        $hyphenator->setOptions($o);

        foreach ($items as $k => $item) {
            foreach ($fields as $field) {
                if (strpos($item[$field], '&shy;') === false) {
                    $items[$k][$field] = trim($hyphenator->hyphenate(' ' . $item[$field]));
                }
            }
        }
    }
}
