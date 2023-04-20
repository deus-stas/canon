<?php


namespace ALS\Helper;


class Typograph {

    public static function processItem(&$item, $fields): void {
        $typo = new TypographLight();

        foreach ($fields as $field) {
            if (!$item[$field]) { continue; }
            $item[$field] = $typo->getResult($item[$field], ['quote' => true]);
        }
    }

    public static function processItems(&$items, $fields): void {
        foreach ($items as $k => $item) {
            self::processItem($items[$k], $fields);
        }
    }

}
