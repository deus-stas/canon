<?php

namespace ALS\Helper;

/*
 * Вспомогательный класс работы с массивами
 */

use CUtil;

class Arr {

    /**
     * Функция комбинирует новый массив, с ключом $key - элементом массива
     * @param $arr
     * @param $key
     * @param null $val
     * @return array
     */
    public static function combine($arr, $key, $val = null) {
        return array_combine(
            array_map(static function ($value) use ($key) { return $value[$key] ?: null; }, $arr),
            $val ?
                array_map(static function ($value) use ($val) { return $value[$val] ?: null; }, $arr) :
                $arr
        );
    }

    /**
     * Функция производит поиск по двумерному массиву
     * @param array $src Массив в котором производится поиск
     * @param string $fieldName Имя ключа, по которому ищем
     * @param string $searchVal Искомое значение
     * @param string $neededFieldVal Значение какого поля нужно вернуть, если null - вернется ключ массива
     * @return false|int|string
     */
    public static function findInArr($src, $fieldName, $searchVal, $neededFieldVal = 'ID') {
        return array_search($searchVal, array_column($src, $fieldName, $neededFieldVal));
    }


    /**
     * Функция разбивает массив на равное количество колонок
     * @param array $source
     * @param int $count
     * @return array
     */
    public static function getChunkCols($source, $count) {
        $listLength = count($source);
        $partLength = floor($listLength / $count);
        $partRem = $listLength % $count;
        $partition = [];
        $mark = 0;

        for ($px = 0; $px < $count; $px++) {
            $increment = ($px < $partRem) ? $partLength + 1 : $partLength;
            $partition[$px] = array_slice($source, $mark, $increment);
            $mark += $increment;
        }

        return $partition;
    }


    /**
     * Метод вычисляет на сколько процентов первый массив похож на второй по значениям
     * @param array $source Исходный массив
     * @param array $target Массив с которым идет сравнение
     * @return int Число в диапазоне 0-100
     */
    public static function getIntersectPercent($source, $target) {
        if (!is_array($source) || !is_array($target)) {
            return null;
        }

        $sourceCount = count($source);
        $intersect = array_intersect($source, $target);
        $intersectCount = count($intersect);

        if ($intersectCount > 0) {
            $percentage = round($intersectCount / $sourceCount * 100);

        } else {
            $percentage = 0;
        }

        return (int)$percentage;
    }


    /**
     * Метод вычисляет на сколько процентов первый массив похож на второй
     * @param array $source Исходный массив
     * @param array $target Массив с которым идет сравнение
     * @return int Число в диапазоне 0-100
     */
    public static function getIntersectKeyPercent($source, $target) {
        if (!is_array($source) || !is_array($target)) {
            return null;
        }

        $sourceCount = count($source);
        $intersect = array_intersect_key($source, $target);
        $intersectCount = count($intersect);

        if ($intersectCount > 0) {
            $percentage = round($intersectCount / $sourceCount * 100);

        } else {
            $percentage = 0;

        }

        return (int)$percentage;

    }


    /**
     * Функция сводит два одномерных или двумерных массива к одному,
     * а дублирующие свойства превращаются в массивы
     * @param array $target
     * @param array $newArray
     * @return array
     */
    public static function getMergeExt($target, $newArray) {
        if (!is_array($target) || !is_array($newArray)) {
            return null;
        }

        foreach ($target as $fieldKey => $fieldValue) {
            if (is_array($fieldValue) && is_array($newArray[$fieldKey])) {
                $target[$fieldKey] = array_merge($fieldValue, $newArray[$fieldKey]);

            } elseif (is_array($fieldValue) && !is_array($newArray[$fieldKey])) {
                $target[$fieldKey][] = $newArray[$fieldKey];

            } elseif (is_array($newArray[$fieldKey]) && !is_array($fieldValue)) {
                $newArray[$fieldKey][] = $fieldValue;
                $target[$fieldKey] = $newArray[$fieldKey];

            } elseif ($fieldValue !== $newArray[$fieldKey]) {
                $target[$fieldKey] = [$fieldValue, $newArray[$fieldKey]];

            }

        }

        return $target;

    }


    /**
     * array_merge_recursive does indeed merge arrays, but it converts values with duplicate
     * keys to arrays rather than overwriting the value in the first array with the duplicate
     * value in the second array, as array_merge does. I.e., with array_merge_recursive,
     * this happens (documented behavior):
     *
     * array_merge_recursive(array('key' => 'org value'), array('key' => 'new value'));
     *     => array('key' => array('org value', 'new value'));
     *
     * array_merge_recursive_distinct does not change the datatypes of the values in the arrays.
     * Matching keys' values in the second array overwrite those in the first array, as is the
     * case with array_merge, i.e.:
     *
     * array_merge_recursive_distinct(array('key' => 'org value'), array('key' => 'new value'));
     *     => array('key' => array('new value'));
     *
     * Parameters are passed by reference, though only for performance reasons. They're not
     * altered by this function.
     *
     * @param array $array1
     * @param array $array2
     * @return array
     * @author Daniel <daniel (at) danielsmedegaardbuus (dot) dk>
     * @author Gabriel Sobrinho <gabriel (dot) sobrinho (at) gmail (dot) com>
     */
    public static function getMergeRecursiveDistinct(array &$array1, array &$array2) {
        $merged = $array1;

        foreach ($array2 as $key => &$value) {
            if (is_array($value) && isset($merged[$key]) && is_array($merged[$key])) {
                $merged[$key] = self::getMergeRecursiveDistinct($merged[$key], $value);
            } else {
                $merged[$key] = $value;
            }
        }

        return $merged;
    }


    /**
     * Функция возвращает набор рандомных ключей из массива
     *
     * @param array $source Массив в котором производится поиск
     * @param array $params Параметры поиска. Массив с параметрами:
     *    <li> offset (int) - Отступ, с которого начать поиск
     *    <li> count (int) - Необходимое количество ключей
     *    <li> reverse (boolean) - Инвертировать полученный массив или нет
     *    <li> padding (int) - Количество резервируемых элементов слева и справа
     * @return array Массив найденных ключей
     */
    public static function getRandKeys($source, $params) {
        if (!is_array($source) || !is_array($params)) {
            return null;
        }

        $foundKeys = [];
        $padding = $params['padding'] ?: 0;


        // Поиск в массиве с учетом отступа offset
        $searchableArr = array_slice($source, $params['offset'], null, true);

        if ($searchableArr) {
            for ($i = 0; $i < $params['count']; $i++) {
                $randKey = array_rand($searchableArr);

                unset($searchableArr[$randKey]);

                if ($padding > 0) {
                    for ($p = 0; $p < $padding; $p++) {
                        if (isset($searchableArr[$randKey - $p])) {
                            unset($searchableArr[$randKey - $p]);
                        }

                        if (isset($searchableArr[$randKey + $p])) {
                            unset($searchableArr[$randKey + $p]);
                        }
                    }
                }

                $foundKeys[] = $randKey;
            }
        }
        // ---------------------------------------------------------------------


        // Если нужна инверсия
        if ($params['reverse']) {
            $foundKeys = array_reverse($foundKeys);
        }

        // ---------------------------------------------------------------------


        return $foundKeys;

    }


    /**
     * Рекурсивно соединяет все элементы массива в строку
     * @param string $separator
     * @param array $data
     * @return string
     */
    public static function implode(string $separator, array $data): string {
        foreach ($data as $k => $item) {
            if (is_array($item)) {
                $data[$k] = self::implode($separator, $item);
            }
        }

        return implode($separator, $data);
    }


    /**
     * Функция производит поиск по массиву $data, $fieldName
     *
     * @param string $query Строка поиска или спец.код __get_all
     * @param array $data Массив по которому ищем
     * @param array $fieldNames Массив полей в которых ищем
     * @param array $params Дополнительные параметры поиска [USE_TRANSLIT]
     * @return array Массив ID найденных элементов
     */
    public static function searchInArraySmart($query, $data, $fieldNames, $params) {
        $queryL = strtolower($query);
        $queryList = [$queryL];


        if ($params['USE_TRANSLIT'] === 'Y') {
            $translitParams = [
                'replace_space' => ' ',
            ];

            $queryList[] = CUtil::translit($queryL, 'ru', $translitParams);
        }


        // Ищем результаты поиска среди магазинов
        $result = [];
        foreach ($data as $item) {
            foreach ($fieldNames as $fieldName) {
                foreach ($queryList as $queryItem) {
                    // Значение поля элемента приводим к нижнему регистру
                    $itemFieldL = strtolower(trim($item[$fieldName]));


                    if ($query === '__get_all') {
                        // Если нужны все результаты
                        $result[] = $item['id'];

                    } elseif (false !== stripos($itemFieldL, $queryItem)) {
                        // Если есть полное вхождение строки поиска в названии
                        $result[] = $item['id'];

                    } elseif (strlen($query) >= 4) {
                        // Если нет четкого вхождения, то вычисляем сходство строк
                        $simPointP = 0;
                        // $simPointP = similar_text($itemFieldL, $query, $simPointP);


                        // Определяем сколько очков сходства нужно набрать:
                        $needSimPoint = 70;
                        if (strlen($queryItem) < 10 && strlen($itemFieldL) < 10) {
                            $needSimPoint = 80;
                        }

                        $stringLength = (abs(strlen($queryItem) - strlen($itemFieldL)) < 2) ? 1 : 0;

                        if ($simPointP > $needSimPoint && $stringLength) {
                            // Если сходство более необходимого то сохраним
                            $result[] = $item['id'];
                        }
                    }
                }
            }
        }

        return $result;
    }


    /**
     * Функция перемешивает массив с сохранением сортировки, но исключая дубли
     * элементов
     * Например, массив вида: [1][2][3][4][5][2][6][7][8][2][8][8][8][8][2][7]
     * Будет приведен к виду: [1][2][3][4][5][2][6][8][7][8][2][8][2][8][7][8]
     * @param array $source Исходный массив
     * @param string $key Ключ элемета массива по которому считать элементы дублями
     * @return array
     */
    public static function shuffleArrayWithDouble($source, $key = 'ID') {
        if (!is_array($source)) {
            return null;
        }

        $result = array_values($source);
        $count = count($result);


        $itemLast = null;
        for ($k = 0; $k < $count; $k++) {
            $item = $result[$k];

            if ($itemLast !== null && $item[$key] === $itemLast[$key]) {
                $findToReplace = false;

                // Поиск подходящего элемента для замены среди следующих элементов
                for ($i = $k; $i < $count; $i++) {
                    if ($result[$i][$key] !== $item[$key]) {
                        $buffer = $result[$i];

                        $result[$i] = $result[$k];
                        $result[$k] = $buffer;
                        $item = $result[$k];

                        $findToReplace = true;

                        break;
                    }
                }


                // Если поиск не удался, то ищем в обратном направлении и смещаем дубли
                if ($findToReplace === false) {
                    for ($i = $k; $i >= 0; $i--) {
                        if ($result[$i][$key] !== $item[$key]
                            && $result[$i - 1][$key] !== $item[$key]) {

                            for ($j = $i; $j < $count; $j++) {
                                $buffer = $result[$i];

                                $result[$i] = $result[$j];
                                $result[$j] = $buffer;
                            }

                            $item = $result[$k];

                            break;
                        }
                    }
                }

            }

            $itemLast = $item;
        }


        return $result;
    }


    /**
     * Функция сортирует двумерый массив по произвольному количеству полей
     * @param array $source Исходный массив
     * @param string $field Ключ массива для сортировки
     * @param integer|bool $sortOrder Порядок для сортировки SORT_ASC|SORT_DESC|false
     * @return array Отсортированный масиив
     */
    public static function sortByField($source, $field, $sortOrder = false) {
        // Копируем сортируемый массив в $result
        $result = $source;

        // Инициализируем массив, определяющий сортировку
        $sortData = [];
        foreach ($source as $item) {
            foreach ($item as $keyField => $valField) {
                if ($keyField === $field) {
                    $sortData[] = $valField;
                }
            }
        }

        if ($sortData) {
            if ($sortOrder) {
                array_multisort($sortData, $sortOrder, $result);

            } else {
                array_multisort($sortData, $result);

            }

        }

        return $result;
    }


    /**
     * Функция сортирует двумерый массив по нужному полю
     * @param array $source Исходный массив
     * @param array $fields Поля и направления сортировки
     * @return array Отсортированный масиив
     */
    public static function sortArrByFields($source, $fields) {
        $result = $source;


        // Определение массива определяющего сортировку
        $sortData = [];
        foreach ($source as $item) {
            foreach ($item as $keyField => $valField) {
                if ($fields[$keyField]) {
                    $sortData[$keyField][] = $valField;
                }
            }
        }


        // Сортировка
        if ($sortData) {
            $multiSort = [];
            foreach ($fields as $k => $v) {
                $multiSort[] = '$sortData["' . $k . '"], ' . $v;
            }

            eval('array_multisort(' . implode(', ', $multiSort) . ', $result);');
        }


        return $result;
    }

}
